<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Contracts\Files\PublicUploadContract;
use App\Http\Controllers\Controller;
use App\Support\Admin\SimplePdfExporter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class AdminModuleController extends Controller
{
    protected string $moduleKey;

    public function __construct(protected readonly PublicUploadContract $uploads)
    {
    }

    public function index(Request $request): View
    {
        $module = $this->module();
        $pageTitle = $this->pageTitle($module, $request);

        return view($this->viewName('index'), [
            'module' => $module,
            'pageTitle' => $pageTitle,
            'breadcrumbs' => ['Admin', $module['group'] ?? 'Module', $pageTitle],
            'records' => $this->records($request),
            'filters' => $request->only(['search', 'status', 'type']),
        ]);
    }

    public function create(Request $request): View
    {
        $module = $this->module();
        abort_unless(($module['can_create'] ?? true), 403);
        return view($this->viewName('create'), [
            'module' => $module,
            'pageTitle' => $this->actionTitle($module, $request, 'Add'),
            'breadcrumbs' => ['Admin', $module['label'], 'Add'],
            'record' => null,
            'formData' => $this->createFormData($request, $module),
            'options' => $this->formOptions($module),
            'optionAttributes' => $this->formOptionAttributes($module),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_create'] ?? true), 403);
        $validated = $request->validate($this->rules($module), $this->validationMessages($module), $this->validationAttributes($module));
        $record = $this->persist($this->prepareData($validated, $request, $module), null);
        return redirect()->route($module['route'].'.edit', array_merge([$record->getKey()], $request->only(['type', 'placement', 'section_key', 'row_title'])))->with('success', $module['singular'].' created successfully.');
    }

    public function show(int|string $id): View
    {
        $module = $this->module();
        $record = $this->findRecord($id);
        return view($this->viewName('show'), [
            'module' => $module,
            'record' => $record,
            'pageTitle' => $module['singular'].' Details',
            'breadcrumbs' => ['Admin', $module['label'], 'View'],
        ]);
    }

    public function edit(int|string $id): View
    {
        $module = $this->module();
        abort_unless(($module['can_edit'] ?? true), 403);
        $record = $this->findRecord($id);
        return view($this->viewName('edit'), [
            'module' => $module,
            'pageTitle' => 'Edit '.$module['singular'],
            'breadcrumbs' => ['Admin', $module['label'], 'Edit'],
            'record' => $record,
            'formData' => $this->formData($record, $module),
            'options' => $this->formOptions($module),
            'optionAttributes' => $this->formOptionAttributes($module),
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_edit'] ?? true), 403);
        $record = $this->findRecord($id);
        $validated = $request->validate($this->rules($module, $record), $this->validationMessages($module), $this->validationAttributes($module));
        $record = $this->persist($this->prepareData($validated, $request, $module), $record);
        return back()->with('success', $module['singular'].' updated successfully.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_delete'] ?? true), 403);
        $record = $this->findRecord($id);
        $record->delete();
        return redirect()->route($module['route'].'.index', request()->only(['type', 'placement', 'section_key', 'row_title']))->with('success', $module['singular'].' deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_delete'] ?? true), 403);

        $data = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1', 'max:500'],
            'selected_ids.*' => ['integer'],
        ]);

        $records = $this->recordsQuery($module)->whereKey($data['selected_ids'])->get();
        $count = $records->count();
        $records->each->delete();
        $this->bumpCacheVersionForModule();

        return back()->with('success', $count.' '.$module['label'].' deleted successfully.');
    }

    public function export(Request $request, string $format): StreamedResponse|\Illuminate\Http\Response
    {
        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);

        $module = $this->module();
        $records = $this->filteredRecordsQuery($request, $module)
            ->orderBy(...($module['sort'] ?? ['id', 'desc']))
            ->limit((int) env('ADMIN_EXPORT_LIMIT', 2000))
            ->get();

        $columns = $module['columns'] ?? [];
        $headers = array_map(fn (array $column) => $column['label'], $columns);
        $rows = $records->map(fn (Model $record) => array_map(fn (array $column) => $this->exportValue($record, $column), $columns))->all();
        $filename = Str::slug($this->pageTitle($module, $request)).'-'.now()->format('Ymd-His');

        if ($format === 'pdf') {
            return response(SimplePdfExporter::table($this->pageTitle($module, $request), $headers, $rows), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
            ]);
        }

        return response()->streamDownload(function () use ($headers, $rows): void {
            echo "<table border=\"1\"><thead><tr>";
            foreach ($headers as $header) {
                echo '<th>'.e($header).'</th>';
            }
            echo "</tr></thead><tbody>";
            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $value) {
                    echo '<td>'.e($value).'</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table>';
        }, $filename.'.xls', ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }
    public function status(Request $request, int|string $id): RedirectResponse
    {
        $module = $this->module();
        $record = $this->findRecord($id);
        $column = $module['status_column'] ?? (array_key_exists('is_active', $record->getAttributes()) ? 'is_active' : 'status');
        $allowed = $module['status_options'] ?? ['active', 'inactive'];
        $value = $column === 'is_active'
            ? $request->boolean('status')
            : $request->validate(['status' => ['required', Rule::in(array_keys($allowed))]])['status'];
        $record->forceFill([$column => $value])->save();
        return back()->with('success', 'Status updated successfully.');
    }
    protected function viewName(string $view): string
    {
        $moduleView = 'admin.'.$this->moduleKey.'.'.$view;

        return view()->exists($moduleView) ? $moduleView : 'admin.shared.'.($view === 'edit' || $view === 'create' ? 'form' : $view);
    }



    protected function validationAttributes(array $module): array
    {
        $attributes = [];

        foreach (($module['fields'] ?? []) as $field) {
            if (empty($field['name'])) {
                continue;
            }

            $attributes[$field['name']] = $field['label'] ?? Str::headline((string) $field['name']);
        }

        return $attributes;
    }

    protected function validationMessages(array $module): array
    {
        $messages = [];

        foreach (($module['fields'] ?? []) as $field) {
            if (empty($field['name'])) {
                continue;
            }

            $name = (string) $field['name'];
            $label = (string) ($field['label'] ?? Str::headline($name));

            $messages[$name.'.required'] = 'Enter '.$label.'.';
            $messages[$name.'.required_if'] = 'Enter '.$label.'.';
            $messages[$name.'.required_with'] = 'Enter '.$label.'.';
            $messages[$name.'.required_without'] = 'Enter '.$label.'.';
            $messages[$name.'.email'] = 'Enter valid '.$label.'.';
            $messages[$name.'.numeric'] = 'Enter valid number for '.$label.'.';
            $messages[$name.'.integer'] = 'Enter valid number for '.$label.'.';
            $messages[$name.'.date'] = 'Enter valid date for '.$label.'.';
            $messages[$name.'.url'] = 'Enter valid URL for '.$label.'.';
            $messages[$name.'.image'] = 'Upload valid image for '.$label.'.';
            $messages[$name.'.file'] = 'Upload valid file for '.$label.'.';
            $messages[$name.'.exists'] = 'Select valid '.$label.'.';
            $messages[$name.'.unique'] = $label.' already exists.';
            $messages[$name.'.min'] = 'Enter valid '.$label.'.';
            $messages[$name.'.max'] = 'Enter valid '.$label.'.';
            $messages[$name.'.in'] = 'Select valid '.$label.'.';
        }

        return $messages;
    }
    protected function module(): array
    {
        $module = config('admin.modules.'.$this->moduleKey);
        abort_if(! $module, 404);
        return array_merge(['key' => $this->moduleKey, 'singular' => Str::singular($module['label']), 'route' => 'admin.'.$this->moduleKey], $module);
    }

    protected function actionTitle(array $module, Request $request, string $action): string
    {
        $label = (string) ($module['singular'] ?? $module['label'] ?? 'Record');

        if ($request->filled('row_title')) {
            $rowTitle = (string) $request->query('row_title');
            $label = preg_replace('/^Row\s+\d+\s*-\s*/i', '', $rowTitle) ?: $rowTitle;
        }

        return trim($action.' '.$label);
    }

    protected function pageTitle(array $module, Request $request): string
    {
        if ($request->filled('row_title')) {
            return (string) $request->query('row_title');
        }

        $channel = $request->query('type');

        if (($module['group'] ?? null) === 'Sales' && in_array($channel, ['customer', 'dealer'], true)) {
            return Str::title((string) $channel).' '.$module['label'];
        }

        return $module['label'];
    }
    protected function records(Request $request): LengthAwarePaginator
    {
        $module = $this->module();
        [$column, $direction] = $module['sort'] ?? ['id', 'desc'];
        $allowedPerPage = [10, 25, 50, 100, 500];
        $requestedPerPage = (int) $request->integer('per_page', (int) ($module['per_page'] ?? 10));
        $perPage = in_array($requestedPerPage, $allowedPerPage, true) ? $requestedPerPage : 10;

        return $this->filteredRecordsQuery($request, $module)
            ->orderBy($column, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }

    protected function filteredRecordsQuery(Request $request, array $module): Builder
    {
        $query = $this->recordsQuery($module);

        if ($request->filled('search') && ! empty($module['search'])) {
            $term = trim((string) $request->input('search'));
            $searchColumn = (string) $request->input('search_column', '');
            $searchColumns = in_array($searchColumn, $module['search'], true) ? [$searchColumn] : $module['search'];

            $query->where(function (Builder $builder) use ($searchColumns, $term): void {
                foreach ($searchColumns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $builder->{$method}($column, 'like', '%'.$term.'%');
                }
            });
        }

        if ($request->filled('status') && ($module['status_column'] ?? null)) {
            $query->where($module['status_column'], $request->input('status'));
        }

        if ($request->filled('placement') && ($module['key'] ?? '') === 'storefront-banners') {
            $query->where('placement', $request->query('placement'));
        }

        if ($request->filled('section_key') && ($module['key'] ?? '') === 'storefront-sections') {
            $query->where('section_key', $request->query('section_key'));
        }

        if ($request->filled('section_key') && ($module['key'] ?? '') === 'storefront-section-products') {
            $query->whereHas('section', fn (Builder $builder) => $builder->where('section_key', $request->query('section_key')));
        }

        // storefront row filters
        if ($request->filled('placement') && ($module['key'] ?? '') === 'storefront-banners') {
            $query->where('placement', $request->query('placement'));
        }

        if ($request->filled('section_key') && ($module['key'] ?? '') === 'storefront-sections') {
            $query->where('section_key', $request->query('section_key'));
        }

        if ($request->filled('section_key') && ($module['key'] ?? '') === 'storefront-section-products') {
            $query->whereHas('section', fn (Builder $builder) => $builder->where('section_key', $request->query('section_key')));
        }

        foreach ($module['filters'] ?? [] as $filter) {
            if ($request->filled($filter['name'])) {
                $column = $filter['column'] ?? $filter['name'];
                $value = $request->input($filter['name']);

                if (! empty($filter['relation'])) {
                    $query->whereHas($filter['relation'], fn (Builder $builder) => $builder->where($column, $value));
                } else {
                    $query->where($column, $value);
                }
            }
        }

        $dateColumn = $module['date_column'] ?? 'created_at';
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date('date_from')->toDateString());
        }
        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date('date_to')->toDateString());
        }

        return $query;
    }

    protected function recordsQuery(array $module): Builder
    {
        $model = $module['model'];
        $query = $model::query();
        if (! empty($module['with'])) $query->with($module['with']);
        if (! empty($module['with_count'])) $query->withCount($module['with_count']);
        foreach ($module['where'] ?? [] as $column => $value) $query->where($column, $value);
        return $query;
    }

    protected function findRecord(int|string $id): Model
    {
        $module = $this->module();
        return $this->recordsQuery($module)->findOrFail($id);
    }

    protected function rules(array $module, ?Model $record = null): array
    {
        $rules = [];
        foreach ($module['fields'] ?? [] as $field) {
            if (($field['display_only'] ?? false) || ($field['name'] ?? '') === '') continue;
            $fieldRules = $field['rules'] ?? ['nullable'];
            $fieldRules = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
            $fieldRules = array_map(function ($rule) use ($record) {
                if (is_string($rule) && str_contains($rule, '{id}')) return str_replace('{id}', $record ? (string) $record->getKey() : 'NULL', $rule);
                return $rule;
            }, $fieldRules);
            if ($record && ($field['type'] ?? '') === 'password') $fieldRules = array_values(array_filter($fieldRules, fn ($r) => $r !== 'required'));
            $rules[$field['name']] = $fieldRules;
        }
        return $rules;
    }

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        foreach ($module['fields'] ?? [] as $field) {
            $name = $field['name'] ?? null;
            if (! $name) continue;

            $type = $field['type'] ?? null;

            if ($type === 'checkbox') {
                $validated[$name] = $request->boolean($name);
            }

            if ($type === 'password' && empty($validated[$name] ?? null)) {
                unset($validated[$name]);
            }

            if (in_array($type, ['file', 'image'], true)) {
                if ($request->hasFile($name)) {
                    $validated[$name] = $this->storePublicUpload($request->file($name), $module, $field);
                } else {
                    unset($validated[$name]);
                }
            }
        }

        if (array_key_exists('slug', $validated) && empty($validated['slug']) && ! empty($validated['name'])) $validated['slug'] = Str::slug($validated['name']);

        return $validated;
    }

    /**
     * Delegates to the injected uploader so the extension allow list that keeps
     * executable and script bearing files out of the public root lives in one
     * place instead of being duplicated per caller.
     */
    protected function storePublicUpload(UploadedFile $file, array $module, array $field): string
    {
        $directory = str_replace('\\', '/', trim((string) ($field['upload_dir'] ?? 'uploads/'.$module['key']), '/\\'));

        return $this->uploads->store($file, $directory);
    }

    protected function persist(array $data, ?Model $record): Model
    {
        if ($record) {
            $record->fill($data)->save();
            $this->bumpCacheVersionForModule();

            return $record->fresh();
        }

        $model = $this->module()['model'];
        $record = $model::query()->create($data);
        $this->bumpCacheVersionForModule();

        return $record;
    }

    protected function bumpCacheVersionForModule(): void
    {
        if (! in_array($this->moduleKey, ['products', 'product-related-products', 'pricing', 'categories', 'brands', 'units', 'inventory', 'warehouses', 'homepage-settings', 'homepage-setting-items', 'languages', 'translations', 'storefront-banners', 'storefront-sections', 'storefront-section-products', 'storefront-service-blocks', 'storefront-footer-links'], true)) {
            return;
        }

        Cache::forever('catalog_cache_version', ((int) Cache::get('catalog_cache_version', 1)) + 1);
    }

    protected function exportValue(Model $record, array $column): string
    {
        $value = data_get($record, $column['key']);

        return match ($column['type'] ?? 'text') {
            'boolean' => $value ? 'Active' : 'Inactive',
            'status' => Str::of((string) $value)->replace('_', ' ')->title()->toString(),
            'money' => number_format((float) $value, 2),
            'date' => $value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y') : '',
            'datetime' => $value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y h:i A') : '',
            default => (string) ($value ?? ''),
        };
    }
    protected function createFormData(Request $request, array $module): array
    {
        $data = [];

        foreach ($module['fields'] ?? [] as $field) {
            $name = $field['name'] ?? null;
            if (! $name) continue;

            $queryKey = $field['query_key'] ?? $name;
            if ($request->filled($queryKey)) {
                $data[$name] = $request->input($queryKey);
            }
        }

        if (($module['key'] ?? '') === 'storefront-section-products' && $request->filled('section_key')) {
            $section = \App\Models\Storefront\StorefrontSection::query()
                ->where('section_key', $request->query('section_key'))
                ->first();

            if ($section) {
                $data['section_id'] = $section->id;
            }
        }

        if (($module['key'] ?? '') === 'storefront-section-products' && $request->filled('section_key')) {
            $section = \App\Models\Storefront\StorefrontSection::query()
                ->where('section_key', $request->query('section_key'))
                ->first();

            if ($section) {
                $data['section_id'] = $section->id;
            }
        }

        if (($module['key'] ?? '') === 'storefront-sections' && $request->query('section_key') === 'row_16_blog') {
            $data['title'] = $data['title'] ?? (string) $request->query('row_title', 'Bottom Blog');
            $data['section_type'] = $data['section_type'] ?? 'offer';
            $data['source_type'] = $data['source_type'] ?? 'manual';
            $data['product_limit'] = $data['product_limit'] ?? 3;
            $data['sort_order'] = $data['sort_order'] ?? 16;
            $data['is_active'] = $data['is_active'] ?? true;
        }

        return $data;
    }

    protected function formData(Model $record, array $module): array
    {
        $data = [];
        foreach ($module['fields'] ?? [] as $field) {
            $name = $field['name'] ?? null;
            if ($name) {
                $value = data_get($record, $field['source'] ?? $name);
                if ($value && ($field['type'] ?? null) === 'datetime-local') {
                    $value = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i');
                }
                if ($value && ($field['type'] ?? null) === 'date') {
                    $value = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
                }
                $data[$name] = $value;
            }
        }
        return $data;
    }

    protected function formOptions(array $module): array
    {
        $options = [];
        foreach ($module['fields'] ?? [] as $field) {
            if (($field['type'] ?? null) !== 'select' || empty($field['name'])) continue;
            if (isset($field['options'])) { $options[$field['name']] = $field['options']; continue; }
            if (isset($field['option_model'])) {
                $query = $field['option_model']::query();
                foreach ($field['option_where'] ?? [] as $column => $value) $query->where($column, $value);
                $label = $field['option_label'] ?? 'name';
                $options[$field['name']] = $query->orderBy($label)->pluck($label, $field['option_value'] ?? 'id')->all();
            }
        }
        return $options;
    }

    protected function formOptionAttributes(array $module): array
    {
        $attributes = [];

        foreach ($module['fields'] ?? [] as $field) {
            if (($field['type'] ?? null) !== 'select' || empty($field['name']) || empty($field['option_model']) || empty($field['option_attributes'])) {
                continue;
            }

            $query = $field['option_model']::query();

            foreach ($field['option_where'] ?? [] as $column => $value) {
                $query->where($column, $value);
            }

            $labelColumn = $field['option_label'] ?? 'name';
            $valueColumn = $field['option_value'] ?? 'id';
            $attributeMap = (array) $field['option_attributes'];
            $columns = array_values(array_unique(array_merge([$valueColumn, $labelColumn], array_values($attributeMap))));

            foreach ($query->orderBy($labelColumn)->get($columns) as $option) {
                $key = data_get($option, $valueColumn);

                foreach ($attributeMap as $attributeName => $column) {
                    $attributes[$field['name']][$key][$attributeName] = data_get($option, $column);
                }
            }
        }

        return $attributes;
    }
}

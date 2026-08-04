<?php

namespace App\Http\Controllers\Admin\Concerns;

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
            'pageTitle' => 'Add '.$module['singular'],
            'breadcrumbs' => ['Admin', $module['label'], 'Add'],
            'record' => null,
            'formData' => $this->createFormData($request, $module),
            'options' => $this->formOptions($module),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_create'] ?? true), 403);
        $validated = $request->validate($this->rules($module));
        $record = $this->persist($this->prepareData($validated, $request, $module), null);
        return redirect()->route($module['route'].'.edit', $record->getKey())->with('success', $module['singular'].' created successfully.');
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
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_edit'] ?? true), 403);
        $record = $this->findRecord($id);
        $validated = $request->validate($this->rules($module, $record));
        $record = $this->persist($this->prepareData($validated, $request, $module), $record);
        return back()->with('success', $module['singular'].' updated successfully.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_delete'] ?? true), 403);
        $record = $this->findRecord($id);
        $record->delete();
        return redirect()->route($module['route'].'.index')->with('success', $module['singular'].' deleted successfully.');
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


    protected function module(): array
    {
        $module = config('admin.modules.'.$this->moduleKey);
        abort_if(! $module, 404);
        return array_merge(['key' => $this->moduleKey, 'singular' => Str::singular($module['label']), 'route' => 'admin.'.$this->moduleKey], $module);
    }

    protected function pageTitle(array $module, Request $request): string
    {
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
        $perPage = min(max(5, (int) $request->integer('per_page', (int) ($module['per_page'] ?? 20))), 100);

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

    protected function storePublicUpload(UploadedFile $file, array $module, array $field): string
    {
        $directory = trim((string) ($field['upload_dir'] ?? 'uploads/'.$module['key']), '/\\');
        $directory = str_replace('\\', '/', $directory);

        abort_if($directory === '' || str_contains($directory, '..'), 422, 'Invalid upload directory.');

        $publicDirectory = public_path($directory);
        if (! is_dir($publicDirectory)) {
            mkdir($publicDirectory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = now()->format('YmdHis').'-'.Str::random(16).'.'.$extension;
        $file->move($publicDirectory, $filename);

        return $directory.'/'.$filename;
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
        if (! in_array($this->moduleKey, ['products', 'pricing', 'categories', 'brands', 'units', 'translations'], true)) {
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
}



<?php

namespace App\Http\Controllers\Admin\ProductHomepageSettings;

use App\Contracts\Catalog\Homepage\HomepageSectionContentContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\ProductHomepageSection;
use App\Services\Catalog\Homepage\HomepageSectionContentFields;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * SRP: the content rows of one homepage section, nothing else.
 *
 * Split out of ProductHomepageSettingController so each stays well under the
 * 200-line rule, and because editing a section's rows is a different job from
 * the section CRUD itself. Both Add Content and Edit Content open `content()`.
 */
final class HomepageSectionContentController extends AdminModuleController
{
    protected string $moduleKey = 'homepage-settings';

    public function __construct(
        AdminModuleServices $modules,
        private readonly HomepageSectionContentContract $sectionContent,
        private readonly HomepageSectionContentFields $contentFields,
    ) {
        parent::__construct($modules);
    }

    public function content(int|string $id): View
    {
        $section = $this->editableSection($id);

        return view('admin.homepage-settings.content', [
            'module' => $this->module(),
            'pageTitle' => $section->title.' - Content',
            'record' => $section,
            'rows' => $this->sectionContent->rowsFor($section),
            'rowLimit' => $section->contentRowLimit(),
            'sectionFields' => $this->contentFields->for($section),
            'fieldHelper' => $this->contentFields,
            'productOptions' => $this->contentFields->has($section, 'product_id')
                ? $this->sectionContent->productOptions()
                : [],
        ]);
    }

    public function saveContent(Request $request, int|string $id): RedirectResponse
    {
        $section = $this->editableSection($id);

        $rows = (array) $request->input('rows', []);
        $files = (array) ($request->allFiles()['rows'] ?? []);
        $limit = $section->contentRowLimit();

        // The editor renders only the allowed number of rows, so a bigger post
        // can only come from a tampered form.
        if ($limit !== null && count($rows) > $limit) {
            return back()->withInput()->with('error', 'This section renders only '.$limit.' row(s).');
        }

        $request->validate(
            array_merge(
                $this->sectionContent->rules($section, $rows, $files),
                $this->sectionContent->timerRules($section),
            ),
            $this->sectionContent->validationMessages($section, $rows),
        );

        $result = $this->sectionContent->save($section, $rows, $files);
        // Row 1 of Top Selling carries the Special Offer countdown, which is
        // stored on the section itself.
        $this->sectionContent->saveTimer($section, (array) $request->input('deal', []));
        $this->bumpCacheVersionForModule();

        return back()->with('success', $result['saved'].' row(s) saved'
            .($result['deleted'] > 0 ? ', '.$result['deleted'].' removed' : '').'.');
    }

    /**
     * Only a row with content has rows to edit: every content section, plus
     * Top Selling, whose products are picked here. A plain product row or the
     * category slider has none, so the editor does not exist for them.
     */
    private function editableSection(int|string $id): ProductHomepageSection
    {
        $module = $this->module();
        abort_unless(($module['can_edit'] ?? true), 403);

        $section = $this->findRecord($id);
        abort_unless($section->hasContentEditor(), 404);

        return $section;
    }
}

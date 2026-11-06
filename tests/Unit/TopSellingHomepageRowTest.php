<?php

namespace Tests\Unit;

use App\Models\Catalog\ProductHomepageSection;
use App\Services\Catalog\Homepage\HomepageSectionContentFields;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Top Selling takes its products from its own content rows, like every other
 * homepage row, instead of from the `is_top_selling` / `is_deal_timer_product`
 * flags that used to sit on the Products form.
 */
class TopSellingHomepageRowTest extends TestCase
{
    public function test_top_selling_is_a_product_row_that_is_edited_as_content(): void
    {
        $section = $this->section('top_selling_section');

        // It still renders product cards, so the API keeps sending products...
        $this->assertTrue($section->isProductSection());
        $this->assertFalse($section->isContentSection());

        // ...but the products are picked in the content editor, which is
        // therefore reachable for it.
        $this->assertTrue($section->usesContentRowProducts());
        $this->assertTrue($section->hasContentEditor());

        // And a product can no longer be placed on it from the Products form.
        $this->assertFalse($section->isAssignableFromProductForm());
        $this->assertNotContains('top_selling_section', ProductHomepageSection::ASSIGNABLE_SECTION_TYPES);
    }

    public function test_a_plain_product_row_is_unchanged(): void
    {
        $section = $this->section('product_section');

        $this->assertTrue($section->isProductSection());
        $this->assertTrue($section->isAssignableFromProductForm());
        $this->assertFalse($section->usesContentRowProducts());
        $this->assertFalse($section->hasContentEditor());
    }

    public function test_a_banner_row_still_opens_the_content_editor(): void
    {
        $section = $this->section('hero_slider');

        $this->assertTrue($section->isContentSection());
        $this->assertTrue($section->hasContentEditor());
        $this->assertFalse($section->usesContentRowProducts());
    }

    public function test_the_row_offers_the_deal_card_plus_eight_products(): void
    {
        $this->assertSame(9, $this->section('top_selling_section')->contentRowLimit());
    }

    public function test_a_top_selling_row_only_asks_for_a_product(): void
    {
        $fields = new HomepageSectionContentFields;

        $this->assertSame(['product_id'], $fields->for($this->section('top_selling_section')));
        $this->assertSame(['product_id'], $fields->columns($this->section('top_selling_section')));
    }

    public function test_the_first_row_is_named_the_deal_card(): void
    {
        $fields = new HomepageSectionContentFields;
        $topSelling = $this->section('top_selling_section');

        $this->assertSame('Special Offer (deal card)', $fields->rowLabel($topSelling, 1));
        $this->assertSame('Product 1', $fields->rowLabel($topSelling, 2));
        $this->assertSame('Product 8', $fields->rowLabel($topSelling, 9));

        // Every other section type keeps the plain numbering.
        $this->assertSame('Row 1', $fields->rowLabel($this->section('hero_slider'), 1));
    }

    public function test_the_countdown_needs_both_the_switch_and_an_end_date(): void
    {
        $this->assertFalse($this->dealSection(true, null)->dealTimerRunning(), 'no end date');
        $this->assertFalse($this->dealSection(false, '+2 days')->dealTimerRunning(), 'switch off');
        $this->assertFalse($this->dealSection(true, '-1 hour')->dealTimerRunning(), 'already ended');
        $this->assertTrue($this->dealSection(true, '+2 days')->dealTimerRunning());
    }

    public function test_a_countdown_that_has_not_started_yet_does_not_run(): void
    {
        $this->assertFalse($this->dealSection(true, '+5 days', '+1 day')->dealTimerRunning());
        $this->assertTrue($this->dealSection(true, '+5 days', '-1 day')->dealTimerRunning());
    }

    private function section(string $type): ProductHomepageSection
    {
        return new ProductHomepageSection(['section_type' => $type, 'title' => 'Row']);
    }

    /**
     * The `datetime` cast reads the date format off the database connection,
     * and these tests boot no application - so the format is stated here
     * instead of standing up a connection just to compare two dates.
     */
    private function dealSection(bool $active, ?string $endsAt, ?string $startsAt = null): ProductHomepageSection
    {
        $section = new class extends ProductHomepageSection
        {
            public function getDateFormat(): string
            {
                return 'Y-m-d H:i:s';
            }
        };

        $section->setRawAttributes([
            'section_type' => 'top_selling_section',
            'deal_timer_active' => $active ? 1 : 0,
            'deal_starts_at' => $startsAt === null ? null : Carbon::parse($startsAt)->format('Y-m-d H:i:s'),
            'deal_ends_at' => $endsAt === null ? null : Carbon::parse($endsAt)->format('Y-m-d H:i:s'),
        ], true);

        return $section;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Catalog\Product;
use App\Models\Storefront\StorefrontBanner;
use App\Models\Storefront\StorefrontFooterLink;
use App\Models\Storefront\StorefrontSection;
use App\Models\Storefront\StorefrontSectionProduct;
use App\Models\Storefront\StorefrontServiceBlock;
use Illuminate\Database\Seeder;

class StorefrontSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            ['placement'=>'hero_main','title'=>'Premium Quality Dry Fruits','subtitle'=>'Weekend Special offer','description'=>'Fresh & Top Quality Dry Fruits are available here!','button_text'=>'Shop Now','button_url'=>'/shop-left-sidebar','image_path'=>'fastkart-store/images/grocery/banner/1.jpg','sort_order'=>1],
            ['placement'=>'promo_small','title'=>'Summer Ice Cream','subtitle'=>'50% Discount','button_text'=>'Shop Now','button_url'=>'/shop-left-sidebar','image_path'=>'fastkart-store/images/grocery/banner/2.jpg','sort_order'=>1],
            ['placement'=>'promo_small','title'=>'Fruits Juice Series','subtitle'=>'Today Special','button_text'=>'Shop Now','button_url'=>'/shop-left-sidebar','image_path'=>'fastkart-store/images/grocery/banner/3.jpg','sort_order'=>2],
            ['placement'=>'promo_small','title'=>'Eat Healthy Be Healthy','subtitle'=>'Combo Offer','button_text'=>'Shop Now','button_url'=>'/shop-left-sidebar','image_path'=>'fastkart-store/images/grocery/banner/4.jpg','sort_order'=>3],
            ['placement'=>'promo_small','title'=>'As Fresh As Fruit','subtitle'=>'Amazing Deals','button_text'=>'Shop Now','button_url'=>'/shop-left-sidebar','image_path'=>'fastkart-store/images/grocery/banner/5.jpg','sort_order'=>4],
            ['placement'=>'bank_offer','title'=>'GET 10% OFF','subtitle'=>'When you spend $20','description'=>'Valid for 30 days','button_text'=>'Copy Code','button_url'=>'MULTICART','image_path'=>'fastkart-store/images/grocery/bank/price/1.svg','sort_order'=>1],
            ['placement'=>'middle_promo','title'=>'Fresh Fruits','subtitle'=>'Weekend Special','button_text'=>'Shop Now','button_url'=>'/shop-left-sidebar','image_path'=>'fastkart-store/images/grocery/banner/8.png','sort_order'=>1],
            ['placement'=>'footer_promo','title'=>'Healthy Food','subtitle'=>'Fresh Grocery','button_text'=>'Shop Now','button_url'=>'/shop-left-sidebar','image_path'=>'fastkart-store/images/grocery/banner/9.jpg','sort_order'=>1],
        ];

        foreach ($banners as $banner) {
            StorefrontBanner::query()->updateOrCreate(
                ['placement' => $banner['placement'], 'sort_order' => $banner['sort_order']],
                $banner + ['is_active' => true]
            );
        }

        $sections = [
            ['section_key'=>'shop_by_categories','title'=>'Shop By Categories','section_type'=>'category','source_type'=>'category','product_limit'=>12,'sort_order'=>1],
            ['section_key'=>'fruits_vegetables','title'=>'Fruits & Vegetables','section_type'=>'product','source_type'=>'manual','product_limit'=>12,'sort_order'=>2],
            ['section_key'=>'bank_wallet_offers','title'=>'Bank & Wallet Offers','section_type'=>'offer','source_type'=>'manual','product_limit'=>4,'sort_order'=>3],
            ['section_key'=>'top_selling_items','title'=>'Top Selling Items','section_type'=>'product','source_type'=>'top_selling','product_limit'=>8,'sort_order'=>4],
            ['section_key'=>'breakfast_dairy','title'=>'Breakfast & Dairy','section_type'=>'product','source_type'=>'manual','product_limit'=>12,'sort_order'=>5],
            ['section_key'=>'chemists_store','title'=>'Chemist Store','section_type'=>'product','source_type'=>'manual','product_limit'=>12,'sort_order'=>6],
            ['section_key'=>'drinks','title'=>'Drinks','section_type'=>'product','source_type'=>'manual','product_limit'=>12,'sort_order'=>7],
            ['section_key'=>'grocery_staples','title'=>'Grocery & Staples','section_type'=>'product','source_type'=>'manual','product_limit'=>12,'sort_order'=>8],
            ['section_key'=>'personal_care','title'=>'Personal Care','section_type'=>'product','source_type'=>'manual','product_limit'=>12,'sort_order'=>9],
            ['section_key'=>'kitchen_dining','title'=>'Kitchen & Dining Needs','section_type'=>'product','source_type'=>'manual','product_limit'=>12,'sort_order'=>10],
        ];

        foreach ($sections as $section) {
            StorefrontSection::query()->updateOrCreate(
                ['section_key' => $section['section_key']],
                $section + ['is_active' => true]
            );
        }

        $demoProduct = Product::query()->first();
        if ($demoProduct) {
            StorefrontSection::query()->where('section_type', 'product')->get()->each(function (StorefrontSection $section) use ($demoProduct): void {
                StorefrontSectionProduct::query()->updateOrCreate(
                    ['section_id' => $section->id, 'product_id' => $demoProduct->id],
                    ['sort_order' => 1, 'is_active' => true]
                );
            });
        }

        $services = [
            ['title'=>'Free Shipping','subtitle'=>'Free Shipping world wide','icon_path'=>'fastkart-store/svg/svg/service-icon-4.svg#shipping','sort_order'=>1],
            ['title'=>'24 x 7 Service','subtitle'=>'Online Service For 24 x 7','icon_path'=>'fastkart-store/svg/svg/service-icon-4.svg#service','sort_order'=>2],
            ['title'=>'Online Pay','subtitle'=>'Online Payment Available','icon_path'=>'fastkart-store/svg/svg/service-icon-4.svg#pay','sort_order'=>3],
            ['title'=>'Festival Offer','subtitle'=>'Super Sale Upto 50% off','icon_path'=>'fastkart-store/svg/svg/service-icon-4.svg#offer','sort_order'=>4],
            ['title'=>'100% Original','subtitle'=>'100% Money Back','icon_path'=>'fastkart-store/svg/svg/service-icon-4.svg#return','sort_order'=>5],
        ];

        foreach ($services as $service) {
            StorefrontServiceBlock::query()->updateOrCreate(['title' => $service['title']], $service + ['is_active' => true]);
        }

        $links = [
            ['link_group'=>'about','title'=>'About Us','url'=>'/about-us','sort_order'=>1],
            ['link_group'=>'about','title'=>'Contact Us','url'=>'/contact-us','sort_order'=>2],
            ['link_group'=>'about','title'=>'Latest Products','url'=>'/shop-left-sidebar','sort_order'=>3],
            ['link_group'=>'useful','title'=>'Your Order','url'=>'/order-success','sort_order'=>1],
            ['link_group'=>'useful','title'=>'Your Account','url'=>'/user-dashboard','sort_order'=>2],
            ['link_group'=>'useful','title'=>'Track Orders','url'=>'/order-tracking','sort_order'=>3],
            ['link_group'=>'useful','title'=>'Your Wishlist','url'=>'/wishlist','sort_order'=>4],
            ['link_group'=>'useful','title'=>'FAQs','url'=>'/faq','sort_order'=>5],
            ['link_group'=>'categories','title'=>'Fresh Vegetables','url'=>'/shop-left-sidebar','sort_order'=>1],
            ['link_group'=>'categories','title'=>'Farmer Medicine','url'=>'/shop-left-sidebar','sort_order'=>2],
            ['link_group'=>'categories','title'=>'Fertilizer','url'=>'/shop-left-sidebar','sort_order'=>3],
            ['link_group'=>'categories','title'=>'Seeds','url'=>'/shop-left-sidebar','sort_order'=>4],
            ['link_group'=>'categories','title'=>'Veterinary Products','url'=>'/shop-left-sidebar','sort_order'=>5],
        ];

        foreach ($links as $link) {
            StorefrontFooterLink::query()->updateOrCreate(
                ['link_group' => $link['link_group'], 'title' => $link['title']],
                $link + ['is_active' => true]
            );
        }
    }
}
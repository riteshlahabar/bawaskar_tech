<?php

use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Catalog\ProductRelatedProduct;
use App\Models\Catalog\ProductType;
use App\Models\Catalog\ProductVariant;
use App\Models\Catalog\Unit;
use App\Models\Communication\AppTranslation;
use App\Models\Communication\Language;
use App\Models\Communication\Notification;
use App\Models\Communication\SupportTicket;
use App\Models\Courier;
use App\Models\DealerProfile;
use App\Models\Field\AttendanceLog;
use App\Models\Field\DealerVisit;
use App\Models\Field\Expense;
use App\Models\Field\LeaveApplication;
use App\Models\Field\SalarySlip;
use App\Models\Field\SalesmanAsset;
use App\Models\Field\SalesmanTarget;
use App\Models\Field\TourPlan;
use App\Models\Finance\Payment;
use App\Models\InternalExpense;
use App\Models\InternalExpenseCategory;
use App\Models\InternalExpenseSubcategory;
use App\Models\Inventory\InventoryBatch;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\ProformaInvoice;
use App\Models\Sales\ReturnRequest;
use App\Models\User;

$active = ['1' => 'Active', '0' => 'Inactive'];
$userStatus = ['active' => 'Active', 'inactive' => 'Inactive', 'pending_approval' => 'Pending Approval'];
$orderStatus = ['salesman_review' => 'Salesman Review', 'admin_review' => 'Admin Review', 'approved' => 'Approved', 'packing' => 'Packing', 'dispatched' => 'Dispatched', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
$approvalStatus = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];

return [
    'brand' => ['name' => 'Bawaskar ERP', 'short_name' => 'BERP'],
    'groups' => [
        ['label' => 'Navigation', 'items' => [
            ['key' => 'dashboard-erp', 'label' => 'Dashboard ERP', 'route' => 'admin.dashboard', 'icon' => 'iconoir-report-columns'],
            ['key' => 'dashboard-hrms', 'label' => 'Dashboard HRMS', 'route' => 'admin.dashboard.hrms', 'icon' => 'iconoir-community'],
        ]],
        ['label' => 'People', 'id' => 'peopleMenu', 'icon' => 'iconoir-community', 'items' => [
            ['key' => 'dealers', 'label' => 'Dealers', 'route' => 'admin.dealers.index', 'icon' => 'iconoir-shop'], ['key' => 'customers', 'label' => 'Customers', 'route' => 'admin.customers.index', 'icon' => 'iconoir-user-love'], ['key' => 'salesmen', 'label' => 'Salesmen', 'route' => 'admin.salesmen.index', 'icon' => 'iconoir-user-badge-check'], ['key' => 'couriers', 'label' => 'Courier', 'route' => 'admin.couriers.index', 'icon' => 'iconoir-delivery-truck'], ]],

        ['label' => 'Sales', 'id' => 'salesMenu', 'icon' => 'iconoir-reports', 'items' => [
            ['key' => 'customer-sales', 'label' => 'Customer', 'id' => 'customerSalesMenu', 'icon' => 'iconoir-user', 'children' => [
                ['key' => 'customer-orders', 'label' => 'Sale Orders', 'route' => 'admin.orders.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-cart'],
                ['key' => 'customer-proforma-invoices', 'label' => 'Proforma Invoices', 'route' => 'admin.proforma-invoices.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-page'],
                ['key' => 'customer-invoices', 'label' => 'Sale Invoices', 'route' => 'admin.invoices.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-receipt'],
                ['key' => 'customer-dispatches', 'label' => 'Dispatch & Delivery', 'route' => 'admin.dispatches.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-delivery-truck'],
                ['key' => 'customer-returns', 'label' => 'Returns & Cancellation', 'route' => 'admin.returns.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-undo-action'],
            ]],
            ['key' => 'dealer-sales', 'label' => 'Dealer', 'id' => 'dealerSalesMenu', 'icon' => 'iconoir-shop', 'children' => [
                ['key' => 'dealer-orders', 'label' => 'Sale Orders', 'route' => 'admin.orders.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-cart'],
                ['key' => 'dealer-proforma-invoices', 'label' => 'Proforma Invoices', 'route' => 'admin.proforma-invoices.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-page'],
                ['key' => 'dealer-invoices', 'label' => 'Sale Invoices', 'route' => 'admin.invoices.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-receipt'],
                ['key' => 'dealer-dispatches', 'label' => 'Dispatch & Delivery', 'route' => 'admin.dispatches.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-delivery-truck'],
                ['key' => 'dealer-returns', 'label' => 'Returns & Cancellation', 'route' => 'admin.returns.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-undo-action'],
            ]],
        ]],
        ['label' => 'Products & Inventory', 'id' => 'productInventoryMenu', 'icon' => 'iconoir-box', 'items' => [
            ['key' => 'products', 'label' => 'Products', 'route' => 'admin.products.index', 'icon' => 'iconoir-box-iso'],
            ['key' => 'product-related-products', 'label' => 'Related Products', 'route' => 'admin.product-related-products.index', 'icon' => 'iconoir-link'],
            ['key' => 'product-types', 'label' => 'Product Types', 'route' => 'admin.product-types.index', 'icon' => 'iconoir-list'],
            ['key' => 'categories', 'label' => 'Category', 'route' => 'admin.categories.index', 'icon' => 'iconoir-list-select'],
            ['key' => 'brands', 'label' => 'Brand', 'route' => 'admin.brands.index', 'icon' => 'iconoir-medal'],
            ['key' => 'units', 'label' => 'Unit', 'route' => 'admin.units.index', 'icon' => 'iconoir-ruler'],
            ['key' => 'inventory', 'label' => 'Stock', 'route' => 'admin.inventory.index', 'icon' => 'iconoir-package'],
            ['key' => 'warehouses', 'label' => 'Warehouse', 'route' => 'admin.warehouses.index', 'icon' => 'iconoir-home-alt'],
            ['key' => 'homepage-settings', 'label' => 'Homepage Settings', 'route' => 'admin.homepage-settings.index', 'icon' => 'iconoir-www'],
        ]],        ['label' => 'Expense', 'id' => 'companyExpenseMenu', 'icon' => 'iconoir-receive-dollars', 'items' => [
            ['key' => 'internal-expenses', 'label' => 'Expense List', 'route' => 'admin.internal-expenses.index', 'icon' => 'iconoir-notes'], ['key' => 'expense-categories', 'label' => 'Category List', 'route' => 'admin.expense-categories.index', 'icon' => 'iconoir-list-select'], ['key' => 'expense-subcategories', 'label' => 'Subcategory List', 'route' => 'admin.expense-subcategories.index', 'icon' => 'iconoir-list'], ]],
        ['label' => 'HRMS', 'items' => [
            ['key' => 'timesheet', 'label' => 'Timesheet', 'id' => 'timesheetMenu', 'icon' => 'iconoir-calendar', 'children' => [['key' => 'attendance', 'label' => 'Attendance', 'route' => 'admin.attendance.index', 'icon' => 'iconoir-check-circle'], ['key' => 'leaves', 'label' => 'Leave', 'route' => 'admin.leaves.index', 'icon' => 'iconoir-calendar-minus'], ['key' => 'bulk-attendance', 'label' => 'Bulk Attendance', 'route' => 'admin.attendance.bulk', 'icon' => 'iconoir-table-rows']]], ['key' => 'dealer-visits', 'label' => 'Dealer Visits', 'route' => 'admin.dealer-visits.index', 'icon' => 'iconoir-map-pin'], ['key' => 'tour-plans', 'label' => 'Tour Plans', 'route' => 'admin.tour-plans.index', 'icon' => 'iconoir-route'], ['key' => 'expenses', 'label' => 'Expenses', 'route' => 'admin.expenses.index', 'icon' => 'iconoir-receive-dollars'], ['key' => 'salary', 'label' => 'Salary & Payroll', 'route' => 'admin.salary.index', 'icon' => 'iconoir-coins'], ['key' => 'targets', 'label' => 'Targets & Commission', 'route' => 'admin.targets.index', 'icon' => 'iconoir-target'], ['key' => 'assets', 'label' => 'Salesman Assets', 'route' => 'admin.assets.index', 'icon' => 'iconoir-laptop'], ]],
        ['label' => 'System', 'id' => 'systemMenu', 'icon' => 'iconoir-settings', 'items' => [
            ['key' => 'company-settings', 'label' => 'Seller / Company Information', 'route' => 'admin.company-settings.edit', 'icon' => 'iconoir-building'], ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'admin.notifications.index', 'icon' => 'iconoir-bell'], ['key' => 'email-templates', 'label' => 'Email Templates', 'route' => 'admin.email-templates.index', 'icon' => 'iconoir-mail'], ['key' => 'languages', 'label' => 'Languages', 'route' => 'admin.languages.index', 'icon' => 'iconoir-language'], ['key' => 'translations', 'label' => 'Translations', 'route' => 'admin.translations.index', 'icon' => 'iconoir-language'], ['key' => 'support', 'label' => 'Support', 'route' => 'admin.support.index', 'icon' => 'iconoir-headset-help'], ['key' => 'reports', 'label' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'iconoir-stats-report'], ]],
    ],
    'modules' => [
        'salesmen' => [
            'label' => 'Salesmen', 'group' => 'People', 'description' => 'Company field-sales employees with email and password login.', 'model' => User::class, 'with' => ['salesmanProfile'], 'search' => ['name', 'email', 'mobile'], 'status_column' => 'status', 'status_options' => $userStatus,
            'columns' => [['key' => 'salesmanProfile.employee_code', 'label' => 'Employee Code'], ['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email'], ['key' => 'mobile', 'label' => 'Mobile'], ['key' => 'salesmanProfile.territory', 'label' => 'Territory'], ['key' => 'salesmanProfile.basic_salary', 'label' => 'Basic Salary', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'name', 'label' => 'Full Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => ['required', 'email', 'max:255', 'unique:users,email,{id}']], ['name' => 'mobile', 'label' => 'Mobile', 'rules' => ['nullable', 'string', 'max:20', 'unique:users,mobile,{id}']], ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'rules' => ['required', 'string', 'min:8'], 'help' => 'Leave blank while editing to keep the existing password.'], ['name' => 'employee_code', 'label' => 'Employee Code', 'rules' => ['required', 'string', 'max:50', 'unique:salesman_profiles,employee_code,{id}']], ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'territory', 'label' => 'Territory', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'basic_salary', 'label' => 'Basic Salary', 'type' => 'number', 'step' => '0.01', 'default' => 0, 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'target_amount', 'label' => 'Monthly Target', 'type' => 'number', 'step' => '0.01', 'default' => 0, 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $userStatus, 'rules' => ['required', 'in:active,inactive']]],
        ],
        'dealers' => [
            'label' => 'Dealers', 'group' => 'People', 'description' => 'B2B dealer registration, approval, salesman assignment, credit and outstanding.', 'model' => User::class, 'with' => ['dealerProfile.salesman'], 'search' => ['name', 'email', 'mobile'], 'status_column' => 'status', 'status_options' => $userStatus,
            'columns' => [['key' => 'dealerProfile.dealer_code', 'label' => 'Dealer Code'], ['key' => 'dealerProfile.firm_name', 'label' => 'Firm'], ['key' => 'name', 'label' => 'Contact Person'], ['key' => 'mobile', 'label' => 'Mobile'], ['key' => 'dealerProfile.salesman.name', 'label' => 'Salesman'], ['key' => 'dealerProfile.credit_limit', 'label' => 'Credit Limit', 'type' => 'money'], ['key' => 'dealerProfile.outstanding_balance', 'label' => 'Outstanding', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'name', 'label' => 'Contact Person', 'rules' => ['required', 'string', 'max:255']], ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255', 'unique:users,email,{id}']], ['name' => 'mobile', 'label' => 'Mobile', 'rules' => ['required', 'string', 'max:20', 'unique:users,mobile,{id}']], ['name' => 'dealer_code', 'label' => 'Dealer Code', 'rules' => ['required', 'string', 'max:50', 'unique:dealer_profiles,dealer_code,{id}']], ['name' => 'firm_name', 'label' => 'Firm Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'gst_number', 'label' => 'GST Number', 'rules' => ['nullable', 'string', 'max:30']], ['name' => 'salesman_id', 'label' => 'Assigned Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman', 'status' => 'active'], 'rules' => ['nullable', 'exists:users,id']], ['name' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'outstanding_balance', 'label' => 'Outstanding Balance', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $userStatus, 'rules' => ['required', 'in:active,inactive,pending_approval']]],
        ],
        'customers' => [
            'label' => 'Customers', 'group' => 'People', 'description' => 'B2C retail customer accounts and language preferences.', 'model' => User::class, 'with' => ['customerProfile'], 'search' => ['name', 'email', 'mobile'], 'status_column' => 'status', 'status_options' => $userStatus,
            'columns' => [['key' => 'name', 'label' => 'Name'], ['key' => 'mobile', 'label' => 'Mobile'], ['key' => 'email', 'label' => 'Email'], ['key' => 'customerProfile.preferred_language', 'label' => 'Language'], ['key' => 'created_at', 'label' => 'Registered', 'type' => 'date'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'name', 'label' => 'Full Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255', 'unique:users,email,{id}']], ['name' => 'mobile', 'label' => 'Mobile', 'rules' => ['required', 'string', 'max:20', 'unique:users,mobile,{id}']], ['name' => 'date_of_birth', 'label' => 'Date of Birth', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'preferred_language', 'label' => 'Preferred Language', 'type' => 'select', 'option_model' => Language::class, 'option_where' => ['is_active' => 1], 'option_value' => 'code', 'option_label' => 'name', 'rules' => ['required', 'string', 'max:10']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $userStatus, 'rules' => ['required', 'in:active,inactive']]],
        ],
        'couriers' => [
            'label' => 'Courier', 'group' => 'People', 'singular' => 'Courier Person', 'description' => 'Courier and delivery person master details.', 'model' => Courier::class, 'search' => ['courier_code', 'name', 'mobile', 'company_name', 'vehicle_number'], 'status_column' => 'status', 'status_options' => ['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On Leave'],
            'columns' => [['key' => 'courier_code', 'label' => 'Courier Code'], ['key' => 'name', 'label' => 'Name'], ['key' => 'mobile', 'label' => 'Mobile'], ['key' => 'company_name', 'label' => 'Company'], ['key' => 'vehicle_type', 'label' => 'Vehicle'], ['key' => 'vehicle_number', 'label' => 'Vehicle No.'], ['key' => 'service_area', 'label' => 'Service Area'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'courier_code', 'label' => 'Courier Code', 'rules' => ['nullable', 'string', 'max:50', 'unique:couriers,courier_code,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'name', 'label' => 'Full Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'mobile', 'label' => 'Mobile', 'rules' => ['required', 'string', 'max:20', 'unique:couriers,mobile,{id}']], ['name' => 'alternate_mobile', 'label' => 'Alternate Mobile', 'rules' => ['nullable', 'string', 'max:20']], ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255', 'unique:couriers,email,{id}']], ['name' => 'company_name', 'label' => 'Courier Company', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'vehicle_type', 'label' => 'Vehicle Type', 'type' => 'select', 'options' => ['bike' => 'Bike', 'scooter' => 'Scooter', 'car' => 'Car', 'van' => 'Van', 'tempo' => 'Tempo', 'truck' => 'Truck', 'other' => 'Other'], 'rules' => ['nullable', 'string', 'max:40']], ['name' => 'vehicle_number', 'label' => 'Vehicle Number', 'rules' => ['nullable', 'string', 'max:50']], ['name' => 'license_number', 'label' => 'License Number', 'rules' => ['nullable', 'string', 'max:80']], ['name' => 'service_area', 'label' => 'Service Area', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'address', 'label' => 'Address', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string']], ['name' => 'city', 'label' => 'City', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'pincode', 'label' => 'Pincode', 'rules' => ['nullable', 'string', 'max:20']], ['name' => 'id_proof_type', 'label' => 'ID Proof Type', 'type' => 'select', 'options' => ['aadhaar' => 'Aadhaar', 'pan' => 'PAN', 'driving_license' => 'Driving License', 'voter_id' => 'Voter ID', 'other' => 'Other'], 'rules' => ['nullable', 'string', 'max:40']], ['name' => 'id_proof_number', 'label' => 'ID Proof Number', 'rules' => ['nullable', 'string', 'max:80']], ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On Leave'], 'rules' => ['required', 'in:active,inactive,on_leave']], ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'categories' => [
            'label' => 'Categories', 'group' => 'Catalog', 'model' => Category::class, 'search' => ['name', 'slug', 'homepage_title'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'image_path', 'label' => 'Image', 'type' => 'image'], ['key' => 'name', 'label' => 'Name'], ['key' => 'slug', 'label' => 'Slug'], ['key' => 'show_on_homepage', 'label' => 'Homepage', 'type' => 'boolean'], ['key' => 'homepage_sort_order', 'label' => 'Home Sort'], ['key' => 'homepage_product_limit', 'label' => 'Limit'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['type' => 'section_heading', 'label' => 'Basic Category'],
                ['name' => 'name', 'label' => 'Category Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'image_path', 'label' => 'Category Image - 130 x 130 px', 'type' => 'image', 'upload_dir' => 'uploads/categories', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['type' => 'section_heading', 'label' => 'Homepage Product Row Settings'],
                ['name' => 'show_on_homepage', 'label' => 'Show this category on Homepage Category Slider', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'homepage_title', 'label' => 'Homepage Title', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Leave blank to use category name.'],
                ['name' => 'homepage_layout', 'label' => 'Homepage Layout', 'type' => 'select', 'options' => ['product_slider' => 'Product Slider', 'product_grid' => 'Product Grid'], 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'homepage_product_limit', 'label' => 'Homepage Product Limit', 'type' => 'number', 'default' => 8, 'rules' => ['nullable', 'integer', 'min:1', 'max:50']],
                ['name' => 'homepage_sort_order', 'label' => 'Homepage Sort Order / Row Number', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
            ],
        ],        'brands' => [
            'label' => 'Brands', 'group' => 'Catalog', 'model' => Brand::class, 'search' => ['name'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'name', 'label' => 'Brand'], ['key' => 'products_count', 'label' => 'Products'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean'], ['key' => 'created_at', 'label' => 'Created', 'type' => 'date']],
            'with_count' => ['products'], 'fields' => [['name' => 'name', 'label' => 'Brand Name', 'rules' => ['required', 'string', 'max:255', 'unique:brands,name,{id}']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],
        'units' => [
            'label' => 'Units', 'group' => 'Catalog', 'singular' => 'Unit', 'model' => Unit::class, 'search' => ['name', 'short_name', 'unit_type'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'name', 'label' => 'Unit Name'], ['key' => 'short_name', 'label' => 'Short Name'], ['key' => 'unit_type', 'label' => 'Type'], ['key' => 'decimal_precision', 'label' => 'Decimal'], ['key' => 'products_count', 'label' => 'Products'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'with_count' => ['products'], 'fields' => [['name' => 'name', 'label' => 'Unit Name', 'rules' => ['required', 'string', 'max:255', 'unique:units,name,{id}']], ['name' => 'short_name', 'label' => 'Short Name', 'rules' => ['required', 'string', 'max:30', 'unique:units,short_name,{id}'], 'help' => 'Example: kg, ltr, pcs, pkt.'], ['name' => 'unit_type', 'label' => 'Unit Type', 'type' => 'select', 'options' => ['weight' => 'Weight', 'volume' => 'Volume', 'quantity' => 'Quantity', 'length' => 'Length', 'other' => 'Other'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'decimal_precision', 'label' => 'Decimal Precision', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0', 'max:6']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],        'product-types' => [
            'label' => 'Product Types', 'group' => 'Catalog', 'description' => 'Manage product types used in product master.', 'model' => ProductType::class, 'search' => ['name', 'slug'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'name', 'label' => 'Product Type'], ['key' => 'slug', 'label' => 'Slug'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'name', 'label' => 'Product Type Name', 'rules' => ['required', 'string', 'max:255']],

                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'products' => [
            'label' => 'Products', 'group' => 'Catalog', 'description' => 'Add product, required size/pack variants, stock, gallery and videos from this single form.', 'model' => Product::class, 'with' => ['category', 'brand', 'homepageSection', 'productType', 'unit', 'images', 'media', 'translations', 'variants.inventoryBatches', 'variants.unit', 'relatedProductLinks.relatedProduct'], 'search' => ['name', 'sku', 'hsn_code'], 'status_column' => 'is_active', 'status_options' => $active,
            'filters' => [
                ['name' => 'homepage_section_id', 'label' => 'Section Title', 'column' => 'homepage_section_id', 'option_model' => ProductHomepageSection::class, 'option_label' => 'title'],
            ],
            'columns' => [['key' => 'images.0.path', 'label' => 'Image', 'type' => 'image'], ['key' => 'sku', 'label' => 'SKU'], ['key' => 'name', 'label' => 'Product Name'], ['key' => 'productType.name', 'label' => 'Product Type'], ['key' => 'category.name', 'label' => 'Category'], ['key' => 'brand.name', 'label' => 'Brand'], ['key' => 'homepageSection.title', 'label' => 'Section Title'], ['key' => 'unit.short_name', 'label' => 'Unit'], ['key' => 'dealer_price', 'label' => 'Dealer Price', 'type' => 'money'], ['key' => 'customer_price', 'label' => 'Customer Price', 'type' => 'money']],
            'fields' => [
                ['type' => 'section_heading', 'label' => '1. Basic Information'],
                ['name' => 'name', 'label' => 'Product Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'option_model' => Category::class, 'rules' => ['required', 'exists:categories,id']],
                ['name' => 'homepage_section_id', 'label' => 'Homepage Section Title', 'type' => 'select', 'option_model' => ProductHomepageSection::class, 'option_where' => ['is_active' => true], 'option_label' => 'title', 'option_attributes' => ['section_type' => 'section_type', 'layout_type' => 'layout_type'], 'rules' => ['nullable', 'exists:product_homepage_sections,id'], 'help' => 'Select any Homepage Settings section. Product will display according to selected section type.'],
                ['name' => 'brand_id', 'label' => 'Brand', 'type' => 'select', 'option_model' => Brand::class, 'rules' => ['nullable', 'exists:brands,id']],
                ['name' => 'product_type_id', 'label' => 'Product Type', 'type' => 'select', 'option_model' => ProductType::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:product_types,id']],
                ['name' => 'sort_order', 'label' => 'Product Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0'], 'help' => 'Controls the overall listing order of this product.'],
                ['name' => 'short_description', 'label' => 'Short Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 2, 'maxlength' => 160, 'character_counter' => true, 'rules' => ['nullable', 'string', 'max:160'], 'help' => 'Maximum 160 characters. Product cards show up to 80 characters.'],
                ['name' => 'is_visible_to_dealers', 'label' => 'Visible to Dealers', 'type' => 'checkbox', 'default' => 1, 'rules' => ['boolean'], 'help' => 'Makes this product visible in dealer-facing listings and orders.'],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1, 'rules' => ['boolean'], 'help' => 'Keeps this product enabled for use in the system.'],
                ['name' => 'is_visible_to_customers', 'label' => 'Visible to Customers', 'type' => 'checkbox', 'default' => 1, 'rules' => ['boolean'], 'help' => 'Makes this product visible in the public customer storefront.'],
                ['name' => 'show_on_homepage', 'label' => 'Allow product on homepage product rows', 'type' => 'checkbox', 'default' => 1, 'rules' => ['boolean'], 'help' => 'Allows this product to appear in homepage product sections.'],

                ['type' => 'section_heading', 'label' => '2. Variant Details (Required)'],
                ['name' => 'variants', 'label' => 'Size / Pack Variants', 'type' => 'product_variants_repeater', 'col' => 'col-12', 'rules' => ['required', 'array', 'min:1'], 'help' => 'At least one active variant and exactly one Main Product are required. Price, tax, unit, SKU, HSN and opening stock are maintained inside each variant.'],

                ['type' => 'section_heading', 'label' => '3. Images & Gallery'],
                ['name' => 'primary_image', 'label' => 'Main Product Image - 500 x 500 px for cards / 750 x 750 px for detail', 'type' => 'image', 'upload_dir' => 'uploads/products', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120']],
                ['name' => 'gallery_images', 'label' => 'Product Gallery Images - 150 x 150 px thumbnails', 'type' => 'image_multiple', 'upload_dir' => 'uploads/products/gallery', 'rules' => ['nullable', 'array']],

                ['type' => 'section_heading', 'label' => '4. Product Videos'],
                ['name' => 'media', 'label' => 'Gallery Videos', 'type' => 'product_media_repeater', 'col' => 'col-12', 'rules' => ['nullable', 'array'], 'help' => 'Add multiple uploaded MP4/WebM videos or YouTube URLs. Videos appear in the same product gallery.'],

                ['type' => 'section_heading', 'label' => '5. Deal Timer / Stock Display'],
                ['name' => 'sale_badge_text', 'label' => 'Sale Badge Text', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'sold_quantity', 'label' => 'Sold Quantity', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'total_quantity', 'label' => 'Total Quantity', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'low_stock_text', 'label' => 'Low Stock Text', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'offer_start_at', 'label' => 'Offer Start Date & Time', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']],
                ['name' => 'offer_end_at', 'label' => 'Offer End Date & Time', 'type' => 'datetime-local', 'rules' => ['nullable', 'date', 'after_or_equal:offer_start_at']],
                ['name' => 'is_offer_active', 'label' => 'Offer Timer Active', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Turns the countdown offer timer on for this product.'],

                ['type' => 'section_heading', 'label' => '6. Product Language Translations'],
                ['type' => 'product_translation_tools', 'label' => 'Auto Translate from Product Name & Description'],
                ['name' => 'translation_hi_name', 'label' => 'Hindi Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Hindi product name'],
                ['name' => 'translation_hi_description', 'label' => 'Hindi Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Hindi description'],
                ['name' => 'translation_mr_name', 'label' => 'Marathi Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Marathi product name'],
                ['name' => 'translation_mr_description', 'label' => 'Marathi Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Marathi description'],
                ['name' => 'translation_gu_name', 'label' => 'Gujarati Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Gujarati product name'],
                ['name' => 'translation_gu_description', 'label' => 'Gujarati Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Gujarati description'],
                ['name' => 'translation_kn_name', 'label' => 'Kannada Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Kannada product name'],
                ['name' => 'translation_kn_description', 'label' => 'Kannada Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Kannada description'],
                ['name' => 'translation_te_name', 'label' => 'Telugu Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Telugu product name'],
                ['name' => 'translation_te_description', 'label' => 'Telugu Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Telugu description'],

                ['type' => 'section_heading', 'label' => '7. Homepage / Display Flags'],
                ['name' => 'is_featured', 'label' => 'Featured Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Marks this product as featured for highlighted listings.'],
                ['name' => 'is_top_selling', 'label' => 'Top Selling Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Uses this product in top-selling product collections.'],
                ['name' => 'is_trending', 'label' => 'Trending Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Marks this product for trending product sections.'],
                ['name' => 'is_new_arrival', 'label' => 'New Arrival Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Shows this product in new-arrival selections.'],
                ['name' => 'is_offer_product', 'label' => 'Offer Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Includes this product in offer-based product groups.'],
                ['name' => 'is_deal_timer_product', 'label' => 'Deal Timer Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Uses this product as the special offer card in Top Selling Items.'],

                ['type' => 'section_heading', 'label' => '8. Homepage Display Fields', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'product_section', 'coupon_section', 'top_selling_section', 'offer_section', 'strip_offer_banner', 'service_section', 'blog_section']],
                ['name' => 'homepage_title', 'label' => 'Homepage Title', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'coupon_section', 'strip_offer_banner', 'blog_section', 'service_section']],
                ['name' => 'homepage_subtitle', 'label' => 'Homepage Subtitle', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'blog_section', 'service_section']],
                ['name' => 'homepage_description', 'label' => 'Homepage Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'blog_section']],
                ['name' => 'homepage_image_path', 'label' => 'Homepage Image / Banner Image', 'type' => 'image', 'upload_dir' => 'uploads/products/homepage', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'help' => 'Hero: 1920x637, Product card: 500x500, Small banner: 375x243, Offer banner depends on layout.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'offer_section', 'strip_offer_banner', 'blog_section']],
                ['name' => 'homepage_mobile_image_path', 'label' => 'Homepage Mobile Image', 'type' => 'image', 'upload_dir' => 'uploads/products/homepage/mobile', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'offer_section', 'strip_offer_banner', 'blog_section']],
                ['name' => 'homepage_logo_image_path', 'label' => 'Homepage Logo Image / Coupon Logo', 'type' => 'image', 'upload_dir' => 'uploads/products/homepage/logos', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'help' => 'Coupon logo recommended 290x90.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section']],
                ['name' => 'homepage_offer_image_path', 'label' => 'Homepage Offer Image', 'type' => 'image', 'upload_dir' => 'uploads/products/homepage/offers', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'help' => 'Coupon offer image recommended 250x200.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section']],
                ['name' => 'homepage_highlight_text', 'label' => 'Homepage Highlight Text', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['strip_offer_banner']],
                ['name' => 'homepage_discount_text', 'label' => 'Homepage Discount Text', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section', 'strip_offer_banner']],
                ['name' => 'homepage_validity_text', 'label' => 'Homepage Validity Text', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section']],
                ['name' => 'homepage_coupon_code', 'label' => 'Homepage Coupon Code', 'rules' => ['nullable', 'string', 'max:80'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section']],
                ['name' => 'homepage_button_text', 'label' => 'Homepage Button Text', 'rules' => ['nullable', 'string', 'max:120'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'strip_offer_banner']],
                ['name' => 'homepage_button_url', 'label' => 'Homepage Button Link', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'offer_section', 'strip_offer_banner', 'blog_section']],
                ['name' => 'homepage_icon_key', 'label' => 'Homepage Service Icon / SVG Key', 'rules' => ['nullable', 'string', 'max:120'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['service_section']],
                ['name' => 'homepage_slot', 'label' => 'Homepage Slot / Position', 'rules' => ['nullable', 'string', 'max:80'], 'help' => 'Example: big, small, left, right, first, second.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['top_small_banners', 'offer_section']],
                ['name' => 'homepage_background_color', 'label' => 'Homepage Background Color', 'rules' => ['nullable', 'string', 'max:30'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['strip_offer_banner']],
                ['name' => 'homepage_text_color', 'label' => 'Homepage Text Color', 'rules' => ['nullable', 'string', 'max:30'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['strip_offer_banner']],
                ['name' => 'homepage_sort_order', 'label' => 'Homepage Section Product Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0'], 'help' => 'Controls only this product position inside the selected homepage section. It does not change the normal catalog listing order.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'product_section', 'coupon_section', 'top_selling_section', 'offer_section', 'strip_offer_banner', 'service_section', 'blog_section']],

                ['type' => 'section_heading', 'label' => '9. SEO', 'display_only' => true],                ['name' => 'meta_title', 'label' => 'Meta Title', 'rules' => ['nullable', 'string', 'max:255'], 'display_only' => true],
                ['name' => 'meta_description', 'label' => 'Meta Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'display_only' => true],
                ['name' => 'meta_keywords', 'label' => 'Meta Keywords', 'rules' => ['nullable', 'string', 'max:255'], 'display_only' => true],

                ['type' => 'section_heading', 'label' => '10. Bottom Details'],
                ['type' => 'product_bottom_details', 'label' => 'Bottom Details', 'groups' => [
                    'description' => 'Description',
                    'additional_information' => 'Additional Information',
                    'care_instructions' => 'Care Instructions',
                ]],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 4, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'benefits', 'label' => 'Benefits', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'usage_instructions', 'label' => 'Usage Instructions', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'crop_information', 'label' => 'Crop Information', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'detail_banner_image', 'label' => 'Detail Page Description Banner - 1199 x 97 px', 'type' => 'image', 'upload_dir' => 'uploads/products/detail-banners', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'detail_banner_url', 'label' => 'Detail Page Description Banner Link', 'rules' => ['nullable', 'string', 'max:255'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'detail_banner_position', 'label' => 'Description Banner Position', 'type' => 'radio', 'col' => 'col-12', 'options' => ['before' => 'Start of description', 'middle' => 'Middle of description', 'after' => 'End of description'], 'rules' => ['required', 'in:before,middle,after'], 'default' => 'after', 'help' => 'Decides where the banner appears inside the Description tab on the product detail page.', 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'additional_info', 'label' => 'Additional Information', 'type' => 'product_additional_information_repeater', 'rules' => ['nullable', 'array'], 'render_inside' => 'product_bottom_details', 'render_group' => 'additional_information'],
                ['name' => 'care_instructions', 'label' => 'Care Instructions', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'care_instructions'],
            ],
        ],        'homepage-settings' => [
            'label' => 'Homepage Settings',
            'sort' => ['sort_order', 'asc'],
            'group' => 'Catalog',
            'description' => 'Create homepage rows/design only. Actual product/banner/text/offer content is managed from Products.',
            'model' => ProductHomepageSection::class,
            'with' => ['category'],
            'search' => ['title', 'section_type', 'layout_type'],
            'status_column' => 'is_active',
            'status_options' => $active,
            'columns' => [
                ['key' => 'title', 'label' => 'Section Title'],
                ['key' => 'section_type_name', 'label' => 'Section Type'],
                ['key' => 'layout_type_name', 'label' => 'Layout Type'],
                ['key' => 'category.name', 'label' => 'Category'],
                ['key' => 'product_limit', 'label' => 'Item Limit'],
                ['key' => 'sort_order', 'label' => 'Sort Order'],
                ['key' => 'is_active', 'label' => 'Active', 'type' => 'boolean'],
            ],
            'fields' => [
                ['type' => 'section_heading', 'label' => 'Homepage Row Settings'],

                ['name' => 'title', 'label' => 'Section Title', 'rules' => ['required', 'string', 'max:255']],

                ['name' => 'section_type', 'label' => 'Section Type', 'type' => 'select', 'options' => [
                    'hero_slider' => 'Hero Slider',
                    'top_small_banners' => 'Top Small Banners',
                    'category_section' => 'Category Section',
                    'product_section' => 'Product Section',
                    'coupon_section' => 'Coupon Section',
                    'top_selling_section' => 'Top Selling Section',
                    'offer_section' => 'Offer Section',
                    'strip_offer_banner' => 'Strip Offer Banner',
                    'service_section' => 'Service Section',
                    'blog_section' => 'Blog Section',
                ], 'rules' => ['required', 'string', 'max:80']],

                ['name' => 'layout_type', 'label' => 'Layout Type', 'type' => 'select', 'options' => [
                    'full_width_slider' => 'Full Width Slider',
                    'four_banner_slider' => 'Four Banner Slider',
                    'category_slider' => 'Category Slider',
                    'product_slider' => 'Product Slider',
                    'product_grid' => 'Product Grid',
                    'coupon_slider' => 'Coupon Slider',
                    'products_with_offer' => 'Products With Special Offer',
                    'two_column_banner' => 'Two Column Banner',
                    'big_small_banner' => 'Big Small Banner',
                    'full_width_banner' => 'Full Width Banner',
                    'text_strip' => 'Text Strip',
                    'service_icons' => 'Service Icons',
                    'blog_slider' => 'Blog Slider',
                ], 'rules' => ['nullable', 'string', 'max:80']],

                ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'option_model' => Category::class, 'rules' => ['nullable', 'exists:categories,id'], 'help' => 'Use this only when Section Type is Product Section.'],

                ['name' => 'product_limit', 'label' => 'Item Limit', 'type' => 'number', 'default' => 8, 'rules' => ['nullable', 'integer', 'min:1', 'max:50']],

                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],

                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'homepage-setting-items' => [
            'label' => 'Homepage Setting Items',
            'group' => 'Catalog',
            'description' => 'Banners, coupon cards, strip text and service blocks used inside Homepage Settings.',
            'model' => ProductHomepageSectionItem::class,
            'with' => ['section'],
            'search' => ['title', 'subtitle', 'coupon_code', 'slot'],
            'status_column' => 'is_active',
            'status_options' => $active,
            'columns' => [
                ['key' => 'section.title', 'label' => 'Homepage Section'],
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'slot', 'label' => 'Slot'],
                ['key' => 'coupon_code', 'label' => 'Coupon Code'],
                ['key' => 'sort_order', 'label' => 'Sort Order'],
                ['key' => 'is_active', 'label' => 'Active', 'type' => 'boolean'],
            ],
            'fields' => [
                ['name' => 'section_id', 'label' => 'Homepage Section', 'type' => 'select', 'option_model' => ProductHomepageSection::class, 'option_label' => 'title', 'rules' => ['required', 'exists:product_homepage_sections,id']],
                ['name' => 'title', 'label' => 'Title', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'subtitle', 'label' => 'Subtitle', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string']],
                ['name' => 'highlight_text', 'label' => 'Highlight Text', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'image_path', 'label' => 'Image', 'type' => 'image', 'upload_dir' => 'uploads/homepage', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120']],
                ['name' => 'mobile_image_path', 'label' => 'Mobile Image', 'type' => 'image', 'upload_dir' => 'uploads/homepage/mobile', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120']],
                ['name' => 'logo_image_path', 'label' => 'Coupon / Bank Logo', 'type' => 'image', 'upload_dir' => 'uploads/homepage/logos', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048']],
                ['name' => 'offer_image_path', 'label' => 'Offer Image', 'type' => 'image', 'upload_dir' => 'uploads/homepage/offers', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120']],
                ['name' => 'button_text', 'label' => 'Button Text', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'button_url', 'label' => 'Button Link', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'coupon_code', 'label' => 'Coupon Code', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'discount_text', 'label' => 'Discount Text', 'rules' => ['nullable', 'string', 'max:120']],
                ['name' => 'validity_text', 'label' => 'Validity Text', 'rules' => ['nullable', 'string', 'max:120']],
                ['name' => 'icon_key', 'label' => 'Service Icon / SVG Key', 'rules' => ['nullable', 'string', 'max:120']],
                ['name' => 'background_color', 'label' => 'Background Color', 'rules' => ['nullable', 'string', 'max:30']],
                ['name' => 'text_color', 'label' => 'Text Color', 'rules' => ['nullable', 'string', 'max:30']],
                ['name' => 'slot', 'label' => 'Slot / Position', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'product-related-products' => [
            'label' => 'Related Products', 'group' => 'Catalog', 'singular' => 'Related Product', 'model' => ProductRelatedProduct::class, 'with' => ['product', 'relatedProduct'], 'search' => [],
            'columns' => [['key' => 'product.name', 'label' => 'Product'], ['key' => 'relatedProduct.name', 'label' => 'Related Product'], ['key' => 'sort_order', 'label' => 'Sort']],
            'fields' => [
                ['name' => 'product_id', 'label' => 'Product', 'type' => 'select', 'option_model' => Product::class, 'option_label' => 'name', 'rules' => ['required', 'exists:products,id']],
                ['name' => 'related_product_id', 'label' => 'Related Product', 'type' => 'select', 'option_model' => Product::class, 'option_label' => 'name', 'rules' => ['required', 'exists:products,id', 'different:product_id']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
            ],
        ],
        'pricing' => [
            'label' => 'Dealer & Customer Pricing', 'group' => 'Catalog', 'singular' => 'Product Price', 'model' => Product::class, 'with' => ['category'], 'search' => ['name', 'sku'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'sku', 'label' => 'SKU'], ['key' => 'name', 'label' => 'Product'], ['key' => 'mrp', 'label' => 'MRP', 'type' => 'money'], ['key' => 'dealer_price', 'label' => 'Dealer Price', 'type' => 'money'], ['key' => 'customer_price', 'label' => 'Customer Price', 'type' => 'money'], ['key' => 'gst_percent', 'label' => 'GST %']],
            'fields' => [['name' => 'mrp', 'label' => 'MRP', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'dealer_price', 'label' => 'Dealer Price', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'customer_price', 'label' => 'Customer Price', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'gst_percent', 'label' => 'GST %', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0', 'max:100'], 'help' => 'Enter GST percent. Use 0 if GST is not applicable.']],
        ],
        'warehouses' => [
            'label' => 'Warehouses', 'group' => 'Inventory', 'model' => Warehouse::class, 'search' => ['name', 'code', 'city'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'code', 'label' => 'Code'], ['key' => 'name', 'label' => 'Warehouse'], ['key' => 'city', 'label' => 'City'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [['name' => 'name', 'label' => 'Warehouse Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'code', 'label' => 'Code', 'rules' => ['required', 'string', 'max:50', 'unique:warehouses,code,{id}']], ['name' => 'city', 'label' => 'City', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],

        'inventory' => [
            'label' => 'Stock', 'group' => 'Inventory', 'singular' => 'Stock Record', 'model' => InventoryBatch::class, 'with' => ['product', 'variant', 'warehouse'], 'search' => ['batch_no'], 'columns' => [['key' => 'product.name', 'label' => 'Product'], ['key' => 'variant.value', 'label' => 'Size / Pack'], ['key' => 'warehouse.name', 'label' => 'Warehouse'], ['key' => 'batch_no', 'label' => 'Batch'], ['key' => 'quantity', 'label' => 'Retail Pack Quantity'], ['key' => 'reserved_quantity', 'label' => 'Reserved'], ['key' => 'low_stock_alert', 'label' => 'Low Stock Level'], ['key' => 'expiry_date', 'label' => 'Expiry', 'type' => 'date']],
            'fields' => [
                ['name' => 'product_id', 'label' => 'Product', 'type' => 'select', 'option_model' => Product::class, 'option_label' => 'name', 'rules' => ['required', 'exists:products,id']],
                ['name' => 'product_variant_id', 'label' => 'Size / Pack Variant', 'type' => 'select', 'option_model' => ProductVariant::class, 'option_label' => 'value', 'rules' => ['nullable', 'exists:product_variants,id'], 'help' => 'Select a variant when this product has Size / Pack variants. Quantity is entered in retail packs/bottles.'],
                ['name' => 'warehouse_id', 'label' => 'Warehouse', 'type' => 'select', 'option_model' => Warehouse::class, 'option_label' => 'name', 'rules' => ['required', 'exists:warehouses,id']],
                ['name' => 'batch_no', 'label' => 'Batch Number', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'manufacturing_date', 'label' => 'Manufacturing Date', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'expiry_date', 'label' => 'Expiry Date', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:manufacturing_date']],
                ['name' => 'purchase_price', 'label' => 'Purchase Price', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'quantity', 'label' => 'Current Quantity', 'type' => 'number', 'step' => '0.001', 'rules' => ['required', 'numeric', 'min:0']],
                ['name' => 'reserved_quantity', 'label' => 'Reserved Quantity', 'type' => 'number', 'step' => '0.001', 'default' => 0, 'rules' => ['required', 'numeric', 'min:0', 'lte:quantity']],
                ['name' => 'low_stock_alert', 'label' => 'Low Stock Alert', 'type' => 'number', 'step' => '0.001', 'default' => 0, 'rules' => ['required', 'numeric', 'min:0']],
            ],
        ],
        'orders' => [
            'label' => 'Sale Orders', 'group' => 'Sales', 'singular' => 'Sale Order', 'model' => Order::class, 'with' => ['customer', 'dealer.dealerProfile', 'salesman', 'invoice', 'proformaInvoices'], 'search' => ['order_no'], 'status_column' => 'status', 'status_options' => $orderStatus, 'filters' => [['name' => 'type', 'column' => 'order_type']], 'can_delete' => false,
            'columns' => [['key' => 'order_no', 'label' => 'Sale Order No.'], ['key' => 'order_type', 'label' => 'Channel'], ['key' => 'dealer.dealerProfile.firm_name', 'label' => 'Dealer'], ['key' => 'customer.name', 'label' => 'Customer'], ['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'grand_total', 'label' => 'Total', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'created_at', 'label' => 'Date', 'type' => 'date']],
            'fields' => [['name' => 'order_type', 'label' => 'Channel', 'type' => 'select', 'query_key' => 'type', 'options' => ['customer' => 'Customer', 'dealer' => 'Dealer'], 'rules' => ['required', 'in:customer,dealer']], ['name' => 'order_no', 'label' => 'Sale Order Number', 'rules' => ['nullable', 'string', 'max:80', 'unique:orders,order_no,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'customer'], 'rules' => ['nullable', 'required_if:order_type,customer', 'exists:users,id']], ['name' => 'dealer_id', 'label' => 'Dealer', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'dealer'], 'rules' => ['nullable', 'required_if:order_type,dealer', 'exists:users,id']], ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['nullable', 'exists:users,id']], ['name' => 'status', 'label' => 'Order Status', 'type' => 'select', 'options' => $orderStatus, 'rules' => ['required', 'in:'.implode(',', array_keys($orderStatus))]], ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'gst_total', 'label' => 'GST Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'discount_total', 'label' => 'Discount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'grand_total', 'label' => 'Grand Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to calculate from subtotal + GST - discount.'], ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'proforma-invoices' => [
            'label' => 'Proforma Invoices', 'group' => 'Sales', 'singular' => 'Proforma Invoice', 'description' => 'Quotation-style invoice generated before sale invoice and dispatch.', 'model' => ProformaInvoice::class, 'with' => ['order.customer', 'order.dealer.dealerProfile', 'order.salesman'], 'search' => ['proforma_no'], 'status_column' => 'status', 'status_options' => ['draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted', 'converted' => 'Converted', 'cancelled' => 'Cancelled'], 'filters' => [['name' => 'type', 'relation' => 'order', 'column' => 'order_type']],
            'columns' => [['key' => 'proforma_no', 'label' => 'Proforma No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'order.dealer.dealerProfile.firm_name', 'label' => 'Dealer'], ['key' => 'order.customer.name', 'label' => 'Customer'], ['key' => 'proforma_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'valid_until', 'label' => 'Valid Until', 'type' => 'date'], ['key' => 'grand_total', 'label' => 'Total', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['required', 'exists:orders,id']], ['name' => 'proforma_no', 'label' => 'Proforma Number', 'rules' => ['nullable', 'string', 'max:80', 'unique:proforma_invoices,proforma_no,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'proforma_date', 'label' => 'Proforma Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'valid_until', 'label' => 'Valid Until', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:proforma_date']], ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'gst_total', 'label' => 'GST Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'discount_total', 'label' => 'Discount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'grand_total', 'label' => 'Grand Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted', 'converted' => 'Converted', 'cancelled' => 'Cancelled'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'invoices' => [
            'label' => 'Sale Invoices', 'group' => 'Sales', 'singular' => 'Sale Invoice', 'model' => Invoice::class, 'with' => ['order.customer', 'order.dealer.dealerProfile'], 'search' => ['invoice_no'], 'filters' => [['name' => 'type', 'relation' => 'order', 'column' => 'order_type']], 'can_delete' => false,
            'columns' => [['key' => 'invoice_no', 'label' => 'Invoice No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'order.dealer.dealerProfile.firm_name', 'label' => 'Dealer'], ['key' => 'order.customer.name', 'label' => 'Customer'], ['key' => 'invoice_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'grand_total', 'label' => 'Total', 'type' => 'money']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['required', 'exists:orders,id', 'unique:invoices,order_id,{id}']], ['name' => 'invoice_no', 'label' => 'Invoice Number', 'rules' => ['nullable', 'string', 'max:80', 'unique:invoices,invoice_no,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'invoice_date', 'label' => 'Invoice Date', 'type' => 'date', 'rules' => ['nullable', 'date'], 'help' => 'Leave blank to use today.'], ['name' => 'grand_total', 'label' => 'Grand Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'pdf_path', 'label' => 'PDF Path', 'rules' => ['nullable', 'string', 'max:255']]],
        ],
        'dispatches' => [
            'label' => 'Dispatch & Delivery', 'group' => 'Sales', 'singular' => 'Dispatch', 'model' => Dispatch::class, 'with' => ['order'], 'search' => ['dispatch_no', 'courier_name', 'tracking_no'], 'status_column' => 'status', 'status_options' => ['packing' => 'Packing', 'dispatched' => 'Dispatched', 'in_transit' => 'In Transit', 'delivered' => 'Delivered', 'returned' => 'Returned'], 'filters' => [['name' => 'type', 'relation' => 'order', 'column' => 'order_type']],
            'columns' => [['key' => 'dispatch_no', 'label' => 'Dispatch No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'courier_name', 'label' => 'Courier'], ['key' => 'tracking_no', 'label' => 'Tracking'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'dispatched_at', 'label' => 'Dispatched', 'type' => 'datetime']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['required', 'exists:orders,id']], ['name' => 'dispatch_no', 'label' => 'Dispatch Number', 'rules' => ['required', 'string', 'max:80', 'unique:dispatches,dispatch_no,{id}']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['packing' => 'Packing', 'dispatched' => 'Dispatched', 'in_transit' => 'In Transit', 'delivered' => 'Delivered', 'returned' => 'Returned'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'courier_name', 'label' => 'Courier Name', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'tracking_no', 'label' => 'Tracking Number', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'tracking_url', 'label' => 'Tracking URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']], ['name' => 'dispatched_at', 'label' => 'Dispatched At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']], ['name' => 'delivered_at', 'label' => 'Delivered At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']]],
        ],
        'returns' => [
            'label' => 'Returns & Cancellation', 'group' => 'Sales', 'singular' => 'Return Request', 'model' => ReturnRequest::class, 'with' => ['order', 'user'], 'search' => ['return_no', 'reason'], 'status_column' => 'status', 'status_options' => ['requested' => 'Requested', 'approved' => 'Approved', 'rejected' => 'Rejected', 'received' => 'Received', 'refunded' => 'Refunded'], 'filters' => [['name' => 'type', 'relation' => 'order', 'column' => 'order_type']],
            'columns' => [['key' => 'return_no', 'label' => 'Return No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'user.name', 'label' => 'Requested By'], ['key' => 'reason', 'label' => 'Reason'], ['key' => 'refund_amount', 'label' => 'Refund', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['required', 'exists:orders,id']], ['name' => 'user_id', 'label' => 'Requested By', 'type' => 'select', 'option_model' => User::class, 'rules' => ['required', 'exists:users,id']], ['name' => 'return_no', 'label' => 'Return Number', 'rules' => ['required', 'string', 'max:80', 'unique:return_requests,return_no,{id}']], ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'rules' => ['required', 'string']], ['name' => 'refund_amount', 'label' => 'Refund Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['requested' => 'Requested', 'approved' => 'Approved', 'rejected' => 'Rejected', 'received' => 'Received', 'refunded' => 'Refunded'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'payments' => [
            'label' => 'Payments', 'group' => 'Finance', 'model' => Payment::class, 'with' => ['order', 'payer', 'collector'], 'search' => ['payment_no', 'transaction_ref'], 'status_column' => 'status', 'status_options' => ['pending' => 'Pending', 'paid' => 'Paid', 'collected' => 'Collected', 'verified' => 'Verified', 'failed' => 'Failed', 'refunded' => 'Refunded'],
            'columns' => [['key' => 'payment_no', 'label' => 'Payment No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'payer.name', 'label' => 'Payer'], ['key' => 'payment_mode', 'label' => 'Mode'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'paid_at', 'label' => 'Paid At', 'type' => 'datetime']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['nullable', 'exists:orders,id']], ['name' => 'payer_id', 'label' => 'Payer', 'type' => 'select', 'option_model' => User::class, 'rules' => ['nullable', 'exists:users,id']], ['name' => 'collected_by', 'label' => 'Collected By', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['nullable', 'exists:users,id']], ['name' => 'payment_no', 'label' => 'Payment Number', 'rules' => ['required', 'string', 'max:80', 'unique:payments,payment_no,{id}']], ['name' => 'payment_mode', 'label' => 'Payment Mode', 'type' => 'select', 'options' => ['cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card', 'gateway' => 'Online Gateway'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0.01']], ['name' => 'transaction_ref', 'label' => 'Transaction Reference', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'paid' => 'Paid', 'collected' => 'Collected', 'verified' => 'Verified', 'failed' => 'Failed', 'refunded' => 'Refunded'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'paid_at', 'label' => 'Paid At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']]],
        ],
        'collections' => [
            'label' => 'Collections', 'group' => 'Finance', 'singular' => 'Collection', 'model' => Payment::class, 'with' => ['payer', 'collector', 'order'], 'search' => ['payment_no', 'transaction_ref'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'payment_no', 'label' => 'Receipt No.'], ['key' => 'payer.name', 'label' => 'Dealer'], ['key' => 'collector.name', 'label' => 'Collected By'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'payment_mode', 'label' => 'Mode'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'paid_at', 'label' => 'Date', 'type' => 'datetime']],
            'fields' => [['name' => 'status', 'label' => 'Verification Status', 'type' => 'select', 'options' => ['collected' => 'Collected', 'verified' => 'Verified', 'rejected' => 'Rejected'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'transaction_ref', 'label' => 'Reference', 'rules' => ['nullable', 'string', 'max:255']]],
        ],
        'outstanding' => [
            'label' => 'Outstanding', 'group' => 'Finance', 'singular' => 'Dealer Outstanding', 'model' => DealerProfile::class, 'with' => ['user', 'salesman'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'dealer_code', 'label' => 'Dealer Code'], ['key' => 'firm_name', 'label' => 'Firm'], ['key' => 'user.name', 'label' => 'Contact'], ['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'money'], ['key' => 'outstanding_balance', 'label' => 'Outstanding', 'type' => 'money']],
            'fields' => [['name' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'outstanding_balance', 'label' => 'Outstanding Balance', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']]],
        ],
        'expense-categories' => [
            'label' => 'Expense Categories', 'group' => 'Expense', 'singular' => 'Expense Category', 'model' => InternalExpenseCategory::class, 'with_count' => ['subcategories', 'expenses'], 'search' => ['name', 'code'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'name', 'label' => 'Category'], ['key' => 'code', 'label' => 'Code'], ['key' => 'subcategories_count', 'label' => 'Subcategories'], ['key' => 'expenses_count', 'label' => 'Expenses'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [['name' => 'name', 'label' => 'Category Name', 'rules' => ['required', 'string', 'max:255', 'unique:internal_expense_categories,name,{id}']], ['name' => 'code', 'label' => 'Code', 'rules' => ['nullable', 'string', 'max:50', 'unique:internal_expense_categories,code,{id}']], ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],
        'expense-subcategories' => [
            'label' => 'Expense Subcategories', 'group' => 'Expense', 'singular' => 'Expense Subcategory', 'model' => InternalExpenseSubcategory::class, 'with' => ['category'], 'with_count' => ['expenses'], 'search' => ['name', 'code'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'category.name', 'label' => 'Category'], ['key' => 'name', 'label' => 'Subcategory'], ['key' => 'code', 'label' => 'Code'], ['key' => 'expenses_count', 'label' => 'Expenses'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'option_model' => InternalExpenseCategory::class, 'option_where' => ['is_active' => true], 'rules' => ['required', 'exists:internal_expense_categories,id']], ['name' => 'name', 'label' => 'Subcategory Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'code', 'label' => 'Code', 'rules' => ['nullable', 'string', 'max:50']], ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],
        'internal-expenses' => [
            'label' => 'Internal Expenses', 'group' => 'Expense', 'singular' => 'Internal Expense', 'model' => InternalExpense::class, 'with' => ['category', 'subcategory', 'payer'], 'search' => ['expense_no', 'title', 'vendor_name', 'payment_mode'], 'status_column' => 'status', 'status_options' => ['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid', 'cancelled' => 'Cancelled'], 'date_column' => 'expense_date',
            'columns' => [['key' => 'expense_no', 'label' => 'Expense No.'], ['key' => 'expense_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'category.name', 'label' => 'Category'], ['key' => 'subcategory.name', 'label' => 'Subcategory'], ['key' => 'title', 'label' => 'Title'], ['key' => 'vendor_name', 'label' => 'Vendor'], ['key' => 'payment_mode', 'label' => 'Payment Mode'], ['key' => 'total_amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'expense_no', 'label' => 'Expense No.', 'rules' => ['nullable', 'string', 'max:80', 'unique:internal_expenses,expense_no,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'expense_date', 'label' => 'Expense Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'option_model' => InternalExpenseCategory::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:internal_expense_categories,id']], ['name' => 'subcategory_id', 'label' => 'Subcategory', 'type' => 'select', 'option_model' => InternalExpenseSubcategory::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:internal_expense_subcategories,id']], ['name' => 'title', 'label' => 'Expense Title', 'rules' => ['required', 'string', 'max:255']], ['name' => 'vendor_name', 'label' => 'Vendor / Party Name', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'payment_mode', 'label' => 'Payment Mode', 'type' => 'select', 'options' => ['cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card', 'cheque' => 'Cheque', 'other' => 'Other'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'taxable_amount', 'label' => 'Taxable Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'gst_amount', 'label' => 'GST Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'total_amount', 'label' => 'Total Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'paid_by', 'label' => 'Paid By', 'type' => 'select', 'option_model' => User::class, 'rules' => ['nullable', 'exists:users,id']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid', 'cancelled' => 'Cancelled'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'receipt_path', 'label' => 'Receipt Upload', 'type' => 'file', 'upload_dir' => 'uploads/internal-expenses', 'rules' => ['nullable', 'file', 'max:5120']], ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'attendance' => [
            'label' => 'Attendance', 'group' => 'Salesman HRMS', 'singular' => 'Attendance Record', 'model' => AttendanceLog::class, 'with' => ['salesman'], 'status_column' => 'status', 'status_options' => ['present' => 'Present', 'absent' => 'Absent', 'half_day' => 'Half Day', 'late' => 'Late', 'leave' => 'Leave'], 'can_delete' => false,
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'attendance_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'check_in_at', 'label' => 'Check In', 'type' => 'datetime'], ['key' => 'check_out_at', 'label' => 'Check Out', 'type' => 'datetime'], ['key' => 'working_minutes', 'label' => 'Minutes'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'attendance_date', 'label' => 'Attendance Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'check_in_at', 'label' => 'Check In', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']], ['name' => 'check_out_at', 'label' => 'Check Out', 'type' => 'datetime-local', 'rules' => ['nullable', 'date', 'after_or_equal:check_in_at']], ['name' => 'working_minutes', 'label' => 'Working Minutes', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['present' => 'Present', 'absent' => 'Absent', 'half_day' => 'Half Day', 'late' => 'Late', 'leave' => 'Leave'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'dealer-visits' => [
            'label' => 'Dealer Visits', 'group' => 'Salesman HRMS', 'singular' => 'Dealer Visit', 'model' => DealerVisit::class, 'with' => ['salesman', 'dealer.dealerProfile'], 'sort' => ['visited_at', 'desc'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'dealer.dealerProfile.firm_name', 'label' => 'Dealer'], ['key' => 'visited_at', 'label' => 'Visited At', 'type' => 'datetime'], ['key' => 'purpose', 'label' => 'Purpose'], ['key' => 'remarks', 'label' => 'Remarks']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'dealer_id', 'label' => 'Dealer', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'dealer'], 'rules' => ['required', 'exists:users,id']], ['name' => 'visited_at', 'label' => 'Visited At', 'type' => 'datetime-local', 'rules' => ['required', 'date']], ['name' => 'purpose', 'label' => 'Purpose', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'tour-plans' => [
            'label' => 'Tour Plans', 'group' => 'Salesman HRMS', 'singular' => 'Tour Plan', 'model' => TourPlan::class, 'with' => ['salesman'], 'status_column' => 'status', 'status_options' => ['planned' => 'Planned', 'approved' => 'Approved', 'completed' => 'Completed', 'cancelled' => 'Cancelled'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'plan_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'route_name', 'label' => 'Route'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'plan_date', 'label' => 'Plan Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'route_name', 'label' => 'Route Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['planned' => 'Planned', 'approved' => 'Approved', 'completed' => 'Completed', 'cancelled' => 'Cancelled'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'expenses' => [
            'label' => 'Expenses', 'group' => 'Salesman HRMS', 'singular' => 'Expense Claim', 'model' => Expense::class, 'with' => ['salesman', 'approver'], 'status_column' => 'status', 'status_options' => $approvalStatus,
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'expense_type', 'label' => 'Type'], ['key' => 'expense_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'approver.name', 'label' => 'Approved By']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'expense_type', 'label' => 'Expense Type', 'type' => 'select', 'options' => ['travel' => 'Travel', 'fuel' => 'Fuel', 'food' => 'Food', 'hotel' => 'Hotel', 'mobile' => 'Mobile', 'other' => 'Other'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'expense_date', 'label' => 'Expense Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0.01']], ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $approvalStatus, 'rules' => ['required', 'string', 'max:40']]],
        ],
        'leaves' => [
            'label' => 'Leave', 'group' => 'Salesman HRMS', 'singular' => 'Leave Application', 'model' => LeaveApplication::class, 'with' => ['salesman', 'approver'], 'status_column' => 'status', 'status_options' => $approvalStatus,
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'leave_type', 'label' => 'Leave Type'], ['key' => 'from_date', 'label' => 'From', 'type' => 'date'], ['key' => 'to_date', 'label' => 'To', 'type' => 'date'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'approver.name', 'label' => 'Approved By']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'leave_type', 'label' => 'Leave Type', 'type' => 'select', 'options' => ['casual' => 'Casual', 'sick' => 'Sick', 'paid' => 'Paid', 'unpaid' => 'Unpaid', 'half_day' => 'Half Day'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'from_date', 'label' => 'From Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'to_date', 'label' => 'To Date', 'type' => 'date', 'rules' => ['required', 'date', 'after_or_equal:from_date']], ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $approvalStatus, 'rules' => ['required', 'string', 'max:40']]],
        ],
        'salary' => [
            'label' => 'Salary & Payroll', 'group' => 'Salesman HRMS', 'singular' => 'Salary Slip', 'model' => SalarySlip::class, 'with' => ['salesman'], 'status_column' => 'status', 'status_options' => ['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'salary_month', 'label' => 'Month'], ['key' => 'salary_year', 'label' => 'Year'], ['key' => 'basic_salary', 'label' => 'Basic', 'type' => 'money'], ['key' => 'incentives', 'label' => 'Incentive', 'type' => 'money'], ['key' => 'deductions', 'label' => 'Deduction', 'type' => 'money'], ['key' => 'net_salary', 'label' => 'Net Salary', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'basic_salary', 'label' => 'Basic Salary', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'allowances', 'label' => 'Allowances', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'bonus', 'label' => 'Bonus', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'incentives', 'label' => 'Incentives', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'commission', 'label' => 'Commission', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'deductions', 'label' => 'Deductions', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'net_salary', 'label' => 'Net Salary', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'targets' => [
            'label' => 'Targets & Commission', 'group' => 'Salesman HRMS', 'singular' => 'Sales Target', 'model' => SalesmanTarget::class, 'with' => ['salesman'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'period_start', 'label' => 'From', 'type' => 'date'], ['key' => 'period_end', 'label' => 'To', 'type' => 'date'], ['key' => 'target_amount', 'label' => 'Target', 'type' => 'money'], ['key' => 'achieved_amount', 'label' => 'Achieved', 'type' => 'money'], ['key' => 'commission_percent', 'label' => 'Commission %']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'period_start', 'label' => 'Period Start', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'period_end', 'label' => 'Period End', 'type' => 'date', 'rules' => ['required', 'date', 'after_or_equal:period_start']], ['name' => 'target_amount', 'label' => 'Target Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'achieved_amount', 'label' => 'Achieved Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'commission_percent', 'label' => 'Commission %', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0', 'max:100']]],
        ],
        'assets' => [
            'label' => 'Salesman Assets', 'group' => 'Salesman HRMS', 'singular' => 'Asset', 'model' => SalesmanAsset::class, 'with' => ['salesman'], 'status_column' => 'status', 'status_options' => ['issued' => 'Issued', 'returned' => 'Returned', 'lost' => 'Lost', 'damaged' => 'Damaged'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'asset_type', 'label' => 'Type'], ['key' => 'asset_name', 'label' => 'Asset'], ['key' => 'serial_no', 'label' => 'Serial No.'], ['key' => 'issued_on', 'label' => 'Issued', 'type' => 'date'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'asset_type', 'label' => 'Asset Type', 'type' => 'select', 'options' => ['mobile' => 'Mobile', 'laptop' => 'Laptop', 'sim' => 'SIM Card', 'vehicle' => 'Vehicle', 'other' => 'Other'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'asset_name', 'label' => 'Asset Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'serial_no', 'label' => 'Serial Number', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'issued_on', 'label' => 'Issued On', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'returned_on', 'label' => 'Returned On', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'condition', 'label' => 'Condition', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['issued' => 'Issued', 'returned' => 'Returned', 'lost' => 'Lost', 'damaged' => 'Damaged'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'notifications' => [
            'label' => 'Notifications', 'group' => 'System', 'singular' => 'Notification', 'model' => Notification::class, 'with' => ['user'], 'search' => ['title', 'message'], 'can_delete' => true,
            'columns' => [['key' => 'user.name', 'label' => 'Recipient'], ['key' => 'channel', 'label' => 'Channel'], ['key' => 'title', 'label' => 'Title'], ['key' => 'message', 'label' => 'Message'], ['key' => 'read_at', 'label' => 'Read At', 'type' => 'datetime'], ['key' => 'created_at', 'label' => 'Sent', 'type' => 'datetime']],
            'fields' => [['name' => 'user_id', 'label' => 'Recipient (blank for all)', 'type' => 'select', 'option_model' => User::class, 'rules' => ['nullable', 'exists:users,id']], ['name' => 'channel', 'label' => 'Channel', 'type' => 'select', 'options' => ['push' => 'Push', 'email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'in_app' => 'In App'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'title', 'label' => 'Title', 'rules' => ['required', 'string', 'max:255']], ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['required', 'string']]],
        ],
        'languages' => [
            'label' => 'Languages', 'group' => 'System', 'singular' => 'Language', 'model' => Language::class, 'search' => ['code', 'name', 'native_name'], 'status_column' => 'is_active', 'status_options' => $active, 'can_delete' => false,
            'columns' => [['key' => 'code', 'label' => 'Code'], ['key' => 'name', 'label' => 'English Name'], ['key' => 'native_name', 'label' => 'Native Name'], ['key' => 'is_default', 'label' => 'Default', 'type' => 'boolean'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean'], ['key' => 'sort_order', 'label' => 'Sort']],
            'fields' => [['name' => 'code', 'label' => 'Language Code', 'rules' => ['required', 'string', 'max:10', 'unique:languages,code,{id}'], 'help' => 'Use stable locale keys like en, hi, mr, gu, kn, te. English remains the app fallback/default.'], ['name' => 'name', 'label' => 'English Name', 'rules' => ['required', 'string', 'max:80']], ['name' => 'native_name', 'label' => 'Native Name', 'rules' => ['nullable', 'string', 'max:120']], ['name' => 'is_default', 'label' => 'Default Language', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Keep English as default. Only one default should be active.'], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']], ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']]],
        ],        'translations' => [
            'label' => 'Languages & Translations', 'group' => 'System', 'singular' => 'Translation', 'model' => AppTranslation::class, 'search' => ['translation_key', 'value', 'locale'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'group', 'label' => 'Group'], ['key' => 'translation_key', 'label' => 'Key'], ['key' => 'locale', 'label' => 'Locale'], ['key' => 'value', 'label' => 'Translation'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [['name' => 'group', 'label' => 'Group', 'rules' => ['required', 'string', 'max:80']], ['name' => 'translation_key', 'label' => 'Translation Key', 'rules' => ['required', 'string', 'max:255']], ['name' => 'locale', 'label' => 'Language', 'type' => 'select', 'option_model' => Language::class, 'option_where' => ['is_active' => 1], 'option_value' => 'code', 'option_label' => 'name', 'rules' => ['required', 'string', 'max:10']], ['name' => 'value', 'label' => 'Translated Text', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['required', 'string']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],
        'support' => [
            'label' => 'Support', 'group' => 'System', 'singular' => 'Support Ticket', 'model' => SupportTicket::class, 'with' => ['user'], 'search' => ['ticket_no', 'subject', 'message'], 'status_column' => 'status', 'status_options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'ticket_no', 'label' => 'Ticket'], ['key' => 'user.name', 'label' => 'User'], ['key' => 'subject', 'label' => 'Subject'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'created_at', 'label' => 'Created', 'type' => 'datetime']],
            'fields' => [['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['required', 'string']]],
        ],
    ],
];

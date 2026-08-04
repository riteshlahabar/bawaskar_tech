@php
    $groups = config('admin.groups', []);

    $isItemActive = function (array $item): bool {
        if (empty($item['route'])) {
            return false;
        }

        $active = request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']);

        if (! $active) {
            return false;
        }

        foreach (($item['params'] ?? []) as $key => $value) {
            if ((string) request($key) !== (string) $value) {
                return false;
            }
        }

        return true;
    };

    $itemUrl = fn (array $item): string => route($item['route'], $item['params'] ?? []);

    $icons = [
        'dashboard-erp' => 'home',
        'dashboard-hrms' => 'activity',
        'dealers' => 'shopping-bag',
        'customers' => 'users',
        'salesmen' => 'user-check',
        'couriers' => 'truck',

        'customer-sales' => 'user',
        'dealer-sales' => 'briefcase',
        'customer-orders' => 'shopping-cart',
        'dealer-orders' => 'shopping-cart',
        'customer-proforma-invoices' => 'file-text',
        'dealer-proforma-invoices' => 'file-text',
        'customer-invoices' => 'file',
        'dealer-invoices' => 'file',
        'customer-dispatches' => 'truck',
        'dealer-dispatches' => 'truck',
        'customer-returns' => 'rotate-ccw',
        'dealer-returns' => 'rotate-ccw',

        'products' => 'box',
        'categories' => 'list',
        'brands' => 'award',
        'units' => 'sliders',
        'inventory' => 'package',
        'warehouses' => 'home',
        'batches' => 'calendar',

        'payments' => 'credit-card',
        'collections' => 'dollar-sign',
        'outstanding' => 'trending-up',
        'internal-expenses' => 'clipboard',
        'expense-categories' => 'list',
        'expense-subcategories' => 'menu',

        'timesheet' => 'clock',
        'attendance' => 'check-circle',
        'leaves' => 'calendar',
        'bulk-attendance' => 'grid',
        'dealer-visits' => 'map-pin',
        'tour-plans' => 'map',
        'expenses' => 'dollar-sign',
        'salary' => 'briefcase',
        'targets' => 'target',
        'assets' => 'monitor',

        'notifications' => 'bell',
        'languages' => 'globe',
        'translations' => 'type',
        'support' => 'headphones',
        'reports' => 'bar-chart-2',
        'email-templates' => 'mail',
    ];

    $groupIcons = [
        'peopleMenu' => 'users',
        'salesMenu' => 'shopping-cart',
        'productInventoryMenu' => 'package',
        'financeMenu' => 'credit-card',
        'companyExpenseMenu' => 'clipboard',
        'systemMenu' => 'settings',
    ];
@endphp

<style>
    .sidebar-wrapper .logo-wrapper.logo-wrapper-center {
        min-height: 62px;
        padding: 10px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        margin-bottom: 4px;
    }

    .bawaskar-sidebar-brand {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        text-decoration: none;
    }

    .bawaskar-sidebar-brand img {
        width: 30px;
        height: 30px;
        object-fit: contain;
        flex: 0 0 30px;
        background: #ffffff;
        border-radius: 6px;
        padding: 2px;
    }

    .bawaskar-sidebar-brand .brand-title {
        display: flex;
        flex-direction: column;
        line-height: 1.05;
        color: #ffffff;
    }

    .bawaskar-sidebar-brand .brand-title strong {
        font-size: 14px;
        font-weight: 700;
        color: #ffffff;
        white-space: nowrap;
    }

    .bawaskar-sidebar-brand .brand-title small {
        font-size: 11px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.82);
        margin-top: 2px;
        text-transform: lowercase;
    }

    .sidebar-wrapper .toggle-sidebar {
        display: none !important;
    }

    .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-main-title {
        display: none !important;
    }

    .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list {
        margin: 0 !important;
        padding: 0 !important;
    }

    .sidebar-wrapper .sidebar-main .sidebar-links .sidebar-list > a.sidebar-link {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
        margin: 0 !important;
        min-height: 38px;
    }

    .sidebar-wrapper .sidebar-submenu {
        margin-top: 0 !important;
        margin-bottom: 2px !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
    }

    .sidebar-wrapper .sidebar-submenu li a {
        padding-top: 6px !important;
        padding-bottom: 6px !important;
        min-height: 32px;
    }

    .menu-arrow {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 13px;
    }

    .sidebar-title.active .menu-arrow i,
    .submenu-title.active .sub-arrow i {
        transform: rotate(90deg);
    }

    .menu-arrow i,
    .sub-arrow i {
        transition: all 0.2s ease;
    }
</style>

<div class="sidebar-wrapper">
    <div id="sidebarEffect"></div>

    <div>
        <div class="logo-wrapper logo-wrapper-center">
            <a href="{{ route('admin.dashboard') }}" class="bawaskar-sidebar-brand">
                <img src="{{ asset('logo/logo.png') }}" alt="Dr Bawasakar Technology">

                <span class="brand-title">
                    <strong>Dr Bawasakar</strong>
                    <small>technology</small>
                </span>
            </a>

            <div class="back-btn">
                <i class="fa fa-angle-left"></i>
            </div>
        </div>

        <div class="logo-icon-wrapper">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('logo/logo.png') }}" alt="Dr Bawasakar Technology" style="width:30px;height:30px;object-fit:contain;display:block;margin:12px auto;background:#fff;border-radius:6px;padding:2px;">
            </a>
        </div>

        <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow">
                <i data-feather="arrow-left"></i>
            </div>

            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    <li class="back-btn"></li>

                    @foreach($groups as $group)
                        @php
                            $groupOpen = collect($group['items'] ?? [])->contains(function ($item) use ($isItemActive) {
                                return !empty($item['children'])
                                    ? collect($item['children'])->contains(fn ($child) => $isItemActive($child))
                                    : $isItemActive($item);
                            });
                        @endphp

                        @if(!isset($group['id']))
                            @foreach($group['items'] as $item)
                                @if(!empty($item['children']))
                                    @php
                                        $open = collect($item['children'])->contains(fn ($child) => $isItemActive($child));
                                    @endphp

                                    <li class="sidebar-list">
                                        <a class="sidebar-link sidebar-title {{ $open ? 'active' : '' }}" href="javascript:void(0)">
                                            <i data-feather="{{ $icons[$item['key']] ?? 'circle' }}"></i>
                                            <span>{{ $item['label'] }}</span>
                                            <span class="menu-arrow"><i class="fa fa-angle-right"></i></span>
                                        </a>

                                        <ul class="sidebar-submenu" style="display:{{ $open ? 'block' : 'none' }}">
                                            @foreach($item['children'] as $child)
                                                <li>
                                                    <a class="{{ $isItemActive($child) ? 'active' : '' }}" href="{{ $itemUrl($child) }}">
                                                        {{ $child['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @else
                                    <li class="sidebar-list">
                                        <a class="sidebar-link sidebar-title link-nav {{ $isItemActive($item) ? 'active' : '' }}" href="{{ $itemUrl($item) }}">
                                            <i data-feather="{{ $icons[$item['key']] ?? 'circle' }}"></i>
                                            <span>{{ $item['label'] }}</span>

                                            @isset($item['badge'])
                                                <span class="badge badge-light-primary ms-auto">{{ $item['badge'] }}</span>
                                            @endisset
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @else
                            <li class="sidebar-list">
                                <a class="sidebar-link sidebar-title {{ $groupOpen ? 'active' : '' }}" href="javascript:void(0)">
                                    <i data-feather="{{ $groupIcons[$group['id']] ?? 'folder' }}"></i>
                                    <span>{{ $group['label'] }}</span>
                                    <span class="menu-arrow"><i class="fa fa-angle-right"></i></span>
                                </a>

                                <ul class="sidebar-submenu" style="display:{{ $groupOpen ? 'block' : 'none' }}">
                                    @foreach($group['items'] as $item)
                                        @if(!empty($item['children']))
                                            @php
                                                $open = collect($item['children'])->contains(fn ($child) => $isItemActive($child));
                                            @endphp

                                            <li>
                                                <a class="submenu-title {{ $open ? 'active' : '' }}" href="javascript:void(0)">
                                                    {{ $item['label'] }}
                                                    <span class="sub-arrow"><i class="fa fa-angle-right"></i></span>
                                                </a>

                                                <ul class="nav-sub-childmenu submenu-content" style="display:{{ $open ? 'block' : 'none' }}">
                                                    @foreach($item['children'] as $child)
                                                        <li>
                                                            <a class="{{ $isItemActive($child) ? 'active' : '' }}" href="{{ $itemUrl($child) }}">
                                                                {{ $child['label'] }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @else
                                            <li>
                                                <a class="{{ $isItemActive($item) ? 'active' : '' }}" href="{{ $itemUrl($item) }}">
                                                    {{ $item['label'] }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <div class="right-arrow" id="right-arrow">
                <i data-feather="arrow-right"></i>
            </div>
        </nav>
    </div>
</div>
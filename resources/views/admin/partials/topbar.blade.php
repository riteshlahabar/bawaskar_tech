<div class="page-header">
    <div class="header-wrapper m-0">
        <div class="header-logo-wrapper p-0">
            <div class="logo-wrapper">
                <a href="{{ route('admin.dashboard') }}" class="bawaskar-brand">
                    <span class="brand-mark">B</span><span class="brand-text">Bawaskar ERP</span>
                </a>
            </div>
            <div class="toggle-sidebar"><i data-feather="menu" class="status_toggle middle sidebar-toggle"></i></div>
        </div>
        <form class="form-inline search-full" action="{{ route('admin.products.index') }}" method="get">
            <div class="form-group w-100"><div class="Typeahead Typeahead--twitterUsers">
                <div class="u-posRelative"><input class="demo-input Typeahead-input form-control-plaintext w-100" type="search" name="search" placeholder="Search products..."><i class="ri-close-line close-search"></i></div>
            </div></div>
        </form>
        <div class="nav-right col-6 pull-right right-header p-0">
            <ul class="nav-menus">
                <li><span class="header-search"><i data-feather="search"></i></span></li>
                <li class="onhover-dropdown"><div class="notification-box"><i data-feather="bell"></i></div>
                    <ul class="notification-dropdown onhover-show-div"><li><i data-feather="bell"></i><h6 class="f-18 mb-0">Notifications</h6></li><li><a class="btn btn-primary w-100" href="{{ route('admin.notifications.index') }}">Open Notification Centre</a></li></ul>
                </li>
                <li class="profile-nav onhover-dropdown pe-0 me-0">
                    <div class="media profile-media"><div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div><div class="user-name-hide media-body"><span>{{ auth()->user()->name }}</span><p class="mb-0 font-roboto">Administrator <span class="middle">⌄</span></p></div></div>
                    <ul class="profile-dropdown onhover-show-div">
                        <li><a href="{{ route('admin.dashboard') }}"><i data-feather="home"></i><span>Dashboard</span></a></li>
                        <li><a href="{{ route('admin.email-templates.index') }}"><i data-feather="mail"></i><span>Email Templates</span></a></li>
                        <li><form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="dropdown-logout"><i data-feather="log-out"></i><span>Log out</span></button></form></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
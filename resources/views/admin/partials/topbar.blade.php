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
        <div class="nav-right col-6 pull-right right-header p-0 admin-header-actions">
            <ul class="nav-menus">
                <li class="onhover-dropdown admin-header-notification"><div class="notification-box"><i data-feather="bell"></i></div>
                    <ul class="notification-dropdown onhover-show-div"><li><i data-feather="bell"></i><h6 class="f-18 mb-0">Notifications</h6></li><li><a class="btn btn-primary w-100" href="{{ route('admin.notifications.index') }}">Open Notification Centre</a></li></ul>
                </li>
                <li class="profile-nav onhover-dropdown pe-0 me-0">
                    <div class="media profile-media admin-profile-media">
                        <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                        <div class="user-name-hide media-body">
                            <span>{{ auth()->user()->name }}</span>
                            <p class="mb-0 font-roboto">{{ auth()->user()->adminRole?->name ?? 'Admin' }}<i class="middle ri-arrow-down-s-line"></i></p>
                        </div>
                    </div>
                    <ul class="profile-dropdown onhover-show-div">
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-logout">
                                    <i data-feather="log-out"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
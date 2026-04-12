<!--! ================================================================ !-->
<!--! [Start] Navigation Manu !-->
<!--! ================================================================ !-->
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="index.html" class="b-brand">
                <!-- ========   change your logo hear   ============ -->
                <img src="{{ config('custom.public_path') . '/adminAssets/assets/images/logo-full.png'}}" alt=""
                    class="logo logo-lg" />
                <img src="{{ config('custom.public_path') . '/adminAssets/assets/images/logo-abbr.png'}}" alt=""
                    class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Navigation</label>
                </li>
                <li class="nxl-item nxl-hasmenu {{ request()->is('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Dashboards</span>
                    </a>
                </li>
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Role/Permission</span><span class="nxl-arrow"><i
                                class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu"
                        style="display: {{ request()->is('admin.permissions/*') || request()->is('roles/*') ? 'block' : 'none' }};">
                        @can('Permission-Management')
                            <li class="nxl-item {{ request()->is('admin.permissions/*') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('permissions.index') }}">Permission</a>
                            </li>
                        @endcan
                        @can('Role-Management')
                            <li class="nxl-item {{ request()->is('roles/*') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('roles.index') }}">Roles</a>
                            </li>
                        @endcan
                    </ul>
                </li>

                {{-- @can('Permission-Management')
                <li class="nxl-item nxl-hasmenu {{ request()->is('admin.permissions/*') ? 'active' : '' }}">
                    <a href="{{ route('permissions.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Permission Management</span>
                    </a>
                </li>
                @endcan
                @can('Role-Management')
                <li class="nxl-item nxl-hasmenu {{ request()->is('roles/*') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Role Management</span>
                    </a>
                </li>
                @endcan
                --}}

                @can('Customer-Management')
                    <li class="nxl-item nxl-hasmenu {{ request()->is('customers/*') ? 'active' : '' }}">
                        <a href="{{ route('customers.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-users"></i></span>
                            <span class="nxl-mtext">Customers Management</span>
                        </a>
                    </li>
                @endcan
                @can('Shop-Management')
                    <li class="nxl-item nxl-hasmenu {{ request()->is('admin/user-shops/*') ? 'active' : '' }}">
                        <a href="{{ route('user-shops.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-shopping-bag"></i></span>
                            <span class="nxl-mtext">User Shop Management</span>
                        </a>
                    </li>
                @endcan
                {{-- @can('Main-Category-Management')
                <li class="nxl-item nxl-hasmenu">
                    <a href="{{ route('main-categories.index') }} {{ request()->is('main-categories/*') ? 'active' : '' }}"
                        class="nxl-link">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">Main Categories Management</span>
                    </a>
                </li>
                @endcan
                @can('Category-Management')
                <li class="nxl-item nxl-hasmenu {{ request()->is('adnub/customers/*') ? 'active' : '' }}">
                    <a href="{{ route('customers.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">Category Management</span>
                    </a>
                </li>
                @endcan
                @can('Sub-Category-Management')
                <li class="nxl-item nxl-hasmenu {{ request()->is('customers/*') ? 'active' : '' }}">
                    <a href="{{ route('customers.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">Sub Category Management</span>
                    </a>
                </li>
                @endcan
                --}}
                <li
                    class="nxl-item nxl-hasmenu nxl-trigger {{ (request()->is('main-categories/*') || request()->is('another-category/*') || request()->is('some-route')) ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Category Management</span><span class="nxl-arrow"><i
                                class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu"
                        style="display: {{ (request()->is('admin/main-categories*') || request()->is('admin/categories*') || request()->is('admin/sub-categories&*')) ? 'block' : 'none' }};">
                        @can('Main-Category-Management')
                            <li class="nxl-item {{ request()->is('main-categories/*') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('main-categories.index') }}">Main Categories</a>
                            </li>
                        @endcan
                        @can('Category-Management')
                            <li class="nxl-item {{ request()->is('admin/categories/*') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('categories.index') }}">Categories</a>
                            </li>
                        @endcan
                        @can('Sub-Category-Management')
                            <li class="nxl-item {{ request()->is('main-categories/*') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('sub-categories.index') }}">Sub Categories</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!--! ================================================================ !-->
<!--! [End]  Navigation Manu !-->
<!--! ================================================================ !-->
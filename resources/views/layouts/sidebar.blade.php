<body>

    <div class="app-wrapper">

        <div class="loader-wrapper">
            <div class="loader_16"></div>
        </div>

        <!-- Menu Navigation starts -->
        <nav>
            <div class="app-logo d-flex align-items-center ps-3 py-3">
    <a class="logo d-none d-md-flex align-items-center text-decoration-none" href="{{ route('home') }}">
        <div class="overflow-hidden rounded-circle border-2 border-success d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <img src="{{ asset('assets/images/logo/1.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <span class="ms-2 fw-bold text-dark fs-5">Tree Expert</span>
    </a>

    <span class="bg-light-primary toggle-semi-nav ms-auto">
        <i class="ti ti-chevrons-right f-s-20"></i>
    </span>
</div>
            <div class="app-nav" id="app-simple-bar" style="overflow-y: auto; max-height: calc(100vh - 150px); padding-bottom: 20px;">

                <ul class="main-nav p-0 mt-2">

                    <li class="no-sub {{ request()->routeIs('home') ? 'active' : '' }}">
                        <a class="" href="{{ route('home') }}">
                            <i class="ph-duotone ph-house-line"></i> Dashboard
                        </a>
                    </li>

                    @canany(['project', 'project.store', 'project.create', 'project.edit', 'project.view', 'project.delete'])
                        <li class="{{ request()->routeIs('add.project', 'project.list') ? 'active' : '' }}">
                            <a class="" data-bs-toggle="collapse" href="#dashboard" aria-expanded="false">
                                <i class="ph-duotone ph-boat"></i>
                                Project
                                <span class="badge badge-notification ms-2" style="background-color: #7cb342; color: white;">2</span>
                            </a>
                            <ul class="collapse" id="dashboard">
                                @can('project.store')
                                    <li class="{{ request()->routeIs('add.project') ? 'active' : '' }}">
                                        <a href="{{ route('add.project') }}">Add Project</a>
                                    </li>
                                @endcan

                                @can('project.list')
                                    <li class="{{ request()->routeIs('project.list') ? 'active' : '' }}">
                                        <a href="{{ route('project.list') }}">Project List</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @can('tree_data')
                        <li class="{{ request()->routeIs('tree.*') ? 'active' : '' }}">
                            <a class="" data-bs-toggle="collapse" href="#ghaats" aria-expanded="false">
                                <i class="ph-duotone ph-waves"></i>
                                Tree Data
                                <span class="badge badge-notification ms-2" style="background-color: #7cb342; color: white;">2</span>
                            </a>
                            <ul class="collapse" id="ghaats">
                                <li class="{{ request()->routeIs('tree.add.data') ? 'active' : '' }}">
                                    <a href="{{ route('tree.add.data') }}">Add Tree Data</a>
                                </li>
                                <li class="{{ request()->routeIs('tree.add.data.multiple') ? 'active' : '' }}">
                                    <a href="{{ route('tree.add.data.multiple') }}">Add Multiple Tree Data</a>
                                </li>
                                <li class="{{ request()->routeIs('tree.list') ? 'active' : '' }}">
                                    <a href="{{ route('tree.list') }}">Tree List</a>
                                </li>
                                <li class="{{ request()->routeIs('tree.name.add') ? 'active' : '' }}">
                                    <a href="{{ route('tree.name.add') }}">Create Tree Name</a>
                                </li>
                                <li class="{{ request()->routeIs('tree.name.list') ? 'active' : '' }}">
                                    <a href="{{ route('tree.name.list') }}">Tree Name List</a>
                                </li>
                                <li class="{{ request()->routeIs('tree.price.list') ? 'active' : '' }}">
                                    <a href="{{ route('tree.price.list') }}">Tree Price</a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                    @can('map')
                        <li class="{{ request()->routeIs('tree.map') ? 'active' : '' }}">
                            <a class="" data-bs-toggle="collapse" href="#TreeMapMenu" aria-expanded="false">
                                <i class="ph-duotone ph-shield"></i>
                                Map
                                <span class="badge badge-notification ms-2" style="background-color: #7cb342; color: white;">1</span>
                            </a>
                            <ul class="collapse" id="TreeMapMenu">
                                <li class="{{ request()->routeIs('tree.map') ? 'active' : '' }}">
                                    <a href="{{ route('tree.map') }}">Tree On Map</a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                    @can('master')
                        <li class="{{ request()->routeIs('project.report', 'tree.report', 'district.index', 'tahsil.index') ? 'active' : '' }}">
                            <a class="" data-bs-toggle="collapse" href="#Inspection" aria-expanded="false">
                                <i class="ph-duotone ph-magnifying-glass"></i>
                                Master
                                <span class="badge badge-notification ms-2" style="background-color: #7cb342; color: white;">4</span>
                            </a>
                            <ul class="collapse" id="Inspection">
                                <li class="{{ request()->routeIs('project.report') ? 'active' : '' }}">
                                    <a href="{{ route('project.report') }}">Project Report</a>
                                </li>
                                <!--<li class="{{ request()->routeIs('tree.report') ? 'active' : '' }}">-->
                                <!--    <a href="{{ route('tree.report') }}">Tree Report</a>-->
                                <!--</li>-->
                                <!--<li class="{{ request()->routeIs('district.index') ? 'active' : '' }}">-->
                                <!--    <a href="{{ route('district.index') }}">District</a>-->
                                <!--</li>-->
                                <!--<li class="{{ request()->routeIs('tahsil.index') ? 'active' : '' }}">-->
                                <!--    <a href="{{ route('tahsil.index') }}">Taluka</a>-->
                                <!--</li>-->
                            </ul>
                        </li>
                    @endcan

                    @can('user_management')
                        <li class="{{ request()->routeIs('roles.*', 'create.user', 'user.list') ? 'active' : '' }}">
                            <a class="" data-bs-toggle="collapse" href="#User" aria-expanded="false">
                                <i class="ph-duotone ph-user"></i>
                                User Management
                                <span class="badge badge-notification ms-2" style="background-color: #7cb342; color: white;">3</span>
                            </a>
                            <ul class="collapse" id="User">
                                @can('user_management.role.view')
                                    <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                        <a href="{{ route('roles.index') }}">Role</a>
                                    </li>
                                @endcan
                                @can('user_management.user.create')
                                    <li class="{{ request()->routeIs('create.user') ? 'active' : '' }}">
                                        <a href="{{ route('create.user') }}">Create User</a>
                                    </li>
                                @endcan
                                @can('user_management.user.view')
                                    <li class="{{ request()->routeIs('user.list') ? 'active' : '' }}">
                                        <a href="{{ route('user.list') }}">User List</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('master')
                        <li class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <a class="" data-bs-toggle="collapse" href="#Settings" aria-expanded="false">
                                <i class="ph-duotone ph-gear"></i>
                                Settings
                                <span class="badge badge-notification ms-2" style="background-color: #7cb342; color: white;">1</span>
                            </a>
                            <ul class="collapse" id="Settings">
                                <li class="{{ request()->routeIs('admin.settings.otp') ? 'active' : '' }}">
                                    <a href="{{ route('admin.settings.otp') }}">OTP Settings</a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                    @canany(['other', 'other.faqs', 'other.videos', 'other.contacts', 'other.notes', 'other.privacy'])
                        <li class="{{ request()->routeIs('rate.app', 'faqs.*', 'videos.*', 'contacts.*', 'notes.*', 'privacy.*') ? 'active' : '' }}">
                            <a class="" data-bs-toggle="collapse" href="#other" aria-expanded="false">
                                <i class="ph-duotone ph-boat"></i>
                                Other
                                <span class="badge badge-notification ms-2" style="background-color: #7cb342; color: white;">6</span>
                            </a>
                            <ul class="collapse" id="other">
                                @can('other')
                                    <li class="{{ request()->routeIs('rate.app') ? 'active' : '' }}">
                                        <a href="{{ route('rate.app') }}">Rate App</a>
                                    </li>
                                @endcan

                                @can('other.faqs')
                                    <li class="{{ request()->routeIs('faqs.*') ? 'active' : '' }}">
                                        <a href="{{ route('faqs.index') }}">FAQ</a>
                                    </li>
                                @endcan

                                @can('other.videos')
                                    <li class="{{ request()->routeIs('videos.*') ? 'active' : '' }}">
                                        <a href="{{ route('videos.index') }}">Video Tutorial</a>
                                    </li>
                                @endcan

                                @can('other.contacts')
                                    <li class="{{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                                        <a href="{{ route('contacts.index') }}">Contact Us</a>
                                    </li>
                                @endcan

                                @can('other.notes')
                                    <li class="{{ request()->routeIs('notes.*') ? 'active' : '' }}">
                                        <a href="{{ route('notes.index') }}">Note</a>
                                    </li>
                                @endcan

                                @can('other.privacy')
                                    <li class="{{ request()->routeIs('privacy.*') ? 'active' : '' }}">
                                        <a href="{{ route('privacy.index') }}">Privacy Policy</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    <li class="no-sub {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                       <a class="" href="{{ route('admin.subscriptions') }}">
                          <i class="ph-duotone ph-chats"></i> Payment
                       </a>
                    </li>
                </ul>
            </div>

            <div class="menu-navs">
                <span class="menu-previous"><i class="ti ti-chevron-left"></i></span>
                <span class="menu-next"><i class="ti ti-chevron-right"></i></span>
            </div>

        </nav>

        <style>
           

            /* Hover effect for main menu items */
            .main-nav li a:hover {
                color: #7cb342 !important;
            }

            .main-nav li a:hover i {
                color: #7cb342 !important;
            }
            
            /* Active state for main menu items */
            .main-nav li.active > a,
            .main-nav li > a[aria-expanded="true"] {
                color: #7cb342 !important;
                background-color: #f0f7e8 !important;
            }

            .main-nav li.active > a i,
            .main-nav li > a[aria-expanded="true"] i {
                color: #7cb342 !important;
            }

            /* Hover effect for submenu items */
            .main-nav .collapse li a:hover {
                background-color: #f0f7e8 !important;
                color: #7cb342 !important;
            }

            /* Active state for submenu items */
            .main-nav .collapse li.active a {
                /*background-color: #7cb342 !important;*/
                color: #7cb342 !important;
            }

            /* Ensure scrolling works */
            #app-simple-bar {
                height: auto !important;
            }
        </style>

</body>
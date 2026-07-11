<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - SIPTP</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ============================================================== --}}
    {{-- CSS MAZER (Compiled dari jsDelivr CDN — Mazer GitHub Release)   --}}
    {{-- Ini mengarah ke file CSS yang sudah di-compile dari repository  --}}
    {{-- resmi Mazer, sehingga tidak perlu compile SCSS secara manual.   --}}
    {{-- ============================================================== --}}
    <link rel="stylesheet" href="{{ asset('vendor/nunito/nunito.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/choices/css/choices.min.css') }}" />

    {{-- MAZER CUSTOM CSS (Light Theme Default) --}}
    {{-- Jika Anda sudah punya file app.css hasil compile SCSS, ganti URL di bawah --}}
    {{-- dengan: {{ asset('template/mazer/assets/compiled/css/app.css') }}           --}}
    <style>

        /* Choices.js Custom Styling to Match Bootstrap/Mazer */
        .choices {
            margin-bottom: 0;
        }
        .choices__inner {
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            min-height: 38px;
            padding: 4px 8px;
            font-size: 0.9rem;
        }
        .choices__list--single {
            padding: 4px 16px 4px 4px;
        }
        .choices__list--dropdown {
            z-index: 1050;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }
        .choices__input {
            background-color: #f8f9fa;
            font-size: 0.9rem;
        }
        .choices[data-type*=select-one] .choices__inner {
            padding-bottom: 4px;
        }
        .choices[data-type*=select-one]::after {
            border-color: #607080 transparent transparent transparent;
            border-width: 5px;
            right: 15px;
        }
        .choices[data-type*=select-one].is-open::after {
            border-color: transparent transparent #607080 transparent;
            border-width: 5px;
        }

        :root {
            --bs-body-font-family: 'Nunito', sans-serif;
            --bs-body-bg: #f2f7ff;
            --bs-body-color: #607080;
        }

        body {
            font-family: var(--bs-body-font-family);
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            overflow-x: hidden;
        }

        /* ====== APP LAYOUT ====== */
        #app {
            display: flex;
            min-height: 100vh;
        }

        /* ====== SIDEBAR ====== */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            z-index: 1030;
            overflow-y: auto;
            background-color: #fff;
            transition: transform 0.3s ease;
        }

        #sidebar:not(.active) {
            transform: translateX(-280px);
        }

        #sidebar.active {
            transform: translateX(0);
        }

        .sidebar-wrapper {
            padding: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .sidebar-header .logo a {
            text-decoration: none;
            color: #25396f;
        }

        .sidebar-header .logo h4 {
            font-weight: 800;
        }

        .sidebar-menu {
            padding: 0.5rem 0;
            flex: 1;
        }

        .sidebar-menu .menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-title {
            padding: 1rem 1.5rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #aab0bc;
        }

        .sidebar-item {
            position: relative;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.7rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #607080;
            text-decoration: none;
            transition: all 0.2s ease;
            border-radius: 0;
            gap: 0.75rem;
        }

        .sidebar-link:hover {
            background-color: #f0f4ff;
            color: #435ebe;
        }

        .sidebar-link i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .sidebar-item.active > .sidebar-link {
            background-color: #435ebe;
            color: #fff;
        }

        .sidebar-item.active > .sidebar-link:hover {
            background-color: #3a52b5;
            color: #fff;
        }

        /* Submenu */
        .sidebar-item.has-sub > .sidebar-link::after {
            content: '\F285';
            font-family: 'bootstrap-icons';
            margin-left: auto;
            font-size: 0.7rem;
            transition: transform 0.3s ease;
        }

        .sidebar-item.has-sub.menu-open > .sidebar-link::after {
            transform: rotate(90deg);
        }

        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
            background-color: #f9fbfd;
        }

        .submenu.submenu-open {
            max-height: 500px;
        }

        .submenu.submenu-closed {
            max-height: 0;
        }

        .submenu-item a {
            display: block;
            padding: 0.5rem 1.5rem 0.5rem 3.5rem;
            font-size: 0.85rem;
            color: #607080;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .submenu-item a:hover {
            color: #435ebe;
            background-color: #eef1ff;
        }

        /* ====== MAIN CONTENT ====== */
        #main {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        /* ====== HEADER / BURGER BTN ====== */
        .burger-btn {
            display: none;
            color: #607080;
        }

        header.mb-3 {
            margin-bottom: 1rem !important;
        }

        /* ====== PAGE HEADING ====== */
        .page-heading h3 {
            color: #25396f;
            font-weight: 700;
        }

        .page-heading .text-subtitle {
            font-size: 0.85rem;
        }

        .breadcrumb-item a {
            color: #435ebe;
            text-decoration: none;
        }

        /* ====== CARD ====== */
        .card {
            border: none;
            border-radius: 0.6rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.06);
            margin-bottom: 1.5rem;
            background-color: #fff;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #e9ecef;
            padding: 1.25rem 1.5rem 0.75rem;
        }

        .card-header h4 {
            font-weight: 700;
            color: #25396f;
            margin-bottom: 0;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 1.25rem 1.5rem;
        }

        /* ====== STATS ICONS ====== */
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
        }

        .stats-icon.purple { background-color: #9694ff; }
        .stats-icon.blue   { background-color: #57caeb; }
        .stats-icon.green  { background-color: #5ddab4; }
        .stats-icon.red    { background-color: #ff7976; }

        .font-semibold { font-weight: 600; }
        .font-extrabold { font-weight: 800; }
        .font-bold { font-weight: 700; }

        .py-4-5 {
            padding-top: 1.75rem !important;
            padding-bottom: 1.75rem !important;
        }

        /* ====== FOOTER ====== */
        footer {
            margin-top: 2rem;
        }

        footer .footer {
            padding: 1rem 0;
        }

        footer .footer p {
            margin-bottom: 0;
            font-size: 0.85rem;
        }

        /* ====== SIDEBAR BACKDROP (MOBILE) ====== */
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1020;
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 1199.98px) {
            #sidebar {
                transform: translateX(-280px);
            }

            #sidebar.active {
                transform: translateX(0);
            }

            #main {
                margin-left: 0;
                width: 100%;
            }

            .burger-btn {
                display: block;
            }
        }

        /* ====== ICONLY FONT (for stat icons) ====== */
        @font-face {
            font-family: 'Iconly';
            src: url("{{ asset('template/mazer/assets/static/fonts/Iconly---Bold.eot') }}");
            src: url("{{ asset('template/mazer/assets/static/fonts/Iconly---Bold.eot') }}?#iefix") format('embedded-opentype'),
                 url("{{ asset('template/mazer/assets/static/fonts/Iconly---Bold.woff') }}") format('woff'),
                 url("{{ asset('template/mazer/assets/static/fonts/Iconly---Bold.ttf') }}") format('truetype'),
                 url("{{ asset('template/mazer/assets/static/fonts/Iconly---Bold.svg') }}#Iconly---Bold") format('svg');
            font-weight: normal;
            font-style: normal;
            font-display: block;
        }

        [class^="iconly-"], [class*=" iconly-"] {
            font-family: 'Iconly' !important;
            speak: never;
            font-style: normal;
            font-weight: normal;
            font-variant: normal;
            text-transform: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .iconly-boldShow:before { content: "\ea6e"; }
        .iconly-boldProfile:before { content: "\ea56"; }
        .iconly-boldAdd-User:before { content: "\e901"; }
        .iconly-boldBookmark:before { content: "\e90d"; }
        .iconly-boldHeart:before { content: "\ea31"; }
        .iconly-boldDocument:before { content: "\ea21"; }
        .iconly-boldWallet:before { content: "\ea82"; }
        .iconly-boldGraph:before { content: "\ea2d"; }
    </style>

    {{-- CSS DINAMIS TAMBAHAN (per-halaman: DataTables, SweetAlert2, dll) --}}
    @stack('styles')
</head>

<body>
    <script src="{{ asset('template/mazer/assets/static/js/initTheme.js') }}"></script>

    <div id="app">
        <div id="sidebar" class="active">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="#">
                                SIPTP
                            </a>
                        </div>
                        <div class="toggler">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle fs-4"></i></a>
                        </div>
                    </div>
                </div>

                <div class="sidebar-menu">
                    <ul class="menu">
                        @if(current_user_role() === 'staff')
                            @include('partials.sidebar.staff')
                            @if(is_admin() == 1)
                                @include('partials.sidebar.admin')
                            @endif
                        @elseif(in_array(current_user_role(), ['spv','manager','direktur']))
                            @include('partials.sidebar.approver')
                            @if(is_admin() == 1)
                                @include('partials.sidebar.admin')
                            @endif
                        @elseif(current_user_role() === 'finance')
                            @include('partials.sidebar.finance')
                            @if(is_admin() == 1)
                                @include('partials.sidebar.admin')
                            @endif
                        @endif

                        {{-- Profile --}}
                        <li class="sidebar-title">Akun</li>
                        <li class="sidebar-item">
                            <a href="#" class='sidebar-link' id="btnOpenProfile">
                                <i class="bi bi-person-circle"></i>
                                <span>{{ Session::get('users_user_name')}}</span>
                            </a>
                        </li>

                        {{-- Logout --}}
                        <li class="sidebar-item">
                            <a href="#" class='sidebar-link text-danger' id="btnLogout">
                                <i class="bi bi-box-arrow-left text-danger"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>


        {{-- ============================================================ --}}
        {{-- MAIN CONTENT                                                 --}}
        {{-- ============================================================ --}}
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>@yield('page-title', 'Dashboard')</h3>
                            <p class="text-subtitle text-muted">@yield('page-subtitle', 'Halaman utama panel admin.')</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    @yield('breadcrumb')
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- KONTEN DINAMIS  (@yield('content'))              -->
            <!-- ============================================================ -->
            <div class="page-content">
                @yield('content')
            </div>
            <!-- ============================================================ -->
            <!-- KONTEN DINAMIS BERAKHIR                                -->
            <!-- ============================================================ -->

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>&copy; {{ date('Y') }} SIPTP</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SCRIPTS                                                       --}}
    {{-- ============================================================ --}}
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/choices/js/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/ajax-helper.js') }}"></script>

    {{-- Sidebar JS (Standalone — no import/build required) --}}
    <script>
    (function() {
        /**
         * Helper: is desktop?
         */
        function isDesktop(win) {
            return win.innerWidth >= 1200;
        }

        /**
         * Calculate nested children height in sidebar menu
         */
        function calculateChildrenHeight(el, deep) {
            var children = el.children;
            var height = 0;
            for (var i = 0; i < el.childElementCount; i++) {
                var child = children[i];
                var link = child.querySelector('.submenu-item a');
                if (link) height += link.offsetHeight;
                if (deep && child.classList.contains('has-sub')) {
                    var subsubmenu = child.querySelector('.submenu');
                    if (subsubmenu && subsubmenu.classList.contains('submenu-open')) {
                        var links = subsubmenu.querySelectorAll('.submenu-item a');
                        links.forEach(function(l) { height += l.offsetHeight; });
                    }
                }
            }
            el.style.setProperty('--submenu-height', height + 'px');
            return height;
        }

        /**
         * Sidebar Class
         */
        function Sidebar(el) {
            this.sidebarEL = typeof el === 'string' ? document.querySelector(el) : el;
            if (!this.sidebarEL) return;
            this.init();
        }

        Sidebar.prototype.init = function() {
            var self = this;

            // Burger button toggle
            document.querySelectorAll('.burger-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.toggle();
                });
            });

            // Sidebar hide button (mobile X)
            document.querySelectorAll('.sidebar-hide').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.hide();
                });
            });

            // Window resize
            window.addEventListener('resize', this.onResize.bind(this));

            // Toggle submenu function
            function toggleSubmenu(el) {
                if (el.classList.contains('submenu-open')) {
                    el.classList.remove('submenu-open');
                    el.classList.add('submenu-closed');
                } else {
                    el.classList.remove('submenu-closed');
                    el.classList.add('submenu-open');
                }
            }

            // Sidebar items with sub
            var sidebarItems = document.querySelectorAll('.sidebar-item.has-sub');
            sidebarItems.forEach(function(sidebarItem) {
                var link = sidebarItem.querySelector('.sidebar-link');
                if (link) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        var submenu = sidebarItem.querySelector('.submenu');
                        if (submenu) {
                            toggleSubmenu(submenu);
                            sidebarItem.classList.toggle('menu-open');
                        }
                    });
                }
            });
        };

        Sidebar.prototype.onResize = function() {
            if (isDesktop(window)) {
                this.sidebarEL.classList.add('active');
                this.sidebarEL.classList.remove('inactive');
            } else {
                this.sidebarEL.classList.remove('active');
            }
            this.deleteBackdrop();
        };

        Sidebar.prototype.toggle = function() {
            if (this.sidebarEL.classList.contains('active')) {
                this.hide();
            } else {
                this.show();
            }
        };

        Sidebar.prototype.show = function() {
            this.sidebarEL.classList.add('active');
            this.sidebarEL.classList.remove('inactive');
            this.createBackdrop();
        };

        Sidebar.prototype.hide = function() {
            this.sidebarEL.classList.remove('active');
            this.sidebarEL.classList.add('inactive');
            this.deleteBackdrop();
        };

        Sidebar.prototype.createBackdrop = function() {
            if (isDesktop(window)) return;
            this.deleteBackdrop();
            var backdrop = document.createElement('div');
            backdrop.classList.add('sidebar-backdrop');
            backdrop.addEventListener('click', this.hide.bind(this));
            document.body.appendChild(backdrop);
        };

        Sidebar.prototype.deleteBackdrop = function() {
            var backdrop = document.querySelector('.sidebar-backdrop');
            if (backdrop) backdrop.remove();
        };

        // Initialize on first load
        var sidebarEl = document.getElementById('sidebar');
        if (sidebarEl) {
            if (isDesktop(window)) {
                sidebarEl.classList.add('active');
            }

            // Initialize submenus
            var submenus = document.querySelectorAll('.sidebar-item.has-sub .submenu');
            submenus.forEach(function(submenu) {
                var parent = submenu.parentElement;
                if (parent.classList.contains('active')) {
                    submenu.classList.add('submenu-open');
                } else {
                    submenu.classList.add('submenu-closed');
                }
            });

            new Sidebar(sidebarEl);
        }

        window.Sidebar = Sidebar;
    })();
    </script>

    {{-- Modal Edit Profil --}}
    <div class="modal fade" id="modalProfile" tabindex="-1" aria-labelledby="modalProfileTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formProfile">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalProfileTitle">
                            <i class="bi bi-person-circle me-1"></i> Edit Profil
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="profile_user_name" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="profile_user_name" name="users_user_name"
                                value="{{ Session::get('users_user_name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="profile_email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="profile_email" name="users_email"
                                value="{{ Session::get('users_email') }}" required>
                        </div>
                        <hr>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-info-circle me-1"></i> Kosongkan password jika tidak ingin mengubahnya.
                        </p>
                        <div class="mb-3">
                            <label for="profile_password" class="form-label fw-semibold">Password Baru</label>
                            <input type="password" class="form-control" id="profile_password" name="password"
                                placeholder="Minimal 8 karakter" minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="profile_password_confirmation" class="form-label fw-semibold">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="profile_password_confirmation" name="password_confirmation"
                                placeholder="Ulangi password baru">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- JS DINAMIS TAMBAHAN (per-halaman: DataTables init, Chart.js, dll) --}}
    @stack('scripts')

    <script>
        // Global helper for formatting to Rupiah
        window.formatRupiah = function(value) {
            if (!value) return '';
            let clean = value.toString().replace(/\D/g, '');
            return clean.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        };

        // Global Choices.js initialization
        window.initChoices = function() {
            $('select').each(function() {
                if (!this.classList.contains('choices__input') && !this.classList.contains('dataTables_select') && !$(this).closest('.dataTables_wrapper').length) {
                    new Choices(this, {
                        removeItemButton: false,
                        searchEnabled: true,
                        shouldSort: false,
                        placeholderValue: $(this).attr('placeholder') || $(this).data('placeholder') || '-- Pilih --',
                        itemSelectText: '',
                    });
                }
            });
        };

        $(document).ready(function() {
            initChoices();

            // Profile modal
            $('#btnOpenProfile').on('click', function(e) {
                e.preventDefault();
                $('#profile_password').val('');
                $('#profile_password_confirmation').val('');
                new bootstrap.Modal(document.getElementById('modalProfile')).show();
            });

            // Profile form submit
            $('#formProfile').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();

                ajaxRequest({
                    url: '{{ route("profile.update") }}',
                    method: 'POST',
                    data: formData,
                    confirmTitle: 'Simpan Perubahan Profil?',
                    confirmMessage: 'Apakah Anda yakin ingin menyimpan perubahan profil?',
                    successCallback: function(response) {
                        bootstrap.Modal.getInstance(document.getElementById('modalProfile')).hide();
                        location.reload();
                    }
                });
            });

            $('#btnLogout').on('click', function(e) {
                e.preventDefault();
                ajaxRequest({
                    url: '{{ url("/logout") }}',
                    method: 'POST',
                    confirmBefore: true,
                    confirmMessage: 'Yakin ingin keluar dari sistem?',
                    successCallback: function(response) {
                        if (response.data && response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('css/dashboard/base.css') }}" rel="stylesheet">

    @if(request()->routeIs('dashboard'))
    <link href="{{ asset('css/dashboard/dashboard.css') }}" rel="stylesheet">
    @elseif(request()->routeIs('analyses*'))
    <link href="{{ asset('css/dashboard/analyses.css') }}" rel="stylesheet">
    @elseif(request()->routeIs('reservation.requests*'))
    <link href="{{ asset('css/dashboard/reservation-requests.css') }}" rel="stylesheet">
    @elseif(request()->routeIs('reservations*'))
    <link href="{{ asset('css/dashboard/reservations.css') }}" rel="stylesheet">
    @elseif(request()->routeIs('messages*'))
    <link href="{{ asset('css/dashboard/messages.css') }}" rel="stylesheet">
    @endif

    @yield('styles')


</head>

<body>
    @auth('administrator')
    <header class="admin-header">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1>
                <i class="fas fa-tachometer-alt"></i>
                {{ __('messages.admin_panel') }}
            </h1>
        </div>
        <div class="admin-actions d-flex align-items-center gap-3">
            <div class="lang-switcher">
                @if(app()->getLocale() == 'ar')
                    <a href="{{ route('lang.switch', 'fr') }}" class="btn btn-outline-light btn-sm px-3">
                        <i class="fas fa-globe me-1"></i> Français
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}" class="btn btn-outline-light btn-sm px-3">
                        <i class="fas fa-globe me-1"></i> العربية
                    </a>
                @endif
            </div>
            <form action="{{ route('administrator.logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> {{ __('messages.logout') }}
                </button>
            </form>
        </div>
    </header>

    <div class="admin-container">
        <nav class="admin-sidebar">
            <ul class="sidebar-menu">
                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> {{ __('messages.dashboard') }}
                    </a></li>
                <li><a href="{{ route('reservation.requests') }}" class="{{ request()->routeIs('reservation.requests*') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i> {{ __('messages.reservation_requests') }}
                    </a></li>
                <li><a href="{{ route('reservations') }}" class="{{ request()->routeIs('reservations') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i> {{ __('messages.manage_reservations') }}
                    </a></li>
                <li><a href="{{ route('analyses') }}" class="{{ request()->routeIs('analyses') || request()->routeIs('eligibility*') ? 'active' : '' }}">
                        <i class="fas fa-flask"></i> {{ __('messages.manage_analyses') }}
                    </a></li>
                <li><a href="{{ route('messages') }}" class="{{ request()->routeIs('messages') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i> {{ __('messages.send_messages') }}
                    </a></li>
            </ul>
        </nav>

        <main class="admin-main">
            @yield('content')
        </main>
    </div>
    @else
    <div class="login-redirect">
        <p>يجب تسجيل الدخول أولاً</p>
        <a href="/auth" class="btn btn-primary">تسجيل الدخول</a> {{-- Fixed link --}}
    </div>
    @endauth

    {{-- Notifications --}}
    @if(session('success'))
    <div class="notification success show">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="notification error show">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="notification error show">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/dashboard/base.js') }}" type="module"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const adminSidebar = document.querySelector('.admin-sidebar');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            
            if (sidebarToggle && adminSidebar && sidebarBackdrop) {
                const toggleSidebar = () => {
                    adminSidebar.classList.toggle('active');
                    sidebarBackdrop.classList.toggle('active');
                };

                sidebarToggle.addEventListener('click', toggleSidebar);
                sidebarBackdrop.addEventListener('click', toggleSidebar);

                // Close sidebar when clicking a link on mobile
                adminSidebar.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth <= 768) {
                            adminSidebar.classList.remove('active');
                            sidebarBackdrop.classList.remove('active');
                        }
                    });
                });
            }
        });
    </script>

    <!-- Page Specific JS -->
    @if(request()->routeIs('dashboard'))
    <script src="{{ asset('js/dashboard/dashboard.js') }}" type="module"></script>
    @elseif(request()->routeIs('analyses*'))
    <script src="{{ asset('js/dashboard/analyses.js') }}" type="module"></script>
    @elseif(request()->routeIs('reservations*'))
    <script src="{{ asset('js/dashboard/reservations.js') }}" type="module"></script>
    @elseif(request()->routeIs('messages*'))
    <script src="{{ asset('js/dashboard/messages.js') }}" type="module"></script>
    @endif

    @yield('scripts')
</body>

</html>
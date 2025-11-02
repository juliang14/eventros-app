<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Eventos App') }}</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">

    {{-- Navbar superior --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid px-4">

            {{-- Logo --}}
            <a class="navbar-brand fw-bold text-pink" href="{{ route('dashboard') }}">
                <i class="bi bi-calendar-heart"></i> EventosApp
            </a>

            {{-- Botón toggle móvil --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Opciones --}}
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item px-2">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                           href="{{ route('dashboard') }}">
                           <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}" 
                           href="{{ route('events.index') }}">
                           <i class="bi bi-calendar-event"></i> Eventos
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link {{ request()->routeIs('guests.*') ? 'active' : '' }}" 
                           href="{{ route('guests.index') }}">
                           <i class="bi bi-people"></i> Invitados
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link {{ request()->routeIs('gifts.*') ? 'active' : '' }}" 
                           href="{{ route('gifts.index') }}">
                           <i class="bi bi-gift"></i> Regalos
                        </a>
                    </li>
                </ul>

                {{-- Perfil de usuario --}}
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=f48fb1&color=fff"
                                 alt="avatar" class="rounded-circle me-2" width="35" height="35">
                            <span>{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person-circle"></i> Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    {{-- Contenido dinámico --}}
    <main class="py-4">
        @yield('content')
    </main>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

{{-- Estilos custom --}}
<style>
    .text-pink {
        color: #ec407a !important;
    }
    .nav-link.active {
        font-weight: bold;
        color: #ec407a !important;
    }
    .nav-link i {
        margin-right: 6px;
    }
</style>
</html>

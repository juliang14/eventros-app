<x-guest-layout>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="text-center mb-4 fw-bold text-primary">Iniciar sesión</h4>

                        <!-- Session Status -->
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input id="email" type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required autofocus autocomplete="username"
                                    class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input id="password" type="password"
                                    name="password"
                                    required autocomplete="current-password"
                                    class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Remember Me -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Recordarme
                                </label>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                @if (Route::has('password.request'))
                                    <a class="text-decoration-none small text-primary" href="{{ route('password.request') }}">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                @endif

                                <button type="submit" class="btn btn-primary">
                                    Iniciar sesión
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        ¿No tienes cuenta?
                        <a href="{{ route('register') }}" class="text-decoration-none">Regístrate aquí</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

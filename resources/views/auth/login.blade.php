<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="mx-auto" style="width: 10rem;">
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('error'))
            <div class="mb-4 font-medium text-sm text-red-600">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('success') }}
            </div>
        @endif

        <form id="loginForm" action="{{ route('user.login') }}" method="post">
        {{--<form method="POST" action="{{ route('login') }}">--}}
            @csrf

            <div>
                <x-label for="phone" value="{{ __('Telefono') }}" />
                <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Contraseña') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button class="ms-4">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>

    <div id="loadingLogin" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:9999; flex-direction:column; align-items:center; justify-content:center; color:#fff;">
        <div style="width:3rem; height:3rem; border:0.35rem solid rgba(255,255,255,0.35); border-top-color:#fff; border-radius:50%; animation:loadingLoginSpin 0.75s linear infinite; margin-bottom:1rem;"></div>
        <p style="margin:0; font-size:1.1rem;">Iniciando sesión...</p>
        <small style="color:rgba(255,255,255,0.7);">Sincronizando información, por favor espera.</small>
    </div>
    <style>
    @keyframes loadingLoginSpin { to { transform: rotate(360deg); } }
    </style>
    <script>
    document.getElementById('loginForm').addEventListener('submit', function() {
        var btn = this.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = true; }
        document.getElementById('loadingLogin').style.display = 'flex';
    });
    </script>
</x-guest-layout>

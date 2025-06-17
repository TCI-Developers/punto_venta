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

        <form action="{{ route('user.login') }}" method="post">
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
</x-guest-layout>

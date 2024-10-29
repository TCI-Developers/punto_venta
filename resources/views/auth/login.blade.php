<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo" >
            <!-- <x-authentication-card-logo /> -->
            <div class="row" style="display:flex; justify-content: center;">
                <img src="{{asset('img/logo.png')}}" alt="logo" width="25%" >
            </div>
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
               * {{ session('status') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 font-medium text-lg text-green-600">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 font-medium text-sm text-red-600">
                {{session('error')}}
            </div>
        @endif

        <!-- <form method="POST" action="{{ route('login') }}"> -->
        <form method="POST" action="{{ route('user.login') }}">
            @csrf

            @if (session('monto'))
            <div class="mb-4 font-medium text-sm text-red-600">
                {{session('monto')}}
                <div style="    display: flex; flex-direction: row; flex-wrap: nowrap; justify-content: center; align-items: center;">
                    <x-label for="next" value="{{ __('¿Deseas ingnorarlo?   SI ')}}" class="mr-1"/>
                    <x-input id="next" class="block mt-1" type="checkbox" name="next"/> {{$val ?? ''}}
                </div>
            </div>
            @endif

            <div>
                <x-label for="phone" value="{{ __('Teléfono 4521231212 Admin: 4521001010') }}" />
                <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required autofocus autocomplete="username" pattern="[0-9]+" title="Por favor ingrese solo dígitos numéricos." />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Contraseña qwertyuiop Admin: 1234567890') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password"/> <br>
            </div>

            <div class="mt-4">
                <x-label for="start_amount_box" value="{{ __('Monto inicial en caja') }}" />
                <x-input id="start_amount_box" class="block mt-1 w-full" type="number" name="start_amount_box" value="{{old('start_amount_box')}}" placeholder="0" />
            </div>

            <!-- <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Recordarme') }}</span>
                </label>
            </div> -->

            <div class="flex items-center justify-end mt-4" style="flex-direction: column;">
                <x-button class="ms-4">
                    {{ __('Acceder') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>

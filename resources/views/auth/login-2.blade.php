@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', 'Iniciar Sesión')

@section('js')
    @include('components..use.notification_success_error')
@stop

@section('auth_body')
    <form action="{{ route('user.login') }}" method="post">
        @csrf
        <div class="input-group mb-3">
            <label for="phone" class="col-12 text-sm">Teléfono 4521231212 Admin: 4521001010</label>
            <input type="phone" name="phone" id="phone" class="form-control" placeholder="phone" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-phone"></span>
                </div>
            </div>
        </div>

        <div class="input-group mb-3">
            <label for="password" class="col-12 text-sm">Contraseña qwertyuiop Admin: 1234567890</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </div>
        </div>
    </form>
@endsection
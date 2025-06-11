<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{asset('css/dashboards/menu.css')}}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')

    <script> 
        //funcion para cerrar modal
        function modal(branch, users){
            $('.inputs').val('');
            $('.option_user_id').attr('selected', false);
            if(branch !== 'null'){                
                if(users != 'false' && users.length){
                    $.each(users, function(index, user){
                        $.each($('.option_user_id'), function(index, opt){
                            if(opt.value == user.user_id){
                                $(opt).attr('selected', true);
                            }
                        });
                    });
                }

                $('.title').html('Actualizar');
                $('input[name=branch_id]').val(branch.id);
                $('#name').val(branch.name);
                $('#address').val(branch.address);
                $('#branchs').modal('show');
            }else{
                $('.title').html('Crear');
                $('#branchs').modal('hide');
            }
        }       
    </script>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
        <div class="card {{$status == 0 ? 'card-secondary':'card-primary'}}">
            <div class="form-group card-header with-border text-center">
                <h2>Sucursales {{$status == 0 ? 'Inhabilitadas':''}}</h2> 
            </div>

            <div class="col-12 mt-2">
                @if(Auth::User()->hasRole('root'))
                <div class="card-header">
                        <a href="{{route('branchs.create')}}" class="btn btn-primary"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Nueva sucursal">
                            <i class="fa fa-plus text-light"></i>
                        </a>
                        <a href="{{route('branchs.index', $status == 0 ? 1:0)}}" class="btn {{$status == 0 ? 'btn-success':'btn-secondary'}} float-right" data-bs-toggle="tooltip" data-bs-placement="top" title="Sucursales Inhabilitadas">
                            <i class="fa fa-folder text-light"></i>
                        </a>
                </div>
                @endif
                
            </div>

            <div class="card-body">
                <div class="app-main"> <!-- MAIN (Center website) -->
                    <div class="row app-row" id="body_formats">  <!--APP ROW 1-->
                        @forelse($branchs as $item)
                            @if($user->hasAnyRole(['root', 'admin']))
                                @include('Admin.branchs._sucursal')
                            @elseif($user->hasBranch($item->id))
                                @include('Admin.branchs._sucursal')
                            @endif
                        @empty
                        @endforelse
                    </div> <!--APP ROW 1-->
                </div><!-- END MAIN -->
            </div>
        </div>
    </main>   
</body>
</html>
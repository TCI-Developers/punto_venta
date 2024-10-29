@extends('adminlte::page')

@section('title', 'Categorias')

@section('js')
    @include('components..use.notification_success_error')

    <script>
        function edit(category){
            $('#formCategory').attr('action',$('#formCategory').attr('edit'));
            $('#modal_create').modal('show');
            $('#title').html('Actualizar');
            $('#btnAddEdit').html('Actualizar');
            $('input[name=id]').val(category.id);
            $('#name').val(category.name);
            $('#description').val(category.description);
        }

        function btnAdd(){
            $('#modal_create .inputModal').val('');
            $('#formCategory').attr('action',$('#formCategory').attr('store'));
            $('#title').html('Agregar');
            $('#btnAddEdit').html('Agregar');
        }
    </script>
@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Categorias {{$status == 0 ? 'Eliminadas':''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <div class="form-group">
                @if($status == 1)
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_create" onClick="btnAdd()"><i class="fa fa-plus"></i> Crear Categoria</button>
                @endif
                @if($status == 1)
                <a href="{{route('category.indexDead')}}" class="btn btn-light float-right"><i class="fa fa-file"></i> Categorias Eliminadas</a>
                @else
                <a href="{{route('category.index')}}" class="btn btn-info float-right"><i class="fa fa-file"></i> Categorias</a>
                @endif
            </div>
            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $item)
                    <tr class="text-center">
                        <td>{{$index+1}}</td>
                        <td>{{$item->name}}</td>
                        <td>{{$item->description}}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" onClick="edit({{$item->getCategory($item->id)}})"><i class="fa fa-edit"></i></button>
                            @if($status == 1)
                            <a href="{{route('category.destroy', $item->id)}}" class="btn btn-light btn-sm"><i class="fa fa-trash"></i></a>
                            @else
                            <a href="{{route('category.enable', $item->id)}}" class="btn btn-primary btn-sm"><i class="fa fa-check"></i></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="table-warning text-center">Sin categorias</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
  </div>

  @include('admin.category._modal');
@stop
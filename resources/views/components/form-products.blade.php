@section('css')
    <link rel="stylesheet" href="/css/products/products-form.css">
@stop

<div class="container">    
        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="inputEmail4">Descripción</label>
                <textarea class="form-control" id="exampleFormControlTextarea1" name="description" rows="2" required></textarea>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="inputPassword4">Linea</label>
                <input type="text" class="form-control" id="inputPassword4" name="linea" required>
            </div>
            <div class="form-group col-md-4">
                <label for="inputPassword4">Marca</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
            <div class="form-group col-md-4">
                <label for="inputPassword4">Fabricante</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="inputPassword4">Linea</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
            <div class="form-group col-md-3">
                <label for="inputPassword4">Marca</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
            <div class="form-group col-md-3">
                <label for="inputPassword4">Fabricante</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
            <div class="form-group col-md-3">
                <label for="inputPassword4">Fabricante</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="inputPassword4">Costo</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
            <div class="form-group col-md-3">
                <label for="inputPassword4">Costo Anterior</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
            <div class="form-group col-md-2">
                <label for="inputPassword4">Peso</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
            <div class="form-group col-md-2">
                <label for="inputPassword4">Existencia</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
            <div class="form-group col-md-2">
                <label for="inputPassword4">Unidad</label>
                <input type="text" class="form-control" id="inputPassword4">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="listPrice">Código de precio</label>
                        <select class="custom-select" id="listPrice">
                            <option disabled selected>Elige una opción</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="listPrice">Utilidad</label>
                        <select class="custom-select" id="listPrice">
                            <option disabled selected>Elige una opción</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="listPrice">Código de precio 2</label>
                        <select class="custom-select" id="listPrice">
                            <option disabled selected>Elige una opción</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="listPrice">Cantidad menor a</label>
                        <select class="custom-select" id="listPrice">
                            <option disabled selected>Elige una opción</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6 ml-auto">
                <label for="txtInformation">Información</label>
                <textarea class="form-control" id="txtInformation" rows="4"></textarea>
            </div> 
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="listPrice">Código de precio 3</label>
                        <select class="custom-select" id="listPrice">
                            <option disabled selected>Elige una opción</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="listPrice">Cantidad mayor que</label>
                        <select class="custom-select" id="listPrice">
                            <option disabled selected>Elige una opción</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="listPrice">Código de precio 4</label>
                        <select class="custom-select" id="listPrice">
                            <option disabled selected>Elige una opción</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="listPrice">Venta a granel</label>
                        <select class="custom-select" id="listPrice">
                            <option disabled selected>Elige una opción</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6">
               <x-input-image title="Imagen" name="product-image"></x-input-image>
            </div> 
        </div>
</div>
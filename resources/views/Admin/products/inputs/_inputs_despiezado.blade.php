<input type="hidden" name="despiece" value="{{$type}}">
<label for="price_general" class="col-lg-4 col-md-4 col-sm-12 {{$type == 'only_edit' ? 'despiezado':''}}">Precio Total Despiece* <br>
    <input type="number" name="price_general" id="price_general" class="form-control inputModal" step="0.01" value="{{$product->precio_despiece}}" readonly>
</label>
<label for="price" class="col-lg-4 col-md-4 col-sm-12 {{$type == 'only_edit' ? 'despiezado':''}}">Precio Unitario* <br>
    <input type="number" name="price" id="price" class="form-control inputModal price" step="0.01" placeholder="0.00" readonly>
</label>
<label for="cantidad_despiezado" class="col-lg-4 col-md-4 col-sm-12 {{$type == 'only_edit' ? 'despiezado':''}}">Cantidad despiezado* <br>
    <input type="number" name="cantidad_despiezado" id="cantidad_despiezado" class="form-control inputModal" step="0.01" onchange="priceDespiece()" placeholder="0.00">
</label>
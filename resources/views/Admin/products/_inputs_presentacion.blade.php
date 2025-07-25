<label for="price" class="col-lg-4 col-md-4 col-sm-12 presentation">Precio Unitario* <br>
    <input type="number" name="price" id="price" class="form-control inputModal price" step="0.01" value="{{$product->precio}}">
</label>
<label for="precio_mayoreo" class="col-lg-4 col-md-4 col-sm-12 presentation">Precio Mayoreo* <br>
    <input type="number" name="precio_mayoreo" id="precio_mayoreo" class="form-control inputModal" step="0.01" min="0" value="{{$product->precio_mayoreo}}" onchange="precioMayoreo(this)">
</label>
<label for="cantidad_mayoreo" class="col-lg-4 col-md-4 col-sm-12 presentation">Cantidad minima mayoreo
    <input type="number" class="form-control pass" name="cantidad_mayoreo" id="cantidad_mayoreo" step="0.01" value="0"
     {{$product->precio_mayoreo > 0 ? '':'readonly'}} >
</label>
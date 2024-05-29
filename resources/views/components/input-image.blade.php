<label for="image-product">{{$title ?? 'Imagen'}}</label>
<div class="input-container d-flex align-items-center justify-content-center">
    <div class="">
        <p class="text-center"><i class="fas fa-cloud-upload-alt"></i></p> 
        <input type="file" accept="image/*" name="{{$name ?? 'image'}}">
    </div>
</div>
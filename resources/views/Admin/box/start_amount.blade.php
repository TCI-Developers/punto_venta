<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Monto Inicial de la Caja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="text-center mb-4">Ingresar Monto Inicial de la Caja</h4>

                    @if(session('monto'))
                    <div class="mb-4 font-medium text-sm text-red-600">
                        {{session('monto')}}
                        <div style="    display: flex; flex-direction: row; flex-wrap: nowrap; justify-content: center; align-items: center;">
                            <label for="next" class="mr-1">¿Deseas ingnorarlo?   SI</label>
                            <input id="next" class="block mt-1" type="checkbox" name="next"/> {{$val ?? ''}}
                        </div>
                    </div>
                    @endif

                    <!-- Mensajes de error -->
                    <div id="error-message" class="alert alert-danger d-none" role="alert">
                        Por favor, ingrese un monto válido.
                    </div>

                    <form id="amountForm" action="{{route('box.storeStarAmountBox')}}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="start_amount_box" class="form-label">Monto Inicial</label>
                            <input type="number" step="0.01" id="start_amount_box" name="start_amount_box" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("amountForm").addEventListener("submit", function(event) {
        var amount = document.getElementById("start_amount_box").value;
        if (amount === "" || isNaN(amount) || amount < 0) {
            event.preventDefault();
            document.getElementById("error-message").classList.remove("d-none");
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

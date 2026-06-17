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

                    @if($ultimoCierre)
                    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="fa fa-info-circle fa-lg"></i>
                        <div>
                            El último turno cerrado por
                            <strong>{{ $ultimoCierreUser->name ?? 'Desconocido' }}</strong>
                            dejó <strong>${{ number_format($ultimoCierre->monto_dejado_caja, 2) }}</strong> en caja
                            <small class="text-muted d-block">{{ \Carbon\Carbon::parse($ultimoCierre->end_date)->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning mb-4" role="alert">
                        No hay cierres de caja previos. Es el primer turno del día.
                    </div>
                    @endif

                    @if(session('monto'))
                    <div class="alert alert-danger mb-3">
                        {{ session('monto') }}
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <label for="next" class="mb-0">¿Ignorar diferencia y continuar?</label>
                            <input id="next" type="checkbox" name="next" form="amountForm"/>
                        </div>
                    </div>
                    @endif

                    @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div id="error-message" class="alert alert-danger d-none" role="alert">
                        Por favor, ingrese un monto válido.
                    </div>

                    <form id="amountForm" action="{{ route('box.storeStarAmountBox') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="start_amount_box" class="form-label">Monto Inicial</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" id="start_amount_box"
                                       name="start_amount_box" class="form-control"
                                       placeholder="{{ $ultimoCierre ? number_format($ultimoCierre->monto_dejado_caja, 2) : '0.00' }}"
                                       required>
                            </div>
                            @if($ultimoCierre)
                            <small class="text-muted">Se esperan ${{ number_format($ultimoCierre->monto_dejado_caja, 2) }} según el último cierre.</small>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Iniciar Turno</button>
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

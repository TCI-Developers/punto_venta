        @if(session('success') || session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const description = {!! json_encode(session('success') ?? session('error')) !!};
                    const icon = '{{ session('success') ? 'success' : 'error' }}';

                    Swal.fire({
                        title: description,
                        position: 'center',
                        icon: icon,
                        showConfirmButton: false,
                        timer: 2000
                    });
                });
            </script>
        @endif

     @if(session('info'))
  	    <script>
            document.addEventListener('DOMContentLoaded', function () {
                const description = {!! json_encode(session('info')) !!};
                Swal.fire({
                    title: description,
                    position: 'center',
                    icon: 'info',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        </script>
    @endif

    @if ($errors->any())
    <script>
       document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'info',
                title: 'Validación de campos',
                html: `
                    <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                `
            });
        });
    </script>
    @endif
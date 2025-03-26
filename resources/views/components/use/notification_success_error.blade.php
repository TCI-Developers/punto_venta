    @if(session('success') || session('error'))
  	    <script>
            let description = '{{ session('success') ?? session('error')}}';
            let icon = '{{ session('success') ? "success":"error"}}';
            swal.fire({
                title: description,
                position: 'center',
                icon: icon,
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif

    @if ($errors->any())
    <script>
        $(document).ready(function() {
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
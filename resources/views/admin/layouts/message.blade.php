@if (session('success') || session('error') || $errors->any())
<script>
    $(document).ready(function () {
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: "error",
                    title: "{{ $error }}"
                });
            @endforeach
        @endif

        @if (session('success'))
            Swal.fire({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                icon: "success",
                title: "{{ session('success') }}"
            });
        @endif

        @if (session('error'))
            Swal.fire({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                icon: "error",
                title: "{{ session('error') }}"
            });
        @endif
    });
</script>
@endif

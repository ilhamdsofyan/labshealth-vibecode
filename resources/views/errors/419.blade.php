<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesi Berakhir</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Sesi Berakhir',
            text: @json($message),
            confirmButtonText: 'Oke',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                confirmButton: 'swal2-confirm btn btn-primary'
            },
            buttonsStyling: false
        }).then(function () {
            window.location.replace(@json($reloadUrl));
        });
    </script>
</body>
</html>

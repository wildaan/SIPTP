<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIPTP')</title>
    <meta name="description" content="SIPTP - Sistem Informasi Pengajuan Transaksi Pengeluaran">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="icon" type="image/png" href="{{ asset('template/mazer/assets/static/images/logo/favicon.png') }}">
    
    <!-- Local Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/nunito/nunito.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    
    <!-- Custom CSS for Guest Layout -->
    @stack('styles')
</head>

<body>
    <div id="app">
        @yield('content')
    </div>

    <!-- Local Vendor JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <!-- Global AJAX Helper -->
    <script src="{{ asset('assets/js/ajax-helper.js') }}"></script>

    <!-- Custom JS for Guest Layout -->
    @stack('scripts')
</body>

</html>

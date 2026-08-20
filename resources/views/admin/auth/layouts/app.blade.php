<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content="theme_ocean">
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title> || Login </title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ config('custom.public_path').'/adminAssets/assets/images/favicon.ico'}}">
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="{{ config('custom.public_path').'/adminAssets/assets/css/bootstrap.min.css'}}">
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="{{ config('custom.public_path').'/adminAssets/assets/vendors/css/vendors.min.css'}}">
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ config('custom.public_path').'/adminAssets/assets/css/theme.min.css'}}">
    <!--! END: Custom CSS-->
    <!--! BEGIN: Brand Colour Override — MUST stay after theme.min.css to win the cascade -->
    <link rel="stylesheet" type="text/css" href="{{ config('custom.public_path').'/adminAssets/assets/css/custom-theme.css'}}">
    <!--! END: Brand Colour Override-->

</head>

<body>
    @yield('content')
    <!-- footer links -->
    <!--! BEGIN: Vendors JS !-->
    <script src="{{ config('custom.public_path').'/adminAssets/assets/vendors/js/vendors.min.js'}}"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    <script src="{{ config('custom.public_path').'/adminAssets/assets/js/common-init.min.js'}}"></script>
    <!--! END: Apps Init !-->
    <!--! BEGIN: Theme Customizer  !-->
    <script src="{{ config('custom.public_path').'/adminAssets/assets/js/theme-customizer-init.min.js'}}"></script>
    <!--! END: Theme Customizer !-->
    @stack('script')
    @include('admin.layouts.message')
</body>
<script src="{{ config('custom.public_path'.'/dataTables.bs5.min.js') }}"></script>
</html>

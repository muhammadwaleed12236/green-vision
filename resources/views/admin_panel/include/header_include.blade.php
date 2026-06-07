<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords">
    <title>Green Vision Dashboard</title>

       <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('assets/img/favicon.png') }}">

    <!-- Bootstrap & Animations -->
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/dataTables.bootstrap4.min.css') }}">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Custom Style -->
    <link rel="stylesheet" href="{{ url('assets/css/style.css') }}">


</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"> </div>
    </div>

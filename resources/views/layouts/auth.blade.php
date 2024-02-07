<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'POS') }}</title>

    @include('layouts.partials.css')

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Cairo', sans-serif;
            font-weight: bold;
        }

        .dropdown-menu>li>a {
            font-weight: bold;
        }

        .checkbox label,
        .checkbox-inline,
        .radio label,
        .radio-inline {
            font-weight: bold;

        }

        .fa-folder:before {
            color: #ffd400 !important;
            /* background-color:#ffd400; */
        }

        .fa-arrow-alt-circle-right:before {
            color: #3936f5;
        }


        body {
            background-color: #243949;
          
           
        }

        h1 {
            color: #fff;
        }
    </style>
</head>

<body class="hold-transition">
    @if (session('status'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
            data-msg="{{ session('status.msg') }}">
    @endif

    @if (!isset($no_header))
        @include('layouts.partials.header-auth')
    @endif

    @yield('content')

    @include('layouts.partials.javascripts')
    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>
    @yield('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2_register').select2();

            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>
</body>

</html>

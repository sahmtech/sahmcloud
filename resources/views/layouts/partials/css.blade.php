<link rel="stylesheet" href="{{ asset('css/vendor.css?v=' . $asset_v) }}">

@if (in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')))
    <link rel="stylesheet" href="{{ asset('css/rtl.css?v=' . $asset_v) }}">
@endif

@yield('css')

<!-- app css -->
<link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200&display=swap" rel="stylesheet">


@if (isset($pos_layout) && $pos_layout)
    <style type="text/css">
        .content {
            padding-bottom: 0px !important;
        }
    </style>
@endif

<style type="text/css">
    /*
 * Pattern lock css
 * Pattern direction
 * http://ignitersworld.com/lab/patternLock.html
 */
    * {
        font-family: 'Cairo', sans-serif;
        font-weight: bold;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .h1,
    .h2,
    .h3,
    .h4,
    .h5,
    .h6 {
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

    .dataTables_filter .input-sm {
        border-radius: 6px !important;
        width: 105% !important;
    }

    .dataTables_length .input-sm {
        padding: 1px;
        border-radius: 5px;
        margin: 0px 7px;
    }

    /* .dt-buttons.btn-group .input-sm {
        background: none;
        border: 0px;
        font-size: 27px;
    } */

    .info-box-new-style {
        padding: 12px !important;
    }

    .patt-wrap {
        z-index: 10;
    }

    .patt-circ.hovered {
        background-color: #cde2f2;
        border: none;
    }

    .fa-folder:before {
        color: #ffd400 !important;
        /* background-color:#ffd400; */
    }

    /* .treeview-menu>li.active {
        background-image: linear-gradient(to right, #2b80ec, #1d1f33);
        color: whitesmoke;
        border-radius: 7px;
        padding: 4px;
        margin-bottom: 2px;
    } */

    /* .skin-blue-light .content-wrapper .content-header-custom {
        background-color: transparent;
        background-image: none;
    } */

    /* .skin-blue-light .content-wrapper .content-header-custom h1 {
        color: rgb(33 0 159) !important;
    } */

    /* .skin-blue-light .main-header .logo {
        background-color: #1a153c;
        color: #ebebeb;
    } */


    /* .skin-blue-light .main-header .logo:hover {
        background: #b5c5d9f0;
        color: #1a153c;
    } */

    /* .skin-blue-light .main-header .navbar {
        background-color: #1a153c;
        background-image: none;

    } */

    /* .skin-blue-light .main-header .navbar .sidebar-toggle {
        color: #a10000;
    } */

    /* .skin-blue-light .main-header .navbar .nav>li>a {
        color: #f0f0f0;
    } */

    /* .treeview-menu>li:hover {
        background-image: linear-gradient(to right, #2b80ec, #1d1f33);
        color: whitesmoke;
        border-left: 2px solid #1d1f33;
        border-radius: 0px 7px 7px 0px;
        padding: 7px;
        margin-bottom: 2px;

    } */

    /* .skin-blue-light .sidebar-menu .treeview-menu>li>a:hover {
        color: whitesmoke;
    }

    .skin-blue-light .sidebar-menu .treeview-menu>li.active>a {
        color: whitesmoke;
    } */
    .box .box-body {
        color: black;
    }

    .sidebar-menu>li>a>.fa {
        color: #073158;
    }

    .fa-arrow-alt-circle-right:before {
        color: #3936f5;
    }

    .btn.btn-flat {

        border-width: 2px;
        border-radius: 4px;
    }

    .pos_controll .btn {
        font-size: larger;
        width: 50px;
        height: 40px;
        border-radius: 5px;
    }

    .patt-circ.hovered .patt-dots {
        display: none;
    }

    .patt-circ.dir {
        background-image: url("{{ asset('/img/pattern-directionicon-arrow.png') }}");
        background-position: center;
        background-repeat: no-repeat;
    }

    .patt-circ.e {
        -webkit-transform: rotate(0);
        transform: rotate(0);
    }

    .info-box-new-style {
        padding: 12px !important;
        border: 1px solid #7e6f7e44;
    }


    /* .skin-blue-light .main-header li.user-header {
        background-color: #1e2b3cfc;
    } */

    .patt-circ.s-e {
        -webkit-transform: rotate(45deg);
        transform: rotate(45deg);
    }

    .patt-circ.s {
        -webkit-transform: rotate(90deg);
        transform: rotate(90deg);
    }

    .patt-circ.s-w {
        -webkit-transform: rotate(135deg);
        transform: rotate(135deg);
    }

    .patt-circ.w {
        -webkit-transform: rotate(180deg);
        transform: rotate(180deg);
    }

    .patt-circ.n-w {
        -webkit-transform: rotate(225deg);
        transform: rotate(225deg);
    }

    .patt-circ.n {
        -webkit-transform: rotate(270deg);
        transform: rotate(270deg);
    }

    .patt-circ.n-e {
        -webkit-transform: rotate(315deg);
        transform: rotate(315deg);
    }

    .login-col-right-logo-title {
        height: 60vh;
        border-radius: 22px;
        display: flex;
        justify-content: center;
    }

    .login-col-left-logo-title-ar {
        height: 60vh;
        background-image: linear-gradient(#a4acd1, #243949);
        color: aliceblue;
        border-radius: 22px 0px 0px 22px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .login-col-left-logo-title-en {
        height: 60vh;
        background-image: linear-gradient(#a4acd1, #243949);
        color: aliceblue;
        border-radius: 0px 22px 22px 0px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
</style>
@if (!empty($__system_settings['additional_css']))
    {!! $__system_settings['additional_css'] !!}
@endif

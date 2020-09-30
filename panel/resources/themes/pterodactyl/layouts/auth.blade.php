{{-- Pterodactyl - Panel --}}
{{-- Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com> --}}

{{-- This software is licensed under the terms of the MIT license. --}}
{{-- https://opensource.org/licenses/MIT --}}
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ config('app.name', 'Pterodactyl') }} - @yield('title')</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="/favicons/manifest.json">
        <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
        <link rel="shortcut icon" href="/favicons/favicon.ico">
        <meta name="msapplication-config" content="/favicons/browserconfig.xml">
        <meta name="theme-color" content="#0e4688">

        @section('scripts')
            {!! Theme::css('vendor/bootstrap/bootstrap.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/admin.min.css?t={cache-version}') !!}
            {!! Theme::css('css/pterodactyl.css?t={cache-version}') !!}
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">

            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->
        @show
    </head>
    <body id="particles-js" class="hold-transition login-page">
        <button id="order-btn">Szervert szeretnék!</button>
        <div id="order-panel" class="conatiner">
            <div class="row">
                <button id="order-close-btn">Vissza</button>
            </div>
            <div class="row justify-content-md-center">
                <div class="col-lg-8" style="overflow: auto;">
                    <img class="gamelogo" src="/assets/png/csgo.png" id="csgo">
                    <img class="gamelogo" src="/assets/png/gtav.png" id="gtav">
                    <img class="gamelogo" src="/assets/png/rust.png" id="rust">
                    <img class="gamelogo" src="/assets/png/minecraft.png" id="minecraft">
                    <img class="gamelogo" src="/assets/png/ark.png" id="ark">
                    <img class="gamelogo" src="/assets/png/conanexiles.png" id="conanexiles">
                </div>
                <div class="col col-lg-4 order-form">
                    rendelés form
                </div>
            </div>
        </div>
        <div class="container">
            <div id="login-position-elements">
                <div class="login-logo">
                    {{ config('app.name', 'Pterodactyl') }}
                </div>
                @yield('content')
                <p class="small login-copyright text-center">
                    Minden jog fenntartva. © 2003-{{ date('Y') }} <a href="https://atw.hu/" target="_blank">ATW Internet Kft.</a><br />
                </p>
            </div>
        </div>
        

        {!! Theme::js('vendor/jquery/jquery.min.js?t={cache-version}') !!}
        {!! Theme::js('vendor/bootstrap/bootstrap.min.js?t={cache-version}') !!}
        {!! Theme::js('js/autocomplete.js?t={cache-version}') !!}
        {!! Theme::js('vendor/particlesjs/particles.min.js?t={cache-version}') !!}
        <script type="text/javascript">
            /* particlesJS.load(@dom-id, @path-json, @callback (optional)); */
            $(function () {
                particlesJS.load('particles-js', '{!! Theme::url('vendor/particlesjs/particles.json?t={cache-version}') !!}', function() {});
            });
            $(document).ready(function(){
                $("#order-btn").click(function(){
                    $(".container").fadeToggle("slow");
                    $("#order-btn").fadeToggle("slow",function(){
                        $("#order-panel").slideToggle("slow");
                    });
                });
                $("#order-close-btn").click(function(){
                    $("#order-panel").slideToggle("slow", function() {
                        $("#order-btn").fadeToggle("slow");
                        $(".container").fadeToggle("slow");
                    });
                });
            });
        </script>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>@lang('login.title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{asset('css/font-awesome.min.css')}}" rel="stylesheet" type="text/css" media="all">
    <link href="{{asset('css/style.css')   }}" rel="stylesheet" type="text/css" media="all"/>
    <link href="//fonts.googleapis.com/css?family=Raleway:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    @php
        $locale = app()->getLocale();
    @endphp
    <script>
        var LanguneThisJs = '@php  echo $locale;@endphp';
    </script>
</head>
<body>
<header>
    <div class="container">
        <a href="/" class="header-logo-link">
            <img src="/images/logo_bb.png" class="header-logo-img">
            <div class="header-text">BilliardCRM</div>
        </a>
        <div class="language-switch-box">
            <a class="lang-switch-item @if($locale == 'ua') active @endif" href="/setLng?lng=ua">
                <img class="lang-switch-item-img active" src="/images/ua.svg">
                <span>@lang('login.ua')</span>
            </a>
            <a class="lang-switch-item @if($locale == 'ru') active @endif" href="/setLng?lng=ru">
                <img class="lang-switch-item-img" src="/images/rus.svg">
                <span>@lang('login.ru')</span>
            </a>
        </div>
    </div>
</header>
<div class="signupform" id="app">
    <div class="container">
        <div class="agile_info">
            <div class="bcl_form">
                <div class="left_grid_info">
                    <h1>@lang('login.h1')</h1>
                    <p>@lang('login.desc')</p>
                    <img src="/images/image.jpg" alt="" />
                </div>
            </div>
            <div class="bc_info">
                <h2>@lang('login.h2')</h2>
                <p>@lang('login.h2_desc')</p>
                <login-form></login-form>
            </div>
        </div>
    </div>
</div>
<div class="footer">
    <div class="footer__copy">&copy; 2019-@php  echo date('Y'); @endphp <a href="//bethoven.digital/">Bethoven Digital</a>.</div>
    <div class="footer__all">@lang('login.footer')</div>
</div>
</body>
</html>

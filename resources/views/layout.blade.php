<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Default page')</title>
        <link href="css/app.css" rel="stylesheet">
    </head>
    <body class="h-100 d-flex flex-column">
        <nav class="navbar border-bottom">
            <div class="container">
                <a class="navbar-brand" href="/">The test project on Laravel</a>
            </div>
        </nav>
        @yield('content')
        <footer class="d-flex justify-content-center mt-auto border-top">
            <p>Make with love</p>
        </footer>
        @section('scripts')
            <script defer type="text/javascript" src="js/app.js"></script>
        @show
    </body>
</html>
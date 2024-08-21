<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <style>
        @import url("https://fonts.googleapis.com/css?family=DM+Serif+Text:400|Mulish:900,600,400,700|Manrope:700");
        @import url("https://fonts.googleapis.com/css?family=Inter:900,600,400,700");

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p {
            margin: 0 !important;

        }

        .master-container {
            margin-top: 70px;

        }

        @media only screen and (min-width:992px) {
            .master-container {
                margin-top: 100px;

            }
        }
    </style>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    {{-- GTM --}}
</head>

<body>

    @stack('before-content')


    <div class="master-container">
        {{-- PAGE SPECIFIC HTML --}}
        @yield('content')
    </div>

    @stack('after-content')

    {{-- PAGE SPECIFIC MODALS IF NEEDED --}}
    @stack('partials.modals')

    {{-- CRITICAL/INTERNAL JAVASCRIPT SNIPPETS --}}
    @stack('body-scripts')

    {{-- EXTERNAL JAVASCRIPT FILES TO BE LOADED FROM CDN/OWN SERVER --}}
    @stack('body-external-scripts')

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

</body>

</html>
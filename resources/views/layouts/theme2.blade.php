<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>{{ env('APP_NAME', 'ICMRIA 2027 | Daffodil International University') }}</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta property="og:url"               content="{{ config('app.url') }}" />
  <meta property="og:type"              content="website" />
  <meta property="og:title"             content="{{ env('APP_NAME', 'ICMRIA 2027') }}" />
  <meta property="og:description"       content="International Conference on Multidisciplinary Research, Innovation and Applications 2027, Daffodil International University" />
  <meta property="og:image"             content="{{ asset('img/icmria27-logo.png') }}" />
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/icmria27-favicon-32.png') }}"/>
  <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('img/icmria27-favicon.png') }}"/>
  <link rel="apple-touch-icon" href="{{ asset('img/icmria27-apple-touch-icon.png') }}"/>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,700,700i|Raleway:300,400,500,700,800" rel="stylesheet">

  <!-- Bootstrap CSS File -->
  <link href="{{ asset('lib/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

  <!-- Libraries CSS Files -->
  <link href="{{ asset('lib/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
  <link href="{{ asset('lib/animate/animate.min.css') }}" rel="stylesheet">
  <link href="{{ asset('lib/venobox/venobox.css') }}" rel="stylesheet">
  <link href="{{ asset('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">

  <!-- Main Stylesheet File -->
  <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>

<body>
  @include('theme2.partials.header')

  @yield('content')

  @include('theme2.partials.footer')

  <a href="#" class="back-to-top"><i class="fa fa-angle-up"></i></a>

  <!-- JavaScript Libraries -->
  <script src="{{ asset('lib/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('lib/jquery/jquery-migrate.min.js') }}"></script>
  <script src="{{ asset('lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('lib/easing/easing.min.js') }}"></script>
  <script src="{{ asset('lib/superfish/hoverIntent.js') }}"></script>
  <script src="{{ asset('lib/superfish/superfish.min.js') }}"></script>
  <script src="{{ asset('lib/wow/wow.min.js') }}"></script>
  <script src="{{ asset('lib/venobox/venobox.min.js') }}"></script>
  <script src="{{ asset('lib/owlcarousel/owl.carousel.min.js') }}"></script>

  <!-- Contact Form JavaScript File -->
  <script src="{{ asset('js/contactform.js') }}"></script>

  <!-- Template Main Javascript File -->
  <script src="{{ asset('js/app.js') }}"></script>
  @yield('scripts')
</body>

</html>

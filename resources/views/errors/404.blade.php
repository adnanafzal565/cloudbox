<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>{{ config("config.app_name") }}</title>
      
      <!-- Favicon -->
      <link rel="shortcut icon" href="{{ asset('/cloud-box/images/favicon.ico') }}" />
      
      <link rel="stylesheet" href="{{ asset('/cloud-box/css/backend-plugin.min.css') }}" />
      <link rel="stylesheet" href="{{ asset('/cloud-box/css/backend.css?v=1.0.0') }}" />
      
      <link rel="stylesheet" href="{{ asset('/cloud-box/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}" />
      <link rel="stylesheet" href="{{ asset('/cloud-box/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css') }}" />
      <link rel="stylesheet" href="{{ asset('/cloud-box/vendor/remixicon/fonts/remixicon.css') }}" />
      
    </head>
  <body class=" ">
    <!-- loader Start -->
    <div id="loading">
          <div id="loading-center">
          </div>
    </div>
    <!-- loader END -->
    
      <div class="wrapper">
        <div class="container">
         <div class="row no-gutters height-self-center">
            <div class="col-sm-12 text-center align-self-center">
               <div class="iq-error position-relative">
                     <img src="{{ asset('/cloud-box/images/error/404.png') }}" class="img-fluid iq-error-img" alt="">
                     <h2 class="mb-0 mt-4">Oops! This Page is Not Found.</h2>
                     <p>The requested page dose not exist.</p>
                     <a class="btn btn-primary d-inline-flex align-items-center mt-3" href="{{ url('/') }}"><i class="ri-home-4-line"></i>Back to Home</a>
               </div>
            </div>
         </div>
        </div>
      </div>
    
    <!-- Backend Bundle JavaScript -->
    <script src="{{ asset('/cloud-box/js/backend-bundle.min.js') }}"></script>
    
    <!-- Chart Custom JavaScript -->
    <script src="{{ asset('/cloud-box/js/customizer.js') }}"></script>
    <!-- app JavaScript -->
    <script src="{{ asset('/cloud-box/js/app.js') }}"></script>
  </body>
</html>
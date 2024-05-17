<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>{{ config("config.app_name") }} | Login</title>
      
      <!-- Favicon -->
      <link rel="shortcut icon" href="{{ asset('/cloud-box/images/favicon.ico') }}" />
      
      <link rel="stylesheet" href="{{ asset('/cloud-box/css/backend-plugin.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/cloud-box/css/backend.css') }}?v=1.0.0">
      
      <link rel="stylesheet" href="{{ asset('/cloud-box/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/cloud-box/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/cloud-box/vendor/remixicon/fonts/remixicon.css') }}">
    
        <script src="{{ asset('/js/react.development.js') }}"></script>
        <script src="{{ asset('/js/react-dom.development.js') }}"></script>
        <script src="{{ asset('/js/babel.min.js') }}"></script>
        <script src="{{ asset('/js/axios.min.js') }}"></script>
        <script src="{{ asset('/js/sweetalert2@11.js') }}"></script>
        <script src="{{ asset('/js/fontawesome.js') }}"></script>
    </head>

  <body class=" ">

        <input type="hidden" id="baseUrl" value="{{ url('/') }}" />
        <input type="hidden" id="appName" value="{{ config('config.app_name') }}" />

        <script>
          const baseUrl = document.getElementById("baseUrl").value
          const appName = document.getElementById("appName").value
        </script>
          
        <script src="{{ asset('/js/script.js?v=' . time()) }}"></script>
    
      <div class="wrapper">
          <section class="login-content">
             <div class="container h-100">
                <div class="row justify-content-center align-items-center height-self-center">
                   <div class="col-md-5 col-sm-12 col-12 align-self-center">
                      <div class="sign-user_card">
                            <img src="{{ asset('/cloud-box/images/logo.png') }}" class="img-fluid rounded-normal light-logo logo" alt="logo" />
                         <h3 class="mb-3">Sign In</h3>
                         <p>Login to stay connected.</p>
                         <form onsubmit="doLogin()">
                            <div class="row">
                               <div class="col-lg-12">
                                  <div class="floating-label form-group">
                                     <input class="floating-input form-control" name="email" type="email" required />
                                     <label>Email</label>
                                  </div>
                               </div>
                               <div class="col-lg-12">
                                  <div class="floating-label form-group">
                                     <input class="floating-input form-control" name="password" type="password" required />
                                     <label>Password</label>
                                  </div>
                               </div>
                               <div class="col-lg-12">
                                    <p class="text-center">
                                        <a href="{{ url('/forgot-password') }}" class="text-primary">Forgot Password?</a>
                                    </p>
                               </div>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Sign In</button>
                            <p class="mt-3">
                               Create an Account <a href="{{ url('/register') }}" class="text-primary">Sign Up</a>
                            </p>
                         </form>
                      </div>
                   </div>
                </div>
             </div>
          </section>
      </div>

        <script>
            async function doLogin() {
                event.preventDefault()
                const form = event.target

                try {
                    const formData = new FormData(form)
                    form.submit.setAttribute("disabled", "disabled")

                    const response = await axios.post(
                        baseUrl + "/api/login",
                        formData
                    )

                    if (response.data.status == "success") {
                        const accessToken = response.data.access_token
                        localStorage.setItem(accessTokenKey, accessToken)

                        const urlSearchParams = new URLSearchParams(window.location.search)
                        const redirect = urlSearchParams.get("redirect") || ""
                        if (redirect == "") {
                            window.location.href = baseUrl
                        } else {
                            window.location.href = redirect
                        }
                    } else {
                        swal.fire("Error", response.data.message, "error")
                    }
                } catch (exp) {
                    swal.fire("Error", exp.message, "error")
                } finally {
                    form.submit.removeAttribute("disabled")
                }
            }
        </script>
    
    <!-- Backend Bundle JavaScript -->
    <script src="{{ asset('/cloud-box/js/backend-bundle.min.js') }}"></script>
    
    <!-- Chart Custom JavaScript -->
    <script src="{{ asset('/cloud-box/js/customizer.js') }}"></script>
    
    <!-- Chart Custom JavaScript -->
    <script src="{{ asset('/cloud-box/js/chart-custom.js') }}"></script>
  </body>
</html>
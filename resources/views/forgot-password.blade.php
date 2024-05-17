<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>{{ config("config.app_name") }} | Forgot password</title>
      
      <!-- Favicon -->
      <link rel="shortcut icon" href="{{ asset('/cloud-box/images/favicon.ico') }}" />
      
      <link rel="stylesheet" href="{{ asset('/cloud-box/css/backend-plugin.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/cloud-box/css/backend.css') }}?v=1.0.0">

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
                         <h2 class="mb-3">Reset Password</h2>
                         <p>Enter your email address and we'll send you an email with instructions to reset your password.</p>
                         <form onsubmit="sendResetLink()">
                            <div class="row">
                               <div class="col-lg-12">
                                  <div class="floating-label form-group">
                                     <input class="floating-input form-control" type="email" name="email" required />
                                     <label>Email</label>
                                  </div>
                               </div>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Reset</button>
                         </form>
                      </div>
                   </div>
                </div>
             </div>
          </section>
      </div>

        <script>
            async function sendResetLink() {
                event.preventDefault()
                const form = event.target

                try {
                    const formData = new FormData(form)
                    form.submit.setAttribute("disabled", "disabled")

                    const response = await axios.post(
                        baseUrl + "/api/send-password-reset-link",
                        formData
                    )

                    if (response.data.status == "success") {
                        swal.fire("Reset password", response.data.message, "success")
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
    
    <!-- app JavaScript -->
    <script src="{{ asset('/cloud-box/js/app.js') }}"></script>
  </body>
</html>
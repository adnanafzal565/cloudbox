<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Admin panel login</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('/administrator/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/administrator/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('/administrator/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/administrator/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('/administrator/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('/administrator/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('/administrator/vendor/simple-datatables/style.css') }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('/administrator/css/style.css') }}" rel="stylesheet">

    <!-- =======================================================
    * Template Name: NiceAdmin
    * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
    * Updated: Apr 7 2024 with Bootstrap v5.3.3
    * Author: BootstrapMade.com
    * License: https://bootstrapmade.com/license/
    ======================================================== -->
  </head>

  <body>

    <input type="hidden" id="baseUrl" value="{{ url('/') }}" />

    <script>
      const baseUrl = document.getElementById("baseUrl").value
    </script>

    <main>
      <div class="container">

        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                <div class="d-flex justify-content-center py-4">
                  <a href="{{ url('/admin') }}" class="logo d-flex align-items-center w-auto">
                    <img src="{{ asset('/administrator/img/logo.png') }}" alt="" />
                    <span class="d-none d-lg-block">Admin panel</span>
                  </a>
                </div><!-- End Logo -->

                <div class="card mb-3">

                  <div class="card-body">

                    <div class="pt-4 pb-2">
                      <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                      <p class="text-center small">Enter your username & password to login</p>
                    </div>

                    <form class="row g-3 needs-validation" novalidate onsubmit="doLogin()">

                      <div class="col-12">
                        <label for="yourUsername" class="form-label">Email</label>
                        <div class="input-group has-validation">
                          <span class="input-group-text" id="inputGroupPrepend">@</span>
                          <input type="email" name="email" class="form-control" id="yourUsername" required>
                          <div class="invalid-feedback">Please enter your email.</div>
                        </div>
                      </div>

                      <div class="col-12">
                        <label for="yourPassword" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="yourPassword" required>
                        <div class="invalid-feedback">Please enter your password!</div>
                      </div>

                      <div class="col-12">
                        <button class="btn btn-primary w-100" name="submit" type="submit">Login</button>
                      </div>
                    </form>

                  </div>
                </div>

                <div class="credits">
                  <!-- All the links in the footer should remain intact. -->
                  <!-- You can delete the links only if you purchased the pro version. -->
                  <!-- Licensing information: https://bootstrapmade.com/license/ -->
                  <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
                  Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
                </div>

              </div>
            </div>
          </div>

        </section>

      </div>
    </main><!-- End #main -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="{{ asset('/administrator/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('/administrator/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('/administrator/vendor/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('/administrator/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('/administrator/vendor/quill/quill.min.js') }}"></script>
    <script src="{{ asset('/administrator/vendor/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('/administrator/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('/administrator/vendor/php-email-form/validate.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('/administrator/js/main.js') }}"></script>
    <script src="{{ asset('/js/axios.min.js') }}"></script>
    <script src="{{ asset('/js/sweetalert2@11.js') }}"></script>
    <script src="{{ asset('/administrator/js/script.js?v=' . time()) }}"></script>

    <script>
      async function doLogin() {
        event.preventDefault()
        const form = event.target

        try {
          const formData = new FormData(form)
          form.submit.setAttribute("disabled", "disabled")

          const response = await axios.post(
            baseUrl + "/api/admin/login",
            formData
          )

          if (response.data.status == "success") {
            const accessToken = response.data.access_token
            localStorage.setItem(accessTokenKey, accessToken)
            window.location.href = baseUrl + "/admin"
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

  </body>

</html>
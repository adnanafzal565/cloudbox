<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>@yield("title", "Admin panel")</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="{{ asset('/administrator/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('/administrator/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

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
  <link href="{{ asset('/administrator/css/style.css') }}" rel="stylesheet" />
  <link href="{{ asset('/administrator/css/custom.css?v=' . time()) }}" rel="stylesheet" />
  <link href="{{ asset('/css/fontawesome.css') }}" rel="stylesheet" />
  <script src="{{ asset('/js/fontawesome.js') }}"></script>

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 7 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->

  <script src="{{ asset('/js/react.development.js') }}"></script>
  <script src="{{ asset('/js/react-dom.development.js') }}"></script>
  <script src="{{ asset('/js/babel.min.js') }}"></script>
  <script src="{{ asset('/js/axios.min.js') }}"></script>
  <script src="{{ asset('/js/sweetalert2@11.js') }}"></script>
  <script src="{{ asset('/administrator/js/script.js?v=' . time()) }}"></script>
</head>

<body>

  <input type="hidden" id="baseUrl" value="{{ url('/') }}" />

  <script>
    const baseUrl = document.getElementById("baseUrl").value
  </script>

  <!-- ======= Header ======= -->
  <header id="header-app" class="header fixed-top d-flex align-items-center">

  </header><!-- End Header -->

  <script type="text/babel">
    function Header() {

      const [state, setState] = React.useState(globalState.state)

      globalState.listen(function (newState) {
          setState(newState)
      })

      async function onInit() {
          window.user = null
          const accessToken = localStorage.getItem(accessTokenKey)
          
          try {
              const response = await axios.post(
                  baseUrl + "/api/me",
                  null,
                  {
                      headers: {
                          Authorization: "Bearer " + accessToken
                      }
                  }
              )

              if (response.data.status == "success") {
                  const newMessages = response.data.new_messages
                  window.user = response.data.user

                  globalState.setState({
                      user: window.user
                  })

                  if (newMessages > 0) {
                    document.getElementById("message-notification-badge").innerHTML = newMessages
                  }

                  window.onInit()
              } else {
                  // swal.fire("Error", response.data.message, "error")
              }
          } catch (exp) {
              // swal.fire("Error", exp.message, "error")
          }

          if (window.user == null) {
            window.location.href = baseUrl + "/admin/login"
          }
      }

      React.useEffect(function () {
          onInit()
      }, [])

      async function logout() {
          try {
              const response = await axios.post(
                  baseUrl + "/api/logout",
                  null,
                  {
                      headers: {
                          Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                      }
                  }
              )

              if (response.data.status == "success") {
                  globalState.setState({
                      user: null
                  })
                  localStorage.removeItem(accessTokenKey)
                  window.location.href = baseUrl + "/admin/login"
              } else {
                  swal.fire("Error", response.data.message, "error")
              }
          } catch (exp) {
              swal.fire("Error", exp.message, "error")
          }
      }

      return (
          <>
              <div className="d-flex align-items-center justify-content-between">
                <a href={`${ baseUrl }/admin`} className="logo d-flex align-items-center">
                  <img src={`${ baseUrl }/administrator/img/logo.png`} alt="" />
                  <span className="d-none d-lg-block">Admin panel</span>
                </a>
                <i className="bi bi-list toggle-sidebar-btn"></i>
              </div>

              <div className="search-bar">
                <form className="search-form d-flex align-items-center" method="POST" action="#">
                  <input type="text" name="query" placeholder="Search" title="Enter search keyword" />
                  <button type="submit" title="Search"><i className="bi bi-search"></i></button>
                </form>
              </div>

              <nav className="header-nav ms-auto">
                <ul className="d-flex align-items-center">

                  <li className="nav-item d-block d-lg-none">
                    <a className="nav-link nav-icon search-bar-toggle " href="#">
                      <i className="bi bi-search"></i>
                    </a>
                  </li>

                  <li className="nav-item dropdown pe-3">

                    <a className="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                      <img src={`${ baseUrl }/administrator/img/profile-img.jpg`} alt="Profile" className="rounded-circle" />
                      <span className="d-none d-md-block dropdown-toggle ps-2">{ state.user?.name }</span>
                    </a>

                    <ul className="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                      <li className="dropdown-header">
                        <h6>{ state.user?.name }</h6>
                        <span>{ state.user?.email }</span>
                      </li>

                      <li>
                        <hr className="dropdown-divider" />
                      </li>

                      <li>
                        <a className="dropdown-item d-flex align-items-center" href="javascript:void(0)" onClick={ logout }>
                          <i className="bi bi-box-arrow-right"></i>
                          <span>Sign Out</span>
                        </a>
                      </li>

                    </ul>
                  </li>

                </ul>
              </nav>
          </>
      )
  }

  ReactDOM.createRoot(
      document.getElementById("header-app")
  ).render(<Header />)
  </script>

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link {{ request()->url() == url('/admin') ? '' : 'collapsed' }}" href="{{ url('/admin') }}">
          <i class="fa fa-chart-area"></i>&nbsp;
          <span>Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->url() == url('/admin/users') ? '' : 'collapsed' }}" href="{{ url('/admin/users') }}">
          <i class="fa fa-users"></i>&nbsp;
          <span>Users</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->url() == url('/admin/messages') ? '' : 'collapsed' }}" href="{{ url('/admin/messages') }}">
          <i class="fa fa-comments"></i>&nbsp;
          <span>Messages</span>
          <span class="badge bg-primary badge-notification" id="message-notification-badge"></span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->url() == url('/admin/settings') ? '' : 'collapsed' }}" href="{{ url('/admin/settings') }}">
          <i class="fa fa-gear"></i>&nbsp;
          <span>Settings</span>
        </a>
      </li>

      <!--<li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Components</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="components-alerts.html">
              <i class="bi bi-circle"></i><span>Alerts</span>
            </a>
          </li>
          <li>
            <a href="components-accordion.html">
              <i class="bi bi-circle"></i><span>Accordion</span>
            </a>
          </li>
          <li>
            <a href="components-badges.html">
              <i class="bi bi-circle"></i><span>Badges</span>
            </a>
          </li>
          <li>
            <a href="components-breadcrumbs.html">
              <i class="bi bi-circle"></i><span>Breadcrumbs</span>
            </a>
          </li>
          <li>
            <a href="components-buttons.html">
              <i class="bi bi-circle"></i><span>Buttons</span>
            </a>
          </li>
          <li>
            <a href="components-cards.html">
              <i class="bi bi-circle"></i><span>Cards</span>
            </a>
          </li>
          <li>
            <a href="components-carousel.html">
              <i class="bi bi-circle"></i><span>Carousel</span>
            </a>
          </li>
          <li>
            <a href="components-list-group.html">
              <i class="bi bi-circle"></i><span>List group</span>
            </a>
          </li>
          <li>
            <a href="components-modal.html">
              <i class="bi bi-circle"></i><span>Modal</span>
            </a>
          </li>
          <li>
            <a href="components-tabs.html">
              <i class="bi bi-circle"></i><span>Tabs</span>
            </a>
          </li>
          <li>
            <a href="components-pagination.html">
              <i class="bi bi-circle"></i><span>Pagination</span>
            </a>
          </li>
          <li>
            <a href="components-progress.html">
              <i class="bi bi-circle"></i><span>Progress</span>
            </a>
          </li>
          <li>
            <a href="components-spinners.html">
              <i class="bi bi-circle"></i><span>Spinners</span>
            </a>
          </li>
          <li>
            <a href="components-tooltips.html">
              <i class="bi bi-circle"></i><span>Tooltips</span>
            </a>
          </li>
        </ul>
      </li>-->

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    @yield("main")

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright. All Rights Reserved
    </div>
  </footer><!-- End Footer -->

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

</body>

</html>
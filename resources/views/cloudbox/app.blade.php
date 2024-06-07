<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>@yield("title", config("config.app_name"))</title>
      
      <!-- Favicon -->
      <link rel="shortcut icon" href="{{ asset('/cloud-box/images/favicon.ico') }}" />
      
      <link rel="stylesheet" href="{{ asset('/cloud-box/css/backend-plugin.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/cloud-box/css/backend.css') }}?v=1.0.0">
      <link rel="stylesheet" href="{{ asset('/cloud-box/css/custom.css?v=' . time()) }}" />
      
      <link rel="stylesheet" href="{{ asset('/cloud-box/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css') }}">
      <link rel="stylesheet" href="{{ asset('/cloud-box/vendor/remixicon/fonts/remixicon.css') }}">

      <script src="{{ asset('/js/react.development.js') }}"></script>
      <script src="{{ asset('/js/react-dom.development.js') }}"></script>
      <script src="{{ asset('/js/babel.min.js') }}"></script>
      <script src="{{ asset('/js/axios.min.js') }}"></script>
      <script src="{{ asset('/js/sweetalert2@11.js') }}"></script>
      <script src="{{ asset('/js/fontawesome.js') }}"></script>

      <script type="text/babel" src="{{ asset('/cloud-box/js/script.js?v=' . time()) }}"></script>
      <script type="text/babel" src="{{ asset('/cloud-box/components/SingleFile.js?v=' . time()) }}"></script>
      <script type="text/babel" src="{{ asset('/cloud-box/components/SingleFolder.js?v=' . time()) }}"></script>
      
      </head>
  <body class="  ">

    <input type="hidden" id="baseUrl" value="{{ url('/') }}" />
    <input type="hidden" id="appName" value="{{ config('config.app_name') }}" />

    <script>
      const baseUrl = document.getElementById("baseUrl").value
      const appName = document.getElementById("appName").value
    </script>
      
    <script src="{{ asset('/js/script.js?v=' . time()) }}"></script>

    <!-- loader Start -->
    <!-- <div id="loading">
          <div id="loading-center">
          </div>
    </div> -->
    <!-- loader END -->
    <!-- Wrapper Start -->
    <div class="wrapper">
      
      <div class="iq-sidebar  sidebar-default ">
          <div class="iq-sidebar-logo d-flex align-items-center justify-content-between">
              <a href="{{ url('/') }}" class="header-logo">
                  <img src="{{ asset('/cloud-box/images/logo.png') }}" class="img-fluid rounded-normal light-logo" alt="logo">
               </a>
              <div class="iq-menu-bt-sidebar">
                  <i class="las la-bars wrapper-menu"></i>
              </div>
          </div>
          <div class="data-scrollbar" data-scroll="1">
              <div class="new-create select-dropdown input-prepend input-append">
                  <div class="btn-group">
                      <div data-toggle="dropdown">
                      <div class="search-query selet-caption"><i class="las la-plus pr-2"></i>Create new</div><span class="search-replace"></span>
                      <span class="caret"><!--icon--></span>
                      </div>
                      <ul class="dropdown-menu">
                          <li><div class="item" onclick="newFolder()"><i class="ri-folder-add-line pr-3"></i>New Folder</div></li>
                          <li><div class="item" onclick="selectFiles()"><i class="ri-file-upload-line pr-3"></i>Upload Files</div></li>
                      </ul>
                  </div>
              </div>

              <script>
                function newFolder() {
                  swal.fire({
                    title: 'Enter name of folder',
                    input: 'text',
                    inputAttributes: {
                      autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Create',
                    showLoaderOnConfirm: true,
                    preConfirm: function(name) {

                      return new Promise(async function (callback) {
                        let response = {}
                        const folderId = document.getElementById("folder-id")?.value || 0
                        
                        const formData = new FormData()
                        formData.append("name", name)
                        formData.append("folder_id", folderId)
                        try {
                          response = await axios.post(
                            baseUrl + "/api/folders/create",
                            formData,
                            {
                              headers: {
                                Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                              }
                            }
                          )
                        } catch (exp) {
                          swal.fire("Error", exp.message, "error")
                        } finally {
                          callback(response)
                        }
                      })
                      
                    },
                    allowOutsideClick: function() {
                      !swal.isLoading()
                    }
                  }).then(function(result) {
                    if (result.isConfirmed) {
                      const response = result.value

                      if (response.data.status == "success") {
                        // const folder = response.data.folder
                        // const stateFolders = globalState.state.folders
                        // stateFolders.push(folder)
                        globalState.setState({
                          reRender: true
                        })

                        // swal.fire("Create folder", response.data.message, "success")
                      } else {
                        swal.fire("Error", response.data.message, "error")
                      }
                    }
                  })
                }

                async function uploadFiles() {
                  event.preventDefault()

                  const form = event.target
                  form.submit.setAttribute("disabled", "disabled")
                  // const files = event.target.files
                  const folderId = document.getElementById("folder-id")?.value || 0
                  const formData = new FormData(form)
                  formData.append("folder_id", folderId)

                  // for (let a = 0; a < files.length; a++) {
                  //   formData.append("files[]", files[a])
                  // }

                  try {
                    const response = await axios.post(
                      baseUrl + "/api/files/upload",
                      formData,
                      {
                        headers: {
                          Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                        }
                      }
                    )

                    if (response.data.status == "success") {
                      const files = response.data.files
                      const stateFiles = globalState.state.files
                      for (let a = 0; a < files.length; a++) {
                        stateFiles.push(files[a])
                      }
                      globalState.setState({
                        files: stateFiles
                      })

                      swal.fire("File upload", response.data.message, "success")
                      $("#upload-file-modal").modal("hide")
                      document.getElementById("files").value = null
                    } else {
                      swal.fire("Error", response.data.message, "error")
                    }
                  } catch (exp) {
                    swal.fire("Error", exp.message, "error")
                  } finally {
                    form.submit.removeAttribute("disabled")
                  }
                }

                function selectFiles() {
                  // document.getElementById("files").click()
                  $("#upload-file-modal").modal("show")
                }
              </script>

              <nav class="iq-sidebar-menu">
                  <ul id="iq-sidebar-toggle" class="iq-menu">
                       <li class="active">
                              <a href="{{ url('/') }}" class="">
                                  <i class="las la-home iq-arrow-left"></i><span>Dashboard</span>
                              </a>
                          <ul id="dashboard" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                          </ul>
                       </li>
                       <li class=" ">
                            <a href="#mydrive" class="collapsed" data-toggle="collapse" aria-expanded="false">
                              <i class="las la-hdd"></i><span>My Drive</span>
                              <i class="las la-angle-right iq-arrow-right arrow-active"></i>
                              <i class="las la-angle-down iq-arrow-right arrow-hover"></i>
                            </a>

                            <ul id="mydrive" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                              
                            </ul>

                            <script type="text/babel">
                              function MyDrive() {
                                const [state, setState] = React.useState(globalState.state)

                                React.useEffect(function () {
                                  globalState.listen(function (newState) {
                                      setState(newState)
                                  })
                                }, [])

                                return (
                                    <>
                                       { state.user?.folders.map(function (f) {
                                        return (
                                          <li className=" " key={`my-drive-folder-${ f.id }`}>
                                            <a href={ `${ baseUrl }/folders/${ f.id }` }>
                                              <i className="lab la-blogger-b"></i><span>{ f.name }</span>
                                            </a>
                                          </li>
                                        )
                                       }) }
                                    </>
                                )
                              }

                              ReactDOM.createRoot(
                                document.getElementById("mydrive")
                              ).render(<MyDrive />)
                            </script>

                       </li>

                       <li>
                          <a href="javascript:void(0)" class="disabled">
                            <i class="las la-share"></i><span>Shared with me</span>
                          </a>
                       </li>

                        <li class=" ">
                          <a href="javascript:void(0)" class="disabled">
                            <i class="las la-trash-alt iq-arrow-left"></i><span>Trash</span>
                          </a>
                        </li>
                  </ul>
              </nav>
              <div class="sidebar-bottom" id="sidebar-bottom-app">
                  
              </div>

              <script type="text/babel">
                function SidebarBottom() {
                  const [state, setState] = React.useState(globalState.state)

                  React.useEffect(function () {
                    globalState.listen(function (newState) {
                        setState(newState)

                        if (newState.user != null) {
                          setTimeout(function () {
                            jQuery('.iq-progress-bar > span').each(function() {
                              let progressBar = jQuery(this);
                              let width = jQuery(this).data('percent');
                              progressBar.css({
                                  'transition': 'width 2s'
                              });

                              setTimeout(function() {
                                  progressBar.appear(function() {
                                      progressBar.css('width', width + '%');
                                  });
                              }, 100);
                            });
                          }, 1000)
                        }
                    })
                  }, [])

                  function getPercentage() {
                    if (state.user == null) return 0
                    const percentage = (state.user.storage_used / state.user.storage_total) * 100
                    if (isNaN(percentage)) {
                      return 0
                    }
                    return percentage.toFixed(2)
                  }

                  function getPercentageIn100() {
                    if (state.user == null) return 0
                    const percentage = (bytesOnlyToSize(state.user.storage_used) / bytesOnlyToSize(state.user.storage_total)) * 100
                    return percentage.toFixed(2)
                  }

                  return (
                      <>
                        { state.user ? (
                          <>
                            <h4 className="mb-3"><i className="las la-cloud mr-2"></i>Storage</h4>
                            <p>{ bytesToSize(state.user.storage_used) } / { bytesToSize(state.user.storage_total) } Used</p>
                            <div className="iq-progress-bar mb-3">
                                <span className="bg-primary iq-progress progress-1" data-percent={ getPercentage() }>
                                </span>
                            </div>
                            <p>{ getPercentage() }% Full - { bytesToSize(state.user.storage_total - state.user.storage_used) } Free</p>
                            <a href="#" onClick={ function () {
                              $("#buyNowModal").modal("show")
                            } } className="btn btn-outline-primary view-more mt-4">Buy Premium Version</a>
                          </>
                        ) : null }
                      </>
                  )
                }

                ReactDOM.createRoot(
                  document.getElementById("sidebar-bottom-app")
                ).render(<SidebarBottom />)
              </script>

              <div class="p-3"></div>
          </div>
          </div>       <div class="iq-top-navbar">
          <div class="iq-navbar-custom">
              <nav class="navbar navbar-expand-lg navbar-light p-0">
              <div class="iq-navbar-logo d-flex align-items-center justify-content-between">
                  <i class="ri-menu-line wrapper-menu"></i>
                  <a href="index.html" class="header-logo">
                      <img src="{{ asset('/cloud-box/images/logo.png') }}" class="img-fluid rounded-normal light-logo" alt="logo">
                      <img src="{{ asset('/cloud-box/images/logo-white.png') }}" class="img-fluid rounded-normal darkmode-logo" alt="logo">
                  </a>
              </div>
                  <div class="iq-search-bar device-search">
                      
                      <form id="form-search" action="{{ url('/search') }}">
                          <div class="input-prepend input-append">
                              <div class="btn-group">
                                  <label class="dropdown-toggle searchbox" data-toggle="dropdown">
                                    <input class="dropdown-toggle search-query text search-input" name="q" type="text"  placeholder="Type here to search..."><span class="search-replace"></span>
                                  </label>
                              </div>
                          </div>
                      </form>
                  </div>
      
                  <div class="d-flex align-items-center">
                      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"  aria-label="Toggle navigation">
                        <i class="ri-menu-3-line"></i>
                      </button>
                      
                      <div class="collapse navbar-collapse" id="navbarSupportedContent">
                          
                      </div> 

                      <script type="text/babel" src="{{ asset('/cloud-box/components/Header.js?v=' . time()) }}"></script>
                  </div>
              </nav>
          </div>
      </div>
      <div class="content-page">
        @yield("main")
      </div>
    </div>
    <!-- Wrapper End-->
    <footer class="iq-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="../backend/privacy-policy.html">Privacy Policy</a></li>
                        <li class="list-inline-item"><a href="../backend/terms-of-service.html">Terms of Use</a></li>
                    </ul>
                </div>
                <div class="col-lg-6 text-right">
                    <span class="mr-1"><script>document.write(new Date().getFullYear())</script>©</span> <a href="#" class="">CloudBOX</a>.
                </div>
            </div>
        </div>
    </footer>
    <!-- Backend Bundle JavaScript -->
    <script src="{{ asset('/cloud-box/js/backend-bundle.min.js') }}"></script>
    
    <!-- Chart Custom JavaScript -->
    <script src="{{ asset('/cloud-box/js/customizer.js') }}"></script>
    
    <!-- Chart Custom JavaScript -->
    <script src="{{ asset('/cloud-box/js/chart-custom.js') }}"></script>
    <!-- app JavaScript -->
    <script src="{{ asset('/cloud-box/js/app.js') }}"></script>
     <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Title</h4>
                    <div>
                        <a class="btn" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="resolte-contaniner" style="height: 500px;" class="overflow-auto">
                        File not found
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="visibility-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Change file visibility</h4>
                    <div>
                        <a class="btn" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                </div>

                <div class="modal-body">
                    <form id="form-visibility-file" onsubmit="changeFileVisibility()">
                        <input type="hidden" name="id" required />

                        <div class="form-group">
                            <label>Visibility</label>
                            <select name="visibility" class="form-control" required>
                                <option value="public" selected>Public</option>
                                <option value="private" disabled>Private (premium)</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <input type="submit" name="submit" form="form-visibility-file" class="btn btn-primary" value="Change visibility" />
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="share-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Share file</h4>
                    <div>
                        <a class="btn" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                </div>

                <div class="modal-body">
                    <form id="form-share-file" onsubmit="shareFile()">
                        <input type="hidden" name="id" required />

                        <div class="form-group">
                            <label>Enter email</label>
                            <input type="email" name="email" class="form-control" required />
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <input type="submit" name="submit" form="form-share-file" class="btn btn-primary" value="Share" />
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="upload-file-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Upload files</h4>
                    <div>
                        <a class="btn" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                </div>

                <div class="modal-body">
                    <form id="form-upload-files" enctype="multiple/form-data" onsubmit="uploadFiles()">
                        <div class="form-group">
                            <label>Select file(s)</label>
                            <input type="file" name="files[]" multiple id="files" />
                        </div>

                        <div class="form-group">
                            <label>Visibility</label>
                            <select name="visibility" class="form-control" required>
                                <option value="public" selected>Public</option>
                                <option value="private" disabled>Private (premium)</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <input type="submit" name="submit" form="form-upload-files" class="btn btn-primary" value="Upload" />
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-file-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">
                    Edit file

                    <b class="file-name"></b>
                  </h4>
                    <div>
                        <a class="btn" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                </div>
                <div class="modal-body" id="edit-file-app">
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="buyNowModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Buy Premium Version ($100 USD)</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <p><a href="https://apps.adnan-tech.com/files" target="_blank">See all features</a></p>
            <p>Let us know for re-branding and customizations.</p>
            <p>Contact: <b>support@adnan-tech.com</b></p>
          </div>
        </div>
      </div>
    </div>

    <script type="text/babel" src="{{ asset('/cloud-box/components/EditFile.js?v=' . time()) }}"></script>

    <a id="download-file" style="display: none;"></a>
  </body>
</html>
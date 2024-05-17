function Trash() {
    const [state, setState] = React.useState(globalState.state)
    const [folders, setFolders] = React.useState([])
    const [files, setFiles] = React.useState([])
    const [loading, setLoading] = React.useState(false)
    const [hasInit, setHasInit] = React.useState(false)

    React.useEffect(function () {
        globalState.listen(function (newState) {
            setState(newState)
        })
        
        onInit()
    }, [])

    async function onInit() {
        setLoading(true)
        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone
        const formData = new FormData()
        formData.append("time_zone", timeZone)

        try {
            const response = await axios.post(
                baseUrl + "/api/files/trash",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                setFolders(response.data.folders)
                setFiles(response.data.files)
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            swal.fire("Error", exp.message, "error")
        } finally {
            setLoading(false)
        }
    }

    return (
        <>
            <div className="row">
                <div className="col-lg-12">
                     <div className="d-flex align-items-center justify-content-between welcome-content mb-3">
                        <div className="navbar-breadcrumb">
                           <nav aria-label="breadcrumb">
                              <ul className="breadcrumb mb-0">
                                 <li className="breadcrumb-item"><a href={ baseUrl }>My Drive</a></li>
                                 <li className="breadcrumb-item active" aria-current="page">Trash</li>
                              </ul>
                           </nav>
                        </div>
                     </div>
                </div>
            </div>

            <div className="row">
                <div className="col-lg-12">
                    <div className="card card-block card-stretch card-transparent">
                        <div className="card-header d-flex justify-content-between pb-0">
                            <div className="header-title">
                                <h4 className="card-title">Folders</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            { loading && (
                <div className="row">
                    <div className="col-lg-12" style={{
                        textAlign: "center"
                    }}>
                        Loading...
                    </div>
                </div>
            ) }

            { !loading && folders.length <= 0 && (
                <div className="row">
                    <div className="col-lg-12">
                        <div className="alert alert-primary">
                            <p style={{
                                textAlign: "center",
                                width: "100%",
                                marginBottom: "0px"
                            }}>0 folders in this folder.</p>
                        </div>
                    </div>
                </div>
            ) }

            <div className="row">
                { folders.map(function (f) {
                    return (
                        <div className="col-md-6 col-sm-6 col-lg-3"
                            key={`folder-${ f.id }`}>
                            <div className="card card-block card-stretch card-height">
                                <div className="card-body">                            
                                        <div className="d-flex justify-content-between">
                                            <a href={ `${ baseUrl }/folder/${ f.id }` } className="folder">
                                                <div className="icon-small bg-danger rounded mb-4">
                                                    <i className="ri-file-copy-line"></i>
                                                </div>
                                            </a>
                                            <div className="card-header-toolbar">
                                                <div className="dropdown">
                                                    <span className="dropdown-toggle" id={`dropdownFolderButton-${ f.id }`} data-toggle="dropdown">
                                                        <i className="ri-more-2-fill"></i>
                                                    </span>
                                                    <div className="dropdown-menu dropdown-menu-right" aria-labelledby={`dropdownFolderButton-${ f.id }`}>
                                                        <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                                            permanentlyDeleteObject("folder", f.id, f.name, onInit)
                                                        } }><i className="ri-delete-bin-6-fill mr-2"></i>Permanently delete</a>
                                                        
                                                        <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                                            restoreObject("folder", f.id, onInit)
                                                        } }><i className="ri-arrow-go-back-fill mr-2"></i>Restore</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <a href={ `${ baseUrl }/folder/${ f.id }` } className="folder">
                                            <h5 className="mb-2">{ f.name }</h5>
                                            <p className="mb-2"><i className="lar la-clock text-danger mr-2 font-size-20"></i> { dateTime(f.deleted_at) }</p>
                                            <p className="mb-0"><i className="las la-file-alt text-danger mr-2 font-size-20"></i> { f.files } File(s)</p>
                                        </a>
                                </div>
                            </div>
                        </div>
                    )
                }) }
            </div>

            <div className="row" style={{
                marginTop: '20px'
            }}>
                <div className="col-lg-12">
                    <div className="card card-block card-stretch card-transparent ">
                        <div className="card-header d-flex justify-content-between pb-0">
                            <div className="header-title">
                                <h4 className="card-title">Files</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            { loading && (
                <div className="row">
                    <div className="col-lg-12" style={{
                        textAlign: "center"
                    }}>
                        Loading...
                    </div>
                </div>
            ) }

            { !loading && files.length <= 0 && (
                <div className="row">
                    <div className="col-lg-12">
                        <div className="alert alert-primary">
                            <p style={{
                                textAlign: "center",
                                width: "100%",
                                marginBottom: "0px"
                            }}>0 files in this folder.</p>
                        </div>
                    </div>
                </div>
            ) }

            <div className="icon icon-grid i-list" style={{
                display: 'block'
            }}>
                <div className="row">
                    <div className="col-lg-12">
                        <div className="card card-block card-stretch card-height">
                            <div className="card-body">
                                <div className="table-responsive">
                                    <table className="table mb-0 table-borderless tbl-server-info">
                                        <thead>
                                            <tr>
                                                <th scope="col">Name</th>
                                                <th scope="col">File Size</th>
                                                <th scope="col">Deleted at</th>
                                                <th scope="col"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            { files.map(function (file) {
                                                return (
                                                    <tr key={`recently-uploaded-file-list-${ file.id }`}>
                                                        <td>
                                                            { file.path ? (
                                                                <a href={ file.path } target="_blank">
                                                                    <div className="d-flex align-items-center">
                                                                        <div className="mr-3">
                                                                                <img src={`${ baseUrl }/cloud-box/images/layouts/page-1/${ getExtension(file.name) }.png`} className="img-fluid avatar-30" alt="image1"
                                                                                    onError={ function () {
                                                                                        event.target.src = baseUrl + "/cloud-box/images/layouts/page-1/jpg.png"
                                                                                    } } />
                                                                        </div>
                                                                        <span style={{
                                                                            color: "black"
                                                                        }}>{ file.name }</span>
                                                                    </div>
                                                                </a>
                                                            ) : (
                                                                <a href="javascript:void(0)">
                                                                    <div className="d-flex align-items-center">
                                                                        <div className="mr-3">
                                                                                <img src={`${ baseUrl }/cloud-box/images/layouts/page-1/private.png`} className="img-fluid avatar-30" alt="image1"
                                                                                    onError={ function () {
                                                                                        event.target.src = baseUrl + "/cloud-box/images/layouts/page-1/private.png"
                                                                                    } } />
                                                                        </div>
                                                                        <span style={{
                                                                            color: "black"
                                                                        }}>{ file.name }</span>
                                                                    </div>
                                                                </a>
                                                            ) }
                                                        </td>
                                                        <td>{ bytesToSize(file.size) }</td>
                                                        <td>{ dateTime(file.deleted_at) }</td>
                                                        <td>
                                                            <div className="dropdown">
                                                                <span className="dropdown-toggle" id={`dropdownFileListButton-${ file.id }`} data-toggle="dropdown">
                                                                    <i className="ri-more-fill"></i>
                                                                </span>
                                                                <div className="dropdown-menu dropdown-menu-right" aria-labelledby={`dropdownFileListButton-${ file.id }`}>
                                                                    
                                                                    { file.path ? (
                                                                        <a className="dropdown-item" href={ file.path } target="_blank"><i className="ri-eye-fill mr-2"></i>View</a>
                                                                    ) : (
                                                                        <a className="dropdown-item" href="javascript:void(0)"><i className="ri-download-line mr-2"></i>Download</a>
                                                                    ) }
                                                                    
                                                                    <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                                                        permanentlyDeleteObject("file", file.id, file.name, onInit)
                                                                    } }><i className="ri-delete-bin-6-fill mr-2"></i>Permanently delete</a>
                                                                    
                                                                    <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                                                        restoreObject("file", file.id, onInit)
                                                                    } }><i className="ri-arrow-go-back-fill mr-2"></i>Restore</a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                )
                                            }) }
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}

ReactDOM.createRoot(
    document.getElementById("trash-app")
).render(<Trash />)
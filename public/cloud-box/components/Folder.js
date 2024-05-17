function Folder() {
    const [state, setState] = React.useState(globalState.state)
    const [folder, setFolder] = React.useState(null)
    const [folders, setFolders] = React.useState([])
    const [files, setFiles] = React.useState([])
    const [tree, setTree] = React.useState([])
    const [loading, setLoading] = React.useState(false)
    const [hasInit, setHasInit] = React.useState(false)
    const [view, setView] = React.useState("grid")

    React.useEffect(function () {
        globalState.listen(function (newState, updatedState) {
            setState(newState)

            if (typeof updatedState.files !== "undefined") {
                onInit()
            }

            if (typeof updatedState.reRender !== "undefined" && updatedState.reRender) {
                onInit()
            }
        })
        
        onInit()
    }, [])

    async function onInit() {
        setLoading(true)
        const id = document.getElementById("folder-id").value
        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone
        const formData = new FormData()
        formData.append("time_zone", timeZone)
        formData.append("id", id)

        try {
            const response = await axios.post(
                baseUrl + "/api/folders",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                setFolder(response.data.folder)
                setFolders(response.data.folders)
                setFiles(response.data.files)
                setTree(response.data.tree)
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
                                 { tree.map(function (t) {
                                    return (
                                        <li className="breadcrumb-item" key={`folder-tree-${ t.id }`}>
                                            <a href={ `${ baseUrl }/folder/${ t.id }` }>{ t.name }</a>
                                        </li>
                                    )
                                 }) }
                                 <li className="breadcrumb-item active" aria-current="page">{ folder?.name }</li>
                              </ul>
                           </nav>
                        </div>
                        <div className="d-flex align-items-center">
                            <div className="list-grid-toggle mr-4">
                                { view == "grid" ? (
                                    <span className="icon icon-grid i-list" onClick={ function () {
                                        setView("list")
                                    } } style={{
                                        display: 'block'
                                    }}><i className="ri-list-check font-size-20"></i></span>
                                ) : view == "list" && (
                                    <span className="icon icon-grid i-grid" onClick={ function () {
                                        setView("grid")
                                    } }><i className="ri-layout-grid-line font-size-20"></i></span>
                                ) }
                                
                                <span className="label label-list">List</span>
                            </div>
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
                        <SingleFolder key={`folder-${ f.id }`}
                            folder={ f }
                            callBack={ onInit } />
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

            { view == "grid" ? (
                <div className="icon icon-grid i-grid">
                    <div className="row">
                        { files.map(function (file) {
                            return (
                                <SingleFile view="grid" file={ file } key={`single-file-grid-${ file.id }`}
                                    callBack={ onInit } />
                            )
                        }) }
                    </div>
                </div>
            ) : view == "list" && (
                <div className="icon icon-grid i-list" style={{
                    display: 'block'
                }}> 
                    <div className="row">
                        <div className="col-lg-12">
                            <div className="card card-block card-stretch card-height">
                                <div className="card-body">
                                    <div className="table-responsive1">
                                        <table className="table mb-0 table-borderless tbl-server-info">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">Last Edit</th>
                                                    <th scope="col">File Size</th>
                                                    <th scope="col"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                { files.map(function (file) {
                                                    return (
                                                        <SingleFile view="list" file={ file } key={`single-file-list-${ file.id }`}
                                                            callBack={ onInit } />
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
            ) }
        </>
    )
}

ReactDOM.createRoot(
    document.getElementById("folder-app")
).render(<Folder />)
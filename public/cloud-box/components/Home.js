function Home() {
    const [state, setState] = React.useState(globalState.state)
    const [files, setFiles] = React.useState([])
    const [folders, setFolders] = React.useState([])
    const [totalFiles, setTotalFiles] = React.useState(0)
    const [totalFolders, setTotalFolders] = React.useState(0)

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
        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone
        const formData = new FormData()
        formData.append("time_zone", timeZone)

        try {
            const response = await axios.post(
                baseUrl + "/api/files/home",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                setFiles(response.data.files)
                setFolders(response.data.folders)
                setTotalFiles(response.data.total_files)
                setTotalFolders(response.data.total_folders)
            } else {
                // swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            // swal.fire("Error", exp.message, "error")
        }
    }

    return (
        <>
            <div className="row">
                <div className="col-lg-12">
                    <div className="card card-block card-stretch card-height iq-welcome" style={{
                        backgroundImage: baseUrl + "/cloud-box/images/layouts/mydrive/background.png no-repeat scroll right center",
                        backgroundColor: '#ffffff',
                        backgroundSize: "contain"
                    }}>
                        <div className="card-body property2-content">
                            <div className="d-flex flex-wrap align-items-center">
                                <div className="col-lg-6 col-sm-6 p-0">
                                    <h3 className="mb-3">Welcome { state.user?.name }</h3>
                                    <p className="mb-5">You have { state.user?.new_notifications } new notifications and { state.user?.new_messages } unread messages to reply.</p>
                                    <a href="#">Try Now<i className="las la-arrow-right ml-2"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="row">
                <div className="col-lg-12">
                    <div className="card card-block card-stretch card-transparent ">
                        <div className="card-header d-flex justify-content-between pb-0">
                            <div className="header-title">
                                <h4 className="card-title">Recently uploaded</h4>
                            </div>
                            <div className="card-header-toolbar d-flex align-items-center">
                                <a href={ `${ baseUrl }/files/all` } className=" view-more">View all</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="row">
                { files.map(function (file) {
                    return (
                        <SingleFile view="grid" file={ file } key={`recently-uploaded-file-${ file.id }`}
                            callBack={ onInit } />
                    )
                }) }
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

            <div className="row">
                { folders.map(function (folder) {
                    return (
                        <SingleFolder key={`folder-${ folder.id }`}
                            folder={ folder }
                            callBack={ onInit } />
                    )
                }) }
            </div>

            <div className="row">
                <div className="col-lg-4">
                    <div className="card card-block card-stretch card-height  plan-bg" style={{
                        height: 'fit-content'
                    }}>
                        <div className="card-body">
                            <h4 className="mb-3 text-white">Unlock Your plan</h4>    
                            <p>Expanded Storage, Access To<br /> More Features On CloudBOX</p>   
                            <div className="row align-items-center justify-content-between">
                               <div className="col-6 go-white ">
                                <a href="#" className="btn d-inline-block mt-5">Go Premium</a>
                               </div>
                                <div className="col-6">
                                    <img src={ `${ baseUrl }/cloud-box/images/layouts/mydrive/lock-bg.png` } className="img-fluid" alt="image1" />
                                </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div className="col-lg-8">
                    <div className="card card-block card-stretch card-height ">
                        <div className="card-header d-flex justify-content-between">
                            <div className="header-title">
                                <h4 className="card-title">Statistic</h4>
                            </div>
                        </div>
                        <div className="card-body">
                            <div className="row mt-4">
                                <div className="col-lg-6 col-md-6 col-6">
                                    <div className="media align-items-center">
                                        <div className="icon iq-icon-box bg-primary rounded icon-statistic">
                                            <i className="las la-long-arrow-alt-down"></i>
                                        </div>
                                        <div className="media-body ml-3">
                                            <p className="mb-0">Folders</p>
                                            <h5>{ totalFolders }</h5>
                                        </div>
                                    </div>
                                </div>
                                <div className="col-lg-6 col-md-6 col-6">
                                    <div className="media align-items-center">
                                        <div className="icon iq-icon-box bg-light rounded icon-statistic">
                                            <i className="las la-long-arrow-alt-up"></i>
                                        </div>
                                        <div className="media-body ml-3">
                                            <p className="mb-0">Files</p>
                                            <h5>{ totalFiles }</h5>
                                        </div>
                                    </div>
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
    document.getElementById("home-app")
).render(<Home />)
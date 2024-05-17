function Shares() {

    const [state, setState] = React.useState(globalState.state)
    const [files, setFiles] = React.useState([])
    const [loading, setLoading] = React.useState(false)
    const [hasInit, setHasInit] = React.useState(false)
    const [page, setPage] = React.useState(1)
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
        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone
        const formData = new FormData()
        formData.append("time_zone", timeZone)
        formData.append("page", page)
        
        try {
            const response = await axios.post(
                baseUrl + "/api/files/shares",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                setFiles(response.data.files)
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            if (exp.response.status == 401) {
                window.location.href = baseUrl + "/login?redirect=" + window.location.href
            } else {
                swal.fire("Error", exp.message, "error")
            }
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
                                 <li className="breadcrumb-item active" aria-current="page">Shared with me</li>
                              </ul>
                           </nav>
                        </div>
                     </div>
                </div>
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
                            }}>0 files.</p>
                        </div>
                    </div>
                </div>
            ) }

            <div className="icon icon-grid i-grid">
                <div className="row">
                    { files.map(function (file) {
                        return (
                            <SingleFile view="grid" file={ file }
                                key={`single-file-grid-${ file.id }`}
                                callBack={ onInit }
                                isShared={ true } />
                        )
                    }) }
                </div>
            </div>
        </>
    )
}

ReactDOM.createRoot(
    document.getElementById("shares-app")
).render(<Shares />)
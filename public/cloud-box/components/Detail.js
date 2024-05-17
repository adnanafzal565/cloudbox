function Detail() {
    const [state, setState] = React.useState(globalState.state)
    const [file, setFile] = React.useState(null)
    const [loading, setLoading] = React.useState(false)
    const [id, setId] = React.useState(0)

    React.useEffect(function () {
        globalState.listen(function (newState, updatedState) {
            setState(newState)

            if (typeof updatedState.reRender !== "undefined" && updatedState.reRender) {
                onInit()
            }
        })

        onInit()
    }, [])

    async function onInit() {
        setLoading(true)
        const fileDetailId = document.getElementById("file-detail-id").value
        setId(fileDetailId)

        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone
        const formData = new FormData()
        formData.append("time_zone", timeZone)
        formData.append("id", fileDetailId)
        
        try {
            const response = await axios.post(
                baseUrl + "/api/files/detail",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                setFile(response.data.file)
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

    async function removeAccess(id, name, fileName) {
        swal.fire({
            title: "Remove access",
            text: "'" + name + "' will not be able to access '" + fileName + "'",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then(async function(result) {
            if (result.isConfirmed) {
                swal.showLoading()

                const formData = new FormData()
                formData.append("id", id)
                try {
                    const response = await axios.post(
                        baseUrl + "/api/files/remove-access",
                        formData,
                        {
                            headers: {
                                Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                            }
                        }
                    )

                    if (response.data.status == "success") {
                        globalState.setState({
                            reRender: true
                        })
                    } else {
                        swal.fire("Error", response.data.message, "error")
                    }
                } catch (exp) {
                    swal.fire("Error", exp.message, "error")
                } finally {
                    swal.hideLoading()
                }
            }
        })
    }

    return (
        <>
            { loading && (
                <div className="row">
                    <div className="col-lg-12" style={{
                        textAlign: "center"
                    }}>
                        Loading...
                    </div>
                </div>
            ) }

            { file != null && (
                <>
                    <div className="row">
                        <div className="col-lg-12">
                             <div className="d-flex align-items-center justify-content-between welcome-content mb-3">
                                <div className="navbar-breadcrumb">
                                   <nav aria-label="breadcrumb">
                                      <ul className="breadcrumb mb-0">
                                         <li className="breadcrumb-item"><a href={ baseUrl }>My Drive</a></li>
                                         <li className="breadcrumb-item active" aria-current="page">File detail</li>
                                         <li className="breadcrumb-item active" aria-current="page">{ file.name }</li>
                                      </ul>
                                   </nav>
                                </div>
                             </div>
                        </div>
                    </div>

                    <SingleFile view="grid" file={ file } />

                    <div className="row" style={{
                        marginTop: '20px'
                    }}>
                        <div className="col-lg-12">
                            <div className="card card-block card-stretch card-transparent ">
                                <div className="card-header d-flex justify-content-between pb-0">
                                    <div className="header-title">
                                        <h4 className="card-title">Shared with</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    { file.shares.length <= 0 && (
                        <div className="row">
                            <div className="col-lg-12">
                                <div className="alert alert-primary">
                                    <p style={{
                                        textAlign: "center",
                                        width: "100%",
                                        marginBottom: "0px"
                                    }}>0 shares.</p>
                                </div>
                            </div>
                        </div>
                    ) }

                    <div className="icon icon-grid i-grid">
                        <div className="row">
                            <div className="col-md-12">
                                <table className="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Shared at</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        { file.shares.map(function (share) {
                                            return (
                                                <tr key={ `file-share-${ share.id }` }>
                                                    <td>{ share.name }</td>
                                                    <td>{ share.email }</td>
                                                    <td>{ date(share.created_at) }</td>
                                                    <td>
                                                        <a className="btn btn-danger btn-sm" href="javascript:void(0)"
                                                            style={{
                                                                backgroundColor: "indianred"
                                                            }}
                                                            onClick={ function () {
                                                                removeAccess(share.id, share.name, file.name)
                                                            } }><i className="ri-file-edit-line mr-2"></i>Remove access</a>
                                                    </td>
                                                </tr>
                                            )
                                        }) }
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </>
            ) }
        </>
    )
}

ReactDOM.createRoot(
    document.getElementById("detail-app")
).render(<Detail />)
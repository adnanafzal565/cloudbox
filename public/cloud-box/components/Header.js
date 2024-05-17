function Header() {
    const [state, setState] = React.useState(globalState.state)

    React.useEffect(function () {
        globalState.listen(function (newState) {
            setState(newState)
        })
        
        onInit()
    }, [])

    async function onInit() {
        const accessToken = localStorage.getItem(accessTokenKey)
        if (accessToken) {
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
                    const user = response.data.user
                    const newMessages = user.new_messages

                    globalState.setState({
                        user: user
                    })

                    if (newMessages > 0) {
                        document.getElementById("message-notification-badge").innerHTML = newMessages
                    }
                } else {
                    // swal.fire("Error", response.data.message, "error")
                }
            } catch (exp) {
                // swal.fire("Error", exp.message, "error")
            }
        }
    }

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
                window.location.reload()
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            swal.fire("Error", exp.message, "error")
        }
    }

    return (

        <ul className="navbar-nav ml-auto navbar-list align-items-center">
            <li className="nav-item nav-icon search-content">
                <a href="#" className="search-toggle rounded" id="dropdownSearch" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i className="ri-search-line"></i>
                </a>
                <div className="iq-search-bar iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownSearch">
                    <form action="#" className="searchbox p-2">
                        <div className="form-group mb-0 position-relative">
                        <input type="text" className="text search-input font-size-12" placeholder="type here to search..." />
                        <a href="#" className="search-link"><i className="las la-search"></i></a> 
                        </div>
                    </form>
                </div>
            </li>

            { state.user == null ? (
                <>
                    <li className="nav-item">
                        <a className="nav-link" href={ `${ baseUrl }/login` } style={{
                            fontSize: "16px"
                        }}>Login</a>
                    </li>

                    <li className="nav-item">
                        <a className="nav-link" href={ `${ baseUrl }/register` } style={{
                            fontSize: "16px"
                        }}>Register</a>
                    </li>
                </>

            ) : (

                <li className="nav-item nav-icon dropdown caption-content">
                    <a href="#" className="search-toggle dropdown-toggle" id="dropdownMenuButton03" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" >
                        <div className="caption bg-primary line-height">{ state.user.name[0] }</div>
                    </a>
                    <div className="iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownMenuButton03">
                        <div className="card mb-0">
                            <div className="card-header d-flex justify-content-between align-items-center mb-0">
                            <div className="header-title">
                                <h4 className="card-title mb-0">Profile</h4>
                            </div>
                            <div className="close-data text-right badge badge-primary cursor-pointer "><i className="ri-close-fill"></i></div>
                            </div>
                            <div className="card-body">
                                <div className="profile-header">
                                    <div className="cover-container text-center">
                                        <div className="rounded-circle profile-icon bg-primary mx-auto d-block">
                                            { state.user.name[0] }
                                            <a href="">
                                                
                                            </a>
                                        </div>

                                        <div className="profile-detail mt-3">
                                            <h5><a href={ `${ baseUrl }/profile` }>{ state.user.name }</a></h5>
                                            <p>{ state.user.email }</p>
                                        </div>

                                        <a href="javascript:void(0)" onClick={ logout } className="btn btn-primary">Sign Out</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </li>
            )}
        </ul>
    )
}

ReactDOM.createRoot(
    document.getElementById("navbarSupportedContent")
).render(<Header />)
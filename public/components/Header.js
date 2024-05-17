function Header() {

    const [state, setState] = React.useState(globalState.state)

    globalState.listen(function (newState) {
        setState(newState)
    })

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
                window.location.reload()
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            swal.fire("Error", exp.message, "error")
        }
    }

    return (
        <div className="container">
            <a className="navbar-brand" href={ baseUrl }>{ appName }</a>
            
            <button className="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span className="navbar-toggler-icon"></span>
            </button>

            <div className="collapse navbar-collapse" id="navbarSupportedContent">
                <ul className="navbar-nav me-auto mb-2 mb-lg-0">
                    <li className="nav-item">
                        <a className="nav-link active" aria-current="page" href={ baseUrl }>Home</a>
                    </li>

                    { state.user == null ? (
                        <>
                            <li className="nav-item">
                                <a className="nav-link" href={ `${ baseUrl }/login` }>Login</a>
                            </li>

                            <li className="nav-item">
                                <a className="nav-link" href={ `${ baseUrl }/register` }>Register</a>
                            </li>
                        </>
                    ) : (
                        <ul className="navbar-nav">
                            <li className="nav-item dropdown">
                                <a className="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    { state.user.name }
                                </a>

                                <ul className="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a className="dropdown-item" href={ `${ baseUrl }/profile` }>Profile</a></li>
                                    <li><hr className="dropdown-divider" /></li>
                                    <li><a className="dropdown-item" onClick={ logout } href="javascript:void(0)">Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    ) }
                </ul>
            </div>
        </div>
    )
}

ReactDOM.createRoot(
    document.getElementById("header-app")
).render(<Header />)
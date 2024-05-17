function Profile() {

    const [state, setState] = React.useState(globalState.state)
    const [name, setName] = React.useState("")
    const [email, setEmail] = React.useState("")
    const [profileImage, setProfileImage] = React.useState("")
    const [isSaving, setIsSaving] = React.useState(false)

    globalState.listen(function (newState) {
        setState(newState)

        if (newState.user != null) {
            setName(newState.user.name)
            setEmail(newState.user.email)
            setProfileImage(newState.user.profile_image)
        }
    })

    async function saveProfile() {
        try {
            event.preventDefault()
            setIsSaving(true)

            const formData = new FormData(event.target)
            const response = await axios.post(
                baseUrl + "/api/save-profile",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                swal.fire("Profile", response.data.message, "success")
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            swal.fire("Error", exp.message, "error")
        } finally {
            setIsSaving(false)
        }
    }

    return (
        <form onSubmit={ saveProfile } encType="multipart/form-data">
            <div className="row" style={{
                marginBottom: "10px"
            }}>
                <div className="offset-4 col-3">
                    <img id="profile-image" style={{
                        width: "100px",
                        height: "100px",
                        objectFit: "cover",
                        borderRadius: "50%",
                        marginBottom: "20px",
                        position: "relative",
                        left: "50%",
                        transform: "translateX(-50%)"
                    }} src={ profileImage } />

                    <input type="file" name="profile_image" accept="image/*" />
                </div>
            </div>

            <div className="form-group">
                <label className="form-label">Name</label>
                <input type="text" name="name" value={ name } onChange={ function () {
                    setName(event.target.value)
                } } className="form-control" required />
            </div>

            <div className="form-group" style={{
                marginTop: "10px",
                marginBottom: "15px"
            }}>
                <label className="form-label">Email</label>
                <input type="email" name="email" value={ email } onChange={ function () {
                    setEmail(event.target.value)
                } } className="form-control" disabled />
            </div>

            <input type="submit" name="submit" className="btn btn-outline-primary btn-sm"
                value={ isSaving ? "Saving..." : "Save" }
                disabled={ isSaving } />
        </form>
    )
}

ReactDOM.createRoot(
    document.getElementById("profile-app")
).render(<Profile />)
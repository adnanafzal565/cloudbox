function ChangePassword() {

    const [isSaving, setIsSaving] = React.useState(false)
    const [currentPassword, setCurrentPassword] = React.useState("")
    const [newPassword, setNewPassword] = React.useState("")

    async function changePassword() {
        try {
            event.preventDefault()
            setIsSaving(true)

            const formData = new FormData(event.target)
            const response = await axios.post(
                baseUrl + "/api/change-password",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                swal.fire("Change password", response.data.message, "success")
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
        <form onSubmit={ changePassword }>
            <div className="form-group">
                <label className="form-label">Current password</label>
                <input type="password" name="current_password" value={ currentPassword } onChange={ function () {
                    setCurrentPassword(event.target.value)
                } } className="form-control" />
            </div>

            <div className="form-group" style={{
                marginTop: "10px",
                marginBottom: "15px"
            }}>
                <label className="form-label">New password</label>
                <input type="password" name="new_password" value={ newPassword } onChange={ function () {
                    setNewPassword(event.target.value)
                } } className="form-control" />
            </div>

            <input type="submit" name="submit" className="btn btn-outline-primary btn-sm"
                value={ isSaving ? "Saving..." : "Change" }
                disabled={ isSaving } />
        </form>
    )
}

ReactDOM.createRoot(
    document.getElementById("change-password-app")
).render(<ChangePassword />)
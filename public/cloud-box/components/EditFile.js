function EditFile() {
    const [state, setState] = React.useState(globalState.state)
    const [content, setContent] = React.useState("")
    const [loading, setLoading] = React.useState(false)
    const [updating, setUpdating] = React.useState(false)

    React.useEffect(function () {
        globalState.listen(function (newState) {
            setState(newState)

            if (newState.editFile != null) {
                setContent(newState.editFile.content)
            }
        })
    }, [])

    async function updateContent() {
        event.preventDefault()
        const form = event.target
        const formData = new FormData(form)
        formData.append("id", state.editFile?.id || 0)

        setUpdating(true)

        try {
            const response = await axios.post(
                baseUrl + "/api/files/update-content",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                swal.fire("Update file", response.data.message, "success")
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            swal.fire("Error", exp.message, "error")
        } finally {
            setUpdating(false)
        }
    }

    return (
        <>
            { state.editFile == null ? (
                <p>Loading...</p>
            ) : (
                <form onSubmit={ updateContent }>
                    <div className="form-group">
                        <label>Content</label>
                        <textarea value={ content }
                            onChange={ function () {
                                setContent(event.target.value)
                            } }
                            className="form-control" name="content" required></textarea>
                    </div>

                    <input type="submit" className="btn btn-outline-primary" disabled={ updating } value="Update" />
                </form>
            ) }
        </>
    )
}

ReactDOM.createRoot(
    document.getElementById("edit-file-app")
).render(<EditFile />)
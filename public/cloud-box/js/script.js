async function changeFileVisibility() {
    event.preventDefault()

    const form = event.target
    const formData = new FormData(form)
    form.submit.setAttribute("disabled", "disabled")

    try {
        const response = await axios.post(
            baseUrl + "/api/files/change-visibility",
            formData,
            {
                headers: {
                    Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                }
            }
        )

        if (response.data.status == "success") {
            swal.fire("Visibility", response.data.message, "success")
            globalState.setState({
                reRender: true
            })
            $("#visibility-modal").modal("hide")
        } else {
            swal.fire("Error", response.data.message, "error")
        }
    } catch (exp) {
        swal.fire("Error", exp.message, "error")
    } finally {
        form.submit.removeAttribute("disabled")
    }
}

async function shareFile() {
    event.preventDefault()

    const form = event.target
    const formData = new FormData(form)
    form.submit.setAttribute("disabled", "disabled")

    try {
        const response = await axios.post(
            baseUrl + "/api/files/share",
            formData,
            {
                headers: {
                    Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                }
            }
        )

        if (response.data.status == "success") {
            swal.fire("File share", response.data.message, "success")
            $("#share-modal").modal("hide")
            document.getElementById("form-share-file").reset()
        } else {
            swal.fire("Error", response.data.message, "error")
        }
    } catch (exp) {
        swal.fire("Error", exp.message, "error")
    } finally {
        form.submit.removeAttribute("disabled")
    }
}

function permanentlyDeleteObject(type, id, name, callbackFunc) {
    swal.fire({
        title: "Delete " + type,
        text: type == "folder" ? "All files and folders inside folder '" + name + "' will be deleted as well." : "Are you sure you want to delete file '" + name + "' permanently ?",
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
                    baseUrl + "/api/" + type + "s/delete-permanently",
                    formData,
                    {
                        headers: {
                            Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                        }
                    }
                )

                if (response.data.status == "success") {
                    if (callbackFunc)
                        callbackFunc()
                    swal.fire("Delete " + type, response.data.message, "success")
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

function rename(type, id, name, callbackFunc) {
    swal.fire({
        title: "Enter new name of " + type + " '" + name + "'",
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        inputValue: name,
        showCancelButton: true,
        confirmButtonText: 'Rename',
        showLoaderOnConfirm: true,

        preConfirm: function(name) {
            return new Promise(async function (callback) {
                let response = {}

                const formData = new FormData()
                formData.append("id", id)
                formData.append("name", name)
                try {
                    response = await axios.post(
                        baseUrl + "/api/" + type + "s/rename",
                        formData,
                        {
                            headers: {
                                Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                            }
                        }
                    )
                } catch (exp) {
                    swal.fire("Error", exp.message, "error")
                } finally {
                    callback(response)
                }
            })
        },

        allowOutsideClick: function() {
            !swal.isLoading()
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            const response = result.value

            if (response.data?.status == "success") {
                if (callbackFunc)
                    callbackFunc()
                // swal.fire("Rename " + type, response.data.message, "success")
            } else {
                swal.fire("Error", response.data?.message, "error")
            }
        }
    })
}
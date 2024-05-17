function SingleFile({ view, file, callBack, isShared }) {

    let extension = file.extension
    // let extension = file.path?.split(".") || []
    // extension = extension[extension.length - 1]
    // extension = extension?.toLowerCase() || ""

    async function onEditFileClicked() {
        $("#edit-file-modal").modal("show")
        $("#edit-file-modal .file-name").html("\"" + file.name + "\"")

        try {
            const formData = new FormData()
            formData.append("id", file.id)

            const response = await axios.post(
                baseUrl + "/api/files/fetch-content",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                const tempFile = { ...file }
                tempFile.content = response.data.content
                globalState.setState({
                    editFile: tempFile
                })
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            swal.fire("Error", exp.message, "error")
        }
    }

    function showShareModal() {
        const form = document.getElementById("form-share-file")
        form.id.value = file.id
        
        $("#share-modal").modal("show")
    }

    function showVisibilityModal() {
        const form = document.getElementById("form-visibility-file")
        form.id.value = file.id
        form.visibility.value = file.visibility
        
        $("#visibility-modal").modal("show")
    }

    return (
        <>
            { view == "grid" ? (
                <div className="col-lg-3 col-md-6 col-sm-6">
                    <div className="card card-block card-stretch card-height">
                        <div className="card-body image-thumb">
                            <a href={ file.path } target="_blank">
                                <div className="mb-4 text-center p-3 rounded iq-thumb">
                                    <div className="iq-image-overlay"></div>
                                    <img src={`${ baseUrl }/cloud-box/images/layouts/page-1/${ getExtension(file.name) }.png`} className="img-fluid" alt="image1"
                                        onError={ function () {
                                            event.target.src = baseUrl + "/cloud-box/images/layouts/page-1/jpg.png"
                                        } } />       
                                </div>
                                <h6>{ file.name }</h6> 
                            </a>

                            <div className="row" style={{
                                marginTop: "20px"
                            }}>
                                <div className="col-md-6">
                                    <span>{ bytesToSize(file.size) }</span>
                                </div>

                                <div className="col-md-6">
                                    <div className="dropdown" style={{
                                        textAlign: "right"
                                    }}>
                                        <span className="dropdown-toggle dropdownFileGridButton" id={`dropdownFileGridButton-${ file.id }`} data-toggle="dropdown">
                                            <i className="ri-more-fill"></i>
                                        </span>

                                        <div className="dropdown-menu dropdown-menu-right" aria-labelledby={`dropdownFileGridButton-${ file.id }`}>
                                            <a className="dropdown-item" href={ file.path } target="_blank"><i className="ri-eye-fill mr-2"></i>View</a>
                                            <a className="dropdown-item" href={ `${ baseUrl }/files/detail/${ file.id }` }><i className="ri-information-line mr-2"></i>Detail</a>
                                            <a className="dropdown-item disabled" href="javascript:void(0)" onClick={ showShareModal }><i className="ri-share-line mr-2"></i>Share (premium)</a>
                                            <a className="dropdown-item disabled" href="javascript:void(0)" onClick={ showVisibilityModal }><i className="ri-eye-line mr-2"></i>Visibility (premium)</a>
                                            
                                            <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                                permanentlyDeleteObject("file", file.id, file.name, callBack)
                                            } }><i className="ri-delete-bin-6-fill mr-2"></i>Delete</a>

                                            <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                                rename("file", file.id, file.name, callBack)
                                            } }><i className="ri-pencil-fill mr-2"></i>Rename</a>

                                            { extension == "txt" && (
                                                <a className="dropdown-item disabled" href="javascript:void(0)" onClick={ onEditFileClicked }><i className="ri-file-edit-line mr-2"></i>Edit (premium)</a>
                                            ) }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            ) : view == "list" && (
                <tr>
                    <td>
                        <a href={ file.path } target="_blank">
                            <div className="d-flex align-items-center">
                                <div className="mr-3">
                                        <img src={`${ baseUrl }/cloud-box/images/layouts/page-1/${ getExtension(file.name) }.png`} className="img-fluid avatar-30" alt="image1"
                                            onError={ function () {
                                                event.target.src = baseUrl + "/cloud-box/images/layouts/page-1/jpg.png"
                                            } } />
                                </div>
                                <span style={{
                                    color: "black"
                                }}>{ file.name }</span>
                            </div>
                        </a>
                    </td>
                    <td>{ date(file.updated_at) }</td>
                    <td>{ bytesToSize(file.size) }</td>
                    <td>
                        <div className="dropdown">
                            <span className="dropdown-toggle" id={`dropdownFileListButton-${ file.id }`} data-toggle="dropdown">
                                <i className="ri-more-fill"></i>
                            </span>
                            <div className="dropdown-menu dropdown-menu-right" aria-labelledby={`dropdownFileListButton-${ file.id }`}>

                                <a className="dropdown-item" href={ file.path } target="_blank"><i className="ri-eye-fill mr-2"></i>View</a>
                                <a className="dropdown-item disabled" href="javascript:void(0)" onClick={ showShareModal }><i className="ri-share-line mr-2"></i>Share (premium)</a>
                                <a className="dropdown-item disabled" href="javascript:void(0)" onClick={ showVisibilityModal }><i className="ri-eye-line mr-2"></i>Visibility (premium)</a>
                                
                                <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                    permanentlyDeleteObject("file", file.id, file.name, callBack)
                                } }><i className="ri-delete-bin-6-fill mr-2"></i>Delete</a>

                                <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                    rename("file", file.id, file.name, callBack)
                                } }><i className="ri-pencil-fill mr-2"></i>Rename</a>

                                { extension == "txt" && (
                                    <a className="dropdown-item disabled" href="javascript:void(0)" onClick={ onEditFileClicked }><i className="ri-file-edit-line mr-2"></i>Edit (premium)</a>
                                ) }
                            </div>
                        </div>
                    </td>
                </tr>
            ) }
        </>
    )
}
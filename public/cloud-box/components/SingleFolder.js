function SingleFolder({ folder, callBack }) {
    return (
        <div className="col-md-6 col-sm-6 col-lg-3">
            <div className="card card-block card-stretch card-height">
                <div className="card-body">                            
                        <div className="d-flex justify-content-between">
                            <a href={ `${ baseUrl }/folders/${ folder.id }` } className="folder">
                                <div className="icon-small bg-danger rounded mb-4">
                                    <i className="ri-file-copy-line"></i>
                                </div>
                            </a>
                            <div className="card-header-toolbar">
                                <div className="dropdown">
                                    <span className="dropdown-toggle" id={`dropdownFolderButton-${ folder.id }`} data-toggle="dropdown">
                                        <i className="ri-more-2-fill"></i>
                                    </span>
                                    <div className="dropdown-menu dropdown-menu-right" aria-labelledby={`dropdownFolderButton-${ folder.id }`}>
                                        <a className="dropdown-item" href={ `${ baseUrl }/folders/${ folder.id }` }><i className="ri-eye-fill mr-2"></i>View</a>
                                        
                                        <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                            rename("folder", folder.id, folder.name, callBack)
                                        } }><i className="ri-pencil-fill mr-2"></i>Rename</a>
                                        
                                        <a className="dropdown-item" href="javascript:void(0)" onClick={ function () {
                                            permanentlyDeleteObject("folder", folder.id, folder.name, callBack)
                                        } }><i className="ri-delete-bin-6-fill mr-2"></i>Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href={ `${ baseUrl }/folders/${ folder.id }` } className="folder">
                            <h5 className="mb-2">{ folder.name }</h5>
                            <p className="mb-2"><i className="lar la-clock text-danger mr-2 font-size-20"></i> { date(folder.updated_at) }</p>
                            <p className="mb-0"><i className="las la-file-alt text-danger mr-2 font-size-20"></i> { folder.files } File(s)</p>
                        </a>
                </div>
            </div>
        </div>
    )
}
function Chat() {

    const [state, setState] = React.useState(globalState.state)
    const [message, setMessage] = React.useState("")
    const [sending, setSending] = React.useState(false)
    const [fetching, setFetching] = React.useState(false)
    const [messages, setMessages] = React.useState([])
    const [show, setShow] = React.useState(false)
    const [initialized, setInitialized] = React.useState(false)
    const [attachments, setAttachments] = React.useState([])

    globalState.listen(function (newState) {
        setState(newState)
    })

    React.useEffect(function () {
        if (show && !initialized && state.user != null) {
            onInit()
        }
    }, [show])

    function attachmentSelected() {
        const files = event.target.files
        const tempFiles = []
        for (let a = 0; a < files.length; a++) {
            const fileReader = new FileReader()
            fileReader.onload = function (event) {
                tempFiles.push({
                    name: files[a].name,
                    src: event.target.result
                })

                if (tempFiles.length == files.length) {
                    setAttachments(tempFiles)
                }
            }
            fileReader.readAsDataURL(files[a])
        }
    }

    function removeAttachment(name) {
        const tempAttachments = [...attachments]
        for (let a = 0; a < tempAttachments.length; a++) {
            if (tempAttachments[a].name == name) {
                tempAttachments.splice(a, 1)
            }
        }
        setAttachments(tempAttachments)
    }

    async function sendMessage() {
        event.preventDefault()
        setSending(true)
        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone

        const form = event.target
        try {
            const formData = new FormData(form)
            formData.append("time_zone", timeZone)

            const response = await axios.post(
                baseUrl + "/api/messages/send",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                const tempMessages = [...messages]
                const newMessage = response.data.message_obj
                tempMessages.push(newMessage)
                setMessages(tempMessages)
                setMessage("")
                setAttachments([])

                setTimeout(function () {
                    document.querySelector(".conversation-container").scrollTop = document.querySelector(".conversation-container").scrollHeight
                }, 100)
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            swal.fire("Error", exp.message, "error")
        } finally {
            setSending(false)
        }
    }

    async function onInit() {
        setFetching(true)

        try {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone
            const formData = new FormData()
            formData.append("time_zone", timeZone)

            const response = await axios.post(
                baseUrl + "/api/messages/fetch",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                const notificationsCount = response.data.notifications_count
                const newMessages = response.data.messages
                const tempMessages = [...messages]

                for (let a = newMessages.length - 1; a >= 0; a--) {
                    tempMessages.push(newMessages[a])
                }
                setMessages(tempMessages)
                setInitialized(true)

                let currentNotificationCount = document.getElementById("message-notification-badge").innerHTML ?? "0"
                currentNotificationCount = parseInt(currentNotificationCount)
                currentNotificationCount -= notificationsCount

                if (currentNotificationCount > 0) {
                  document.getElementById("message-notification-badge").innerHTML = currentNotificationCount
                } else {
                  document.getElementById("message-notification-badge").innerHTML = ""
                }

                setTimeout(function () {
                    document.querySelector(".conversation-container").scrollTop = document.querySelector(".conversation-container").scrollHeight
                }, 100)
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            swal.fire("Error", exp.message, "error")
        } finally {
            setFetching(false)
        }
    }

    return (
        <>
            { show && (
                <div className="page">
                  <div className="marvel-device nexus5">
                    <div className="top-bar"></div>
                    <div className="sleep"></div>
                    <div className="volume"></div>
                    <div className="camera"></div>
                    <div className="screen">
                      <div className="screen-container">
                        <div className="status-bar">
                          <div className="time"></div>
                          <div className="battery">
                            <i className="fa fa-battery"></i>
                          </div>
                          <div className="network">
                            <i className="fa fa-signal"></i>
                          </div>
                          <div className="wifi">
                            <i className="fa fa-wifi"></i>
                          </div>
                          {/*<div className="star">
                            <i className="fa fa-star"></i>
                          </div>*/}
                        </div>
                        <div className="chat">
                          <div className="chat-container">
                            <div className="user-bar">
                              {/*<div className="back">
                                <i className="fa fa-arrow-left"></i>
                              </div>*/}
                              <div className="avatar">
                                <img src={`${ baseUrl }/img/adnan-afzal.jpg` } alt="https://github.com/adnanafzal565" />
                              </div>
                              <div className="name">
                                <span>Admin</span>
                                {/*<span className="status">online</span>*/}
                              </div>
                              {/*<div className="actions more">
                                <i className="fa fa-more-vert"></i>
                              </div>
                              <div className="actions attachment">
                                <i className="fa fa-attachment"></i>
                              </div>
                              <div className="actions">
                                <i className="fa fa-phone"></i>
                              </div>*/}
                            </div>
                            <div className="conversation">
                              <div className="conversation-container">
                                {/*<div className="message sent">
                                  What happened last night?
                                  <span className="metadata">
                                      <span className="time"></span><span className="tick"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" id="msg-dblcheck-ack" x="2063" y="2076"><path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.88a.32.32 0 0 1-.484.032l-.358-.325a.32.32 0 0 0-.484.032l-.378.48a.418.418 0 0 0 .036.54l1.32 1.267a.32.32 0 0 0 .484-.034l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.88a.32.32 0 0 1-.484.032L1.892 7.77a.366.366 0 0 0-.516.005l-.423.433a.364.364 0 0 0 .006.514l3.255 3.185a.32.32 0 0 0 .484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z" fill="#4fc3f7"/></svg></span>
                                  </span>
                                </div>
                                <div className="message received">
                                  You were drunk.
                                  <span className="metadata"><span className="time"></span></span>
                                </div>*/}

                                { messages.map(function (m) {
                                    return (
                                        <div className={`message ${ m.sender_id == state.user.id ? "sent" : "received" }`} key={`message-${ m.id }`}>
                                            { m.message }

                                            <div style={{
                                                marginTop: "20px"
                                            }}>
                                                { m.attachments.map(function (attachment, attachmentIndex) {
                                                    return (
                                                        <div key={`message-attachment-${ m.id }-${ attachmentIndex }`} style={{
                                                            width: "fit-content",
                                                            marginRight: "20px",
                                                            marginBottom: "20px",
                                                            position: "relative",
                                                            display: "inline-block"
                                                        }}>
                                                            <img src={ attachment } style={{
                                                                width: "50px",
                                                                height: "50px",
                                                                objectFit: "cover",
                                                                cursor: "pointer"
                                                            }} onClick={ function () {
                                                                window.open(attachment, "_blank")
                                                            } } />
                                                        </div>
                                                    )
                                                }) }
                                            </div>

                                            <span className="metadata"><span className="time">{ m.created_at }</span></span>
                                        </div>
                                    )
                                }) }

                              </div>

                              <form className="conversation-compose" encType="multipart/form-data"
                                onSubmit={ sendMessage }>
                                <div className="emoji" onClick={ function () {
                                    document.getElementById("input-attachment-message").click()
                                } }>
                                    <i className="fa fa-paperclip"></i>

                                    <input type="file" style={{
                                        display: "none"
                                    }} id="input-attachment-message" multiple onChange={ attachmentSelected }
                                        name="attachments[]" />
                                </div>

                                <input className="input-msg" value={ message } onChange={ function () {
                                    setMessage(event.target.value)
                                } } name="message" placeholder="Type a message" autoComplete="off" autoFocus />

                                {/*<div className="photo">
                                  <i className="fa fa-camera"></i>
                                </div>*/}
                                
                                <button className="send" type="submit" disabled={ sending }>
                                    <div className="circle" style={{
                                        backgroundColor: sending ? "gray" : "#008a7c"
                                    }}>
                                        <i className="fa fa-paper-plane"></i>
                                    </div>
                                </button>
                              </form>

                                { attachments.length > 0 && (
                                    <div style={{
                                        marginRight: "10px",
                                        marginLeft: "10px",
                                        marginTop: "20px",
                                        display: "inline-block",
                                        maxWidth: "300px"
                                    }}>
                                        { attachments.map(function (attachment) {
                                            return (
                                                <div key={`selected-attachment-${ attachment.name }`} style={{
                                                    width: "fit-content",
                                                    marginRight: "20px",
                                                    marginBottom: "20px",
                                                    position: "relative",
                                                    display: "inline-block"
                                                }}>
                                                    <div onClick={ function () {
                                                        removeAttachment(attachment.name)
                                                    } }>
                                                        <i className="fa fa-close" style={{
                                                            color: "red",
                                                            position: "absolute",
                                                            right: "-10px",
                                                            top: "-10px",
                                                            border: "2px solid white",
                                                            borderRadius: "50%",
                                                            fontSize: "12px",
                                                            cursor: "pointer"
                                                        }}></i>
                                                    </div>

                                                    <img src={ attachment.src } style={{
                                                        width: "50px",
                                                        height: "50px",
                                                        objectFit: "cover"
                                                    }} />
                                                </div>
                                            )
                                        }) }
                                    </div>
                                ) }
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            ) }

            <button type="button" style={{
                position: "fixed",
                right: "20px",
                bottom: "20px",
                backgroundColor: "#4154f1",
                color: "white",
                border: "none",
                width: "60px",
                borderRadius: "50%",
                height: "60px"
            }} onClick={ function () {
                setShow(!show)
            } }>
                <span className="badge bg-danger" id="message-notification-badge"></span>
                Chat
            </button>
        </>
    )
}

ReactDOM.createRoot(
    document.getElementById("chat-app")
).render(<Chat />)
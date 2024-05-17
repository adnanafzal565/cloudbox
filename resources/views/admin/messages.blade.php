@extends ("admin/layouts/app")
@section ("title", "Messages")

@section ("main")

  <div class="pagetitle">
    <h1>Messages</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item active">Messages</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section" id="messages-app">

  </section>

  <script type="text/babel">
    function Attachments({ message }) {
      const styles = {
        singleAttachment: {
          width: "fit-content",
          marginRight: "20px",
          marginBottom: "20px",
          position: "relative",
          display: "inline-block"
        },

        attachmentImage: {
          width: "50px",
          height: "50px",
          objectFit: "cover",
          cursor: "pointer"
        },

        container: {
          marginTop: "10px"
        }
      }

      return (
        <div style={ styles.container }>
          { message.attachments.map(function (attachment, attachmentIndex) {
            return (
              <div key={`message-attachment-${ message.id }-${ attachmentIndex }`} style={ styles.singleAttachment }>
                <img src={ attachment } style={ styles.attachmentImage } onClick={ function () {
                  window.open(attachment, "_blank")
                } } />
              </div>
            )
          }) }
        </div>
      )
    }

    function Messages() {
      const [state, setState] = React.useState(globalState.state)
      const [fetchingContacts, setFetchingContacts] = React.useState(false)
      const [contacts, setContacts] = React.useState([])
      const [selectedContact, setSelectedContact] = React.useState(0)
      const [fetchingMessages, setFetchingMessages] = React.useState(false)
      const [messages, setMessages] = React.useState([])
      const [message, setMessage] = React.useState("")
      const [sending, setSending] = React.useState(false)
      const [attachments, setAttachments] = React.useState([])
      const [search, setSearch] = React.useState("")

      const styles = {
        btnSubmit: {
          backgroundColor: sending ? "gray" : "#05728f"
        },
        hidden: {
          display: "none"
        },
        attachmentContainer: {
          width: "fit-content",
          marginTop: "13px",
          marginRight: "10px",
          cursor: "pointer"
        },
        selectedAttachmentContainer: {
          marginRight: "10px",
          marginLeft: "10px",
          marginTop: "20px",
          display: "inline-block",
          maxWidth: "300px"
        },
        selectedSingleAttachment: {
          width: "fit-content",
          marginRight: "20px",
          marginBottom: "20px",
          position: "relative",
          display: "inline-block"
        },
        iconRemoveAttachment: {
          color: "red",
          position: "absolute",
          right: "-10px",
          top: "-10px",
          border: "2px solid white",
          borderRadius: "50%",
          fontSize: "12px",
          cursor: "pointer"
        },
        selectedImage: {
          width: "50px",
          height: "50px",
          objectFit: "cover"
        }
      }

      globalState.listen(function (newState) {
        setState(newState)

        if (newState.user != null) {
          onInit()
        }
      })

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

      async function onInit() {
        setFetchingContacts(true)

        try {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone
            const formData = new FormData()
            formData.append("time_zone", timeZone)
            formData.append("search", search)

            const response = await axios.post(
                baseUrl + "/api/admin/fetch-contacts",
                formData,
                {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                    }
                }
            )

            if (response.data.status == "success") {
                setContacts(response.data.users)
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        } catch (exp) {
            swal.fire("Error", exp.message, "error")
        } finally {
            setFetchingContacts(false)
        }
      }

      async function fetchContactMessages(id, hasMore = false) {
        setFetchingMessages(true)

        try {
          const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone
          const formData = new FormData()
          formData.append("time_zone", timeZone)
          formData.append("id", id)

          const response = await axios.post(
            baseUrl + "/api/admin/fetch-messages",
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
            
            let tempMessages = []
            if (hasMore) {
              tempMessages = [...messages]
            }

            for (let a = newMessages.length - 1; a >= 0; a--) {
              tempMessages.push(newMessages[a])
            }
            setMessages(tempMessages)

            let currentNotificationCount = document.getElementById("message-notification-badge").innerHTML ?? "0"
            currentNotificationCount = parseInt(currentNotificationCount)
            currentNotificationCount -= notificationsCount

            if (currentNotificationCount > 0) {
              document.getElementById("message-notification-badge").innerHTML = currentNotificationCount
            } else {
              document.getElementById("message-notification-badge").innerHTML = ""
            }

            const tempContacts = [...contacts]
            for (let a = 0; a < tempContacts.length; a++) {
              if (tempContacts[a].id == id) {
                tempContacts[a].user_notifications = 0
                break
              }
            }
            setContacts(tempContacts)

            setTimeout(function () {
              document.querySelector(".msg_history").scrollTop = document.querySelector(".msg_history").scrollHeight
            }, 100)
          } else {
            swal.fire("Error", response.data.message, "error")
          }
        } catch (exp) {
          swal.fire("Error", exp.message, "error")
        } finally {
          setFetchingMessages(false)
        }
      }

      async function sendMessage() {
        event.preventDefault()

        if (selectedContact <= 0) {
          swal.fire("Error", "Please select a user first.", "error")
          return
        }

        setSending(true)
        const form = event.target
        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone

        try {
          const formData = new FormData(form)
          formData.append("time_zone", timeZone)
          formData.append("id", selectedContact)

          const response = await axios.post(
            baseUrl + "/api/admin/send-message",
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
              document.querySelector(".msg_history").scrollTop = document.querySelector(".msg_history").scrollHeight
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

      return (
        <div className="messaging">
          <div className="inbox_msg">
            <div className="inbox_people">
              <div className="headind_srch">
                <div className="recent_heading">
                  <h4>Recent</h4>
                </div>
                <div className="srch_bar">
                  <div className="stylish-input-group">
                    <input type="text" className="search-bar" value={ search } onChange={ function () {
                      setSearch(event.target.value)
                    } } placeholder="Search" />
                    <span className="input-group-addon">
                      <button type="button" onClick={ onInit }> <i className="fa fa-search" aria-hidden="true"></i> </button>
                    </span>
                  </div>
                </div>
              </div>
              <div className="inbox_chat">

                {/*<div className="chat_list active_chat">
                  <div className="chat_people">
                    <div className="chat_img"> <img src="https://ptetutorials.com/images/user-profile.png" alt="sunil" /> </div>
                    <div className="chat_ib">
                      <h5>Sunil Rajput <span className="chat_date">Dec 25</span></h5>
                      <p>Test, which is a new approach to have all solutions 
                        astrology under one roof.</p>
                    </div>
                  </div>
                </div>*/}

                { contacts.map(function (contact) {
                  return (
                    <div className={`chat_list ${ contact.id == selectedContact ? "active_chat" : ""}`}
                      key={`contact-${ contact.id }`}
                      onClick={ function () {
                        setSelectedContact(contact.id)
                        fetchContactMessages(contact.id)
                      } }>
                      <div className="chat_people">
                        <div className="chat_img"> <img src={ contact.profile_image } alt={ contact.name } /> </div>
                        <div className="chat_ib">
                          <h5>{ contact.name } <span className="chat_date">{ contact.last_message_date }</span></h5>
                          <p>
                            { contact.last_message }

                            { contact.user_notifications > 0 && (
                              <span class="badge bg-info">{ contact.user_notifications }</span>
                            ) }
                          </p>
                        </div>
                      </div>
                    </div>
                  )
                }) }
                
              </div>
            </div>
            <div className="mesgs">
              <div className="msg_history">

                {/*<div className="incoming_msg">
                  <div className="incoming_msg_img"> <img src="https://ptetutorials.com/images/user-profile.png" alt="sunil" /> </div>
                  <div className="received_msg">
                    <div className="received_withd_msg">
                      <p>Test which is a new approach to have all
                        solutions</p>
                      <span className="time_date"> 11:01 AM    |    June 9</span></div>
                  </div>
                </div>

                <div className="outgoing_msg">
                  <div className="sent_msg">
                    <p>Test which is a new approach to have all
                      solutions</p>
                    <span className="time_date"> 11:01 AM    |    June 9</span> </div>
                </div>*/}

                { messages.map(function (m) {
                  return (
                    <React.Fragment key={`message-${ m.id }`}>
                      { m.sender_id == selectedContact ? (
                        <div className="incoming_msg">
                          <div className="received_msg">
                            <div className="received_withd_msg">
                              <p>
                                { m.message }
                                <Attachments message={ m } />
                              </p>
                              
                              <span className="time_date"> { m.created_at } </span></div>
                          </div>
                        </div>
                      ) : (
                        <div className="outgoing_msg">
                          <div className="sent_msg">
                            <p>
                              { m.message }
                              <Attachments message={ m } />
                            </p>
                            
                            <span className="time_date"> { m.created_at } </span> </div>
                        </div>
                      ) }
                    </React.Fragment>
                  )
                }) }

              </div>

              <form onSubmit={ sendMessage } encType="multipart/form-data">
                <div className="type_msg">
                  <div className="input_msg_write">

                    <div onClick={ function () {
                      document.getElementById("input-attachment-message").click()
                    } } style={ styles.attachmentContainer }>
                      <i className="fa fa-paperclip"></i>
                    </div>

                    <input type="file" style={ styles.hidden } id="input-attachment-message"
                      multiple
                      onChange={ attachmentSelected }
                      name="attachments[]" />

                    <input type="text" name="message" className="write_msg" placeholder="Type a message"
                      value={ message } onChange={ function () {
                        setMessage(event.target.value)
                      } } />
                    <button className="msg_send_btn" type="submit" disabled={ sending } style={ styles.btnSubmit }>
                      <i className="fa fa-paper-plane" aria-hidden="true"></i>
                    </button>
                  </div>
                </div>
              </form>

              { attachments.length > 0 && (
                <div style={ styles.selectedAttachmentContainer }>
                    { attachments.map(function (attachment) {
                        return (
                            <div key={`selected-attachment-${ attachment.name }`} style={ styles.selectedSingleAttachment }>
                                <div onClick={ function () {
                                    removeAttachment(attachment.name)
                                } }>
                                  <i className="fa fa-close" style={ styles.iconRemoveAttachment }></i>
                                </div>

                                <img src={ attachment.src } style={ styles.selectedImage } />
                            </div>
                        )
                    }) }
                </div>
            ) }

            </div>
          </div>
          
        </div>
      )
    }

    ReactDOM.createRoot(
      document.getElementById("messages-app")
    ).render(<Messages />)
  </script>

  <style>
    img{ max-width:100%;}
    #messages-app .chat_ib .badge {
      margin-left: 10px;
    }
    #messages-app .inbox_people {
      background: #f8f8f8 none repeat scroll 0 0;
      float: left;
      overflow: hidden;
      width: 40%; border-right:1px solid #c4c4c4;
    }
    #messages-app .inbox_msg {
      border: 1px solid #c4c4c4;
      clear: both;
      overflow: hidden;
    }
    #messages-app .top_spac{ margin: 20px 0 0;}


    #messages-app .recent_heading {float: left; width:40%;}
    #messages-app .srch_bar {
      display: inline-block;
      text-align: right;
      width: 60%;
    }
    #messages-app .headind_srch{ padding:10px 29px 10px 20px; overflow:hidden; border-bottom:1px solid #c4c4c4;}

    #messages-app .recent_heading h4 {
      color: #05728f;
      font-size: 21px;
      margin: auto;
    }
    #messages-app .srch_bar input{ border:1px solid #cdcdcd; border-width:0 0 1px 0; width:80%; padding:2px 0 4px 6px; background:none;}
    #messages-app .srch_bar .input-group-addon button {
      background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
      border: medium none;
      padding: 0;
      color: #707070;
      font-size: 18px;
    }
    #messages-app .srch_bar .input-group-addon { margin: 0 0 0 -27px;}

    #messages-app .chat_ib h5{ font-size:15px; color:#464646; margin:0 0 8px 0;}
    #messages-app .chat_ib h5 span{ font-size:13px; float:right;}
    #messages-app .chat_ib p{ font-size:14px; color:#989898; margin:auto}
    #messages-app .chat_img {
      float: left;
      width: 11%;
    }
    .chat_img img {
      border-radius: 50%;
    }
    #messages-app .chat_ib {
      float: left;
      padding: 0 0 0 15px;
      width: 88%;
    }

    #messages-app .chat_people{ overflow:hidden; clear:both;}
    #messages-app .chat_list {
      border-bottom: 1px solid #c4c4c4;
      margin: 0;
      padding: 18px 16px 10px;
      cursor: pointer;
    }
    #messages-app .inbox_chat { height: 550px; overflow-y: scroll;}

    #messages-app .active_chat{ background:#ebebeb;}

    #messages-app .incoming_msg_img {
      display: inline-block;
      width: 6%;
    }
    #messages-app .received_msg {
      display: inline-block;
      padding: 0 0 0 10px;
      vertical-align: top;
      width: 92%;
     }
     #messages-app .received_withd_msg p {
      background: #ebebeb none repeat scroll 0 0;
      border-radius: 3px;
      color: #646464;
      font-size: 14px;
      margin: 0;
      padding: 5px 10px 5px 12px;
      width: 100%;
    }
    #messages-app .time_date {
      color: #747474;
      display: block;
      font-size: 12px;
      margin: 8px 0 0;
    }
    #messages-app .received_withd_msg { width: 57%;}
    #messages-app .mesgs {
      float: left;
      padding: 30px 15px 0 25px;
      width: 60%;
    }

     #messages-app .sent_msg p {
      background: #05728f none repeat scroll 0 0;
      border-radius: 3px;
      font-size: 14px;
      margin: 0; color:#fff;
      padding: 5px 10px 5px 12px;
      width:100%;
    }
    #messages-app .outgoing_msg{ overflow:hidden; margin:26px 0 26px;}
    #messages-app .sent_msg {
      float: right;
      width: 46%;
    }
    #messages-app .input_msg_write {
      display: flex;
    }
    #messages-app .input_msg_write input {
      background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
      border: medium none;
      color: #4c4c4c;
      font-size: 15px;
      min-height: 48px;
      width: 100%;
    }

    #messages-app .type_msg {border-top: 1px solid #c4c4c4;position: relative;}
    #messages-app .msg_send_btn {
      background: #05728f none repeat scroll 0 0;
      border: medium none;
      border-radius: 50%;
      color: #fff;
      cursor: pointer;
      font-size: 17px;
      height: 33px;
      position: absolute;
      right: 0;
      top: 11px;
      width: 33px;
    }
    #messages-app .messaging { padding: 0 0 50px 0;}
    #messages-app .msg_history {
      height: 516px;
      overflow-y: auto;
    }
  </style>

@endsection
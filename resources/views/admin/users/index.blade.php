@extends ("admin/layouts/app")
@section ("title", "Users")

@section ("main")

  <div class="pagetitle">
    <div style="display: flex;">
      <h1>Users</h1>
      <a href="{{ url('/admin/users/add') }}" class="btn btn-outline-primary btn-sm"
        style="margin-left: 15px;">Add user</a>
    </div>

    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item active">Users</li>
      </ol>
    </nav>
  </div>

  <section class="section" id="users-app">

  </section>

  <script type="text/babel">
    function Users() {
      const styles = {
        profileImage: {
          width: "100px"
        },
        editBtn: {
          marginBottom: "10px"
        }
      }

      const [users, setUsers] = React.useState([])

      async function onInit() {
          try {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone

            const formData = new FormData()
            formData.append("time_zone", timeZone)

            const response = await axios.post(
              baseUrl + "/api/admin/users/fetch",
              formData,
              {
                headers: {
                  Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                }
              }
            )

            if (response.data.status == "success") {
              setUsers(response.data.users)
            } else {
              swal.fire("Error", response.data.message, "error")
            }
          } catch (exp) {
            swal.fire("Error", exp.message, "error")
          }
      }

      React.useEffect(function () {
          onInit()
      }, [])

      function deleteUser(id) {
        swal.fire({
          title: "Delete user: #" + id,
          text: "Are you sure you want to delete this user ?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!"
        }).then(async function (result) {
          if (result.isConfirmed) {
            try {
              const formData = new FormData()
              formData.append("id", id)

              const response = await axios.post(
                baseUrl + "/api/admin/users/delete",
                formData,
                {
                  headers: {
                    Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                  }
                }
              )

              if (response.data.status == "success") {
                const tempUsers = [...users]
                for (let a = 0; a < tempUsers.length; a++) {
                  if (tempUsers[a].id == id) {
                    tempUsers.splice(a, 1)
                  }
                }
                setUsers(tempUsers)
              } else {
                swal.fire("Error", response.data.message, "error")
              }
            } catch (exp) {
              swal.fire("Error", exp.message, "error")
            }
          }
        })
      }

      return (
        <div className="row">
          <div className="col-12">
            <table className="table table-bordered table-responsive">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Profile</th>
                  <th>Type</th>
                  <th>Registered at</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody>
                { users.map(function (user) {
                  return (
                    <tr key={`user-${ user.id }`}>
                      <td>{ user.name }</td>
                      <td>{ user.email }</td>
                      <td>
                        <img src={ user.profile_image } style={ styles.profileImage }
                          onError={function () {
                            event.target.remove()
                          }} />
                      </td>
                      <td>{ user.type }</td>
                      <td>{ user.created_at }</td>
                      <td>
                        <a href={`${ baseUrl }/admin/users/edit/${ user.id }`} style={ styles.editBtn } className="btn btn-outline-warning">Edit</a>&nbsp;
                        
                        <button type="button" className="btn btn-outline-danger" onClick={ function () {
                          deleteUser(user.id)
                        } }>Delete</button>
                      </td>
                    </tr>
                  )
                }) }
              </tbody>
            </table>
          </div>
        </div>
      )
    }

    ReactDOM.createRoot(
      document.getElementById("users-app")
    ).render(<Users />)
  </script>

@endsection
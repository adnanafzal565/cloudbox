@extends ("admin/layouts/app")
@section ("title", "Edit user")

@section ("main")

  <div class="pagetitle">
    <h1>Edit user</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Users</li>
        <li class="breadcrumb-item">Edit</li>
        <li class="breadcrumb-item active">{{ $id }}</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Profile information</h5>

            <form onsubmit="updateUser()" id="form-update-user">
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Name</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" name="name" required />
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Email</label>
                <div class="col-sm-10">
                  <input type="email" class="form-control" name="email" disabled />
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Type</label>
                <div class="col-sm-10">
                  <select class="form-control" name="type" required>
                    <option value="user">User</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Storage used</label>
                <div class="col-sm-10">
                  <input type="text" name="storage_used" class="form-control" disabled />
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Storage total (bytes)</label>
                
                <div class="col-sm-5">
                  <select class="form-control" name="storage_unit" required>
                    <option value="kb">KB</option>
                    <option value="mb">MB</option>
                    <option value="gb">GB</option>
                  </select>
                </div>

                <div class="col-sm-5">
                  <input type="text" name="storage_total" class="form-control" required />
                </div>
              </div>

              <input type="submit" name="submit" class="btn btn-outline-warning" value="Edit" />
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Change password</h5>

            <form onsubmit="changePassword()" id="form-change-password">
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">New password</label>
                <div class="col-sm-10">
                  <input type="password" class="form-control" name="password" required />
                </div>
              </div>

              <input type="submit" name="submit" class="btn btn-outline-info" value="Change password" />
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <input type="hidden" id="id" value="{{ $id }}" />

  <script>
    const id = document.getElementById("id").value

    async function changePassword() {
      event.preventDefault()

      const form = event.target
      form.submit.setAttribute("disabled", "disabled")

      const formData = new FormData(form)
      formData.append("id", id)

      try {
        const response = await axios.post(
          baseUrl + "/api/admin/users/change-password",
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
        form.submit.removeAttribute("disabled")
      }
    }

    async function updateUser() {
      event.preventDefault()

      const form = event.target
      form.submit.setAttribute("disabled", "disabled")

      const formData = new FormData(form)
      formData.append("id", id)

      try {
        const response = await axios.post(
          baseUrl + "/api/admin/users/update",
          formData,
          {
            headers: {
              Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
            }
          }
        )

        if (response.data.status == "success") {
          swal.fire("Update user", response.data.message, "success")
        } else {
          swal.fire("Error", response.data.message, "error")
        }
      } catch (exp) {
        swal.fire("Error", exp.message, "error")
      } finally {
        form.submit.removeAttribute("disabled")
      }
    }

    async function onInit() {
      const formData = new FormData()
      formData.append("id", id)

      try {
        const response = await axios.post(
          baseUrl + "/api/admin/users/fetch/" + id,
          formData,
          {
            headers: {
              Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
            }
          }
        )

        if (response.data.status == "success") {
          const user = response.data.user
          const form = document.getElementById("form-update-user")
          form.name.value = user.name
          form.email.value = user.email
          form.type.value = user.type
          form.storage_used.value = bytesToSize(user.storage_used)
          form.storage_total.value = bytesOnlyToSize(user.storage_total)
          form.storage_unit.value = bytesToUnit(user.storage_total)?.toLowerCase() || ""
        } else {
          swal.fire("Error", response.data.message, "error")
        }
      } catch (exp) {
        swal.fire("Error", exp.message, "error")
      }
    }
  </script>

@endsection
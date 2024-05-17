@extends ("admin/layouts/app")
@section ("title", "Add user")

@section ("main")

  <div class="pagetitle">
    <h1>Add user</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Users</li>
        <li class="breadcrumb-item active">Add</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">User information</h5>

            <form onsubmit="addUser()" id="form-add-user">
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Name</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" name="name" required />
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Email</label>
                <div class="col-sm-10">
                  <input type="email" class="form-control" name="email" required />
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Password</label>
                <div class="col-sm-10">
                  <input type="password" class="form-control" name="password" required />
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

              <input type="submit" name="submit" class="btn btn-outline-primary" value="Add" />
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>

    async function addUser() {
      event.preventDefault()

      const form = event.target
      const formData = new FormData(form)
      form.submit.setAttribute("disabled", "disabled")

      try {
        const response = await axios.post(
          baseUrl + "/api/admin/users/add",
          formData,
          {
            headers: {
              Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
            }
          }
        )

        if (response.data.status == "success") {
          swal.fire("Add user", response.data.message, "success")
        } else {
          swal.fire("Error", response.data.message, "error")
        }
      } catch (exp) {
        swal.fire("Error", exp.message, "error")
      } finally {
        form.submit.removeAttribute("disabled")
      }
    }
  </script>

@endsection
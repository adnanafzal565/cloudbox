<ul class="list-group profile-left-menu">
    <li class="list-group-item">
        <a href="{{ url('/profile') }}">Profile</a>
    </li>

    <li class="list-group-item">
        <a href="{{ url('/change-password') }}">Change password</a>
    </li>
</ul>

<style>
    .profile-left-menu .active a {
        color: white !important;
    }
    .profile-left-menu a:hover {
        text-decoration: underline;
    }
    .profile-left-menu a {
        text-decoration: none;
        color: black;
    }
</style>
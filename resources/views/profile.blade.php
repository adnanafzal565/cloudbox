@extends ("layouts/app")
@section ("title", "Profile")

@section ("main")

    <div class="container" style="margin-top: 30px; margin-bottom: 30px;">
        <div class="row">
            <div class="col-4">
                @include ("layouts/profile-left-menu")
            </div>

            <div class="col-8" id="profile-app">
                
            </div>
        </div>
    </div>

    <script type="text/babel" src="{{ asset('/components/Profile.js?v=' . time()) }}"></script>

@endsection
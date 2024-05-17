@extends ("cloudbox/app")
@section ("title", "Home")

@section ("main")

    <div class="container-fluid" id="home-app">
        
    </div>

    <script type="text/babel" src="{{ asset('/cloud-box/components/Home.js?v=' . time()) }}"></script>

@endsection
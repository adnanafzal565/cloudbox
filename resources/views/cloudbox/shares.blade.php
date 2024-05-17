@extends ("cloudbox/app")
@section ("title", "Shared with me")

@section ("main")

    <div class="container-fluid" id="shares-app">
        
    </div>

    <script type="text/babel" src="{{ asset('/cloud-box/components/Shares.js?v=' . time()) }}"></script>

@endsection
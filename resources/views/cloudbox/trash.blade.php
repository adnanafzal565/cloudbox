@extends ("cloudbox/app")
@section ("title", "Trash")

@section ("main")

    <div class="container-fluid" id="trash-app">
        
    </div>

    <script type="text/babel" src="{{ asset('/cloud-box/components/Trash.js?v=' . time()) }}"></script>

@endsection
@extends ("cloudbox/app")
@section ("title", "All files")

@section ("main")

    <div class="container-fluid" id="all-files-app">
        
    </div>

    <script type="text/babel" src="{{ asset('/cloud-box/components/AllFiles.js?v=' . time()) }}"></script>

@endsection
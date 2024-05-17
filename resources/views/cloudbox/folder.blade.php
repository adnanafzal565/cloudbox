@extends ("cloudbox/app")
@section ("title", "Folder")

@section ("main")

    <input type="hidden" id="folder-id" value="{{ $id }}" />

    <div class="container-fluid" id="folder-app">
        
    </div>

    <script type="text/babel" src="{{ asset('/cloud-box/components/Folder.js?v=' . time()) }}"></script>

@endsection
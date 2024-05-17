@extends ("cloudbox/app")
@section ("title", "File detail")

@section ("main")

    <input type="hidden" id="file-detail-id" value="{{ $id }}" />

    <div class="container-fluid" id="detail-app">
        
    </div>

    <script type="text/babel" src="{{ asset('/cloud-box/components/Detail.js?v=' . time()) }}"></script>

@endsection
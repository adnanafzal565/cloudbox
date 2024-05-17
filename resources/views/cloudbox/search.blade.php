@extends ("cloudbox/app")
@section ("title", "Search '" . $q . "'")

@section ("main")

    <input type="hidden" id="q" value="{{ $q }}" />

    <div class="container-fluid" id="search-app">
        
    </div>

    <script type="text/babel" src="{{ asset('/cloud-box/components/Search.js?v=' . time()) }}"></script>

@endsection
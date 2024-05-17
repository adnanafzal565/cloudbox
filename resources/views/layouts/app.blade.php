<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield("title", "Laravel authentication")</title>

        <link rel="stylesheet" href="{{ asset('/css/bootstrap.css') }}" />
        <link rel="stylesheet" href="{{ asset('/css/fontawesome.css') }}" />
        <script src="{{ asset('/js/jquery.js') }}"></script>
        <script src="{{ asset('/js/bootstrap.js') }}"></script>

        <script src="{{ asset('/js/react.development.js') }}"></script>
        <script src="{{ asset('/js/react-dom.development.js') }}"></script>
        <script src="{{ asset('/js/babel.min.js') }}"></script>
        <script src="{{ asset('/js/axios.min.js') }}"></script>
        <script src="{{ asset('/js/sweetalert2@11.js') }}"></script>
        <script src="{{ asset('/js/fontawesome.js') }}"></script>
    </head>

    <body>
    	<input type="hidden" id="baseUrl" value="{{ url('/') }}" />
    	<input type="hidden" id="appName" value="{{ config('config.app_name') }}" />

    	<script>
    		const baseUrl = document.getElementById("baseUrl").value
    		const appName = document.getElementById("appName").value    		
    	</script>
        
        <script src="{{ asset('/js/script.js?v=' . time()) }}"></script>
        
        <nav class="navbar navbar-expand-lg navbar-light bg-light" id="header-app">
          	
        </nav>

        <script type="text/babel" src="{{ asset('/components/Header.js?v=' . time()) }}"></script>

        <main class="flex-shrink-0">
            @yield("main")
        </main>

        <footer class="footer mt-auto py-3 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center">
                        <span class="text-muted">Copyright {{ now()->year }}. All right reserved.</span>                        
                    </div>
                </div>
            </div>
        </footer>

        <div id="chat-app"></div>
        <script type="text/babel" src="{{ asset('/components/Chat.js?v=' . time()) }}"></script>
        <link rel="stylesheet" href="{{ asset('/css/chat.css') }}" />
    </body>
</html>
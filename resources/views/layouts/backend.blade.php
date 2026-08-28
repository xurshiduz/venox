<!DOCTYPE html>
<html lang="zxx" class="js">

<head>
    <meta charset="utf-8">
    <meta name="author" content="{{ config('app.name', 'Laravel') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="{{ config('app.name', 'Laravel') }}">
    <link rel="shortcut icon" href="/favicon.ico">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/backend/assets/css/dashlite.css?ver=3.0.3">
    <link id="skin-default" rel="stylesheet" href="/backend/assets/css/theme.css?ver=3.0.3">
    
    @yield('css')
</head>

<body class="nk-body bg-lighter npc-general has-sidebar {{ Auth::user()->dark_mode ? 'dark-mode' : '0' }}">
    <div class="nk-app-root">
        <div class="nk-main ">
            @include('backend.layouts.sidebar')
            <div class="nk-wrap ">
                @include('backend.layouts.main')
                @yield('content')
                <div class="nk-footer">
                    <div class="container-fluid">
                        <div class="nk-footer-wrap">
                            <div class="nk-footer-copyright"> &copy; 1999-{{ Carbon\Carbon::now()->format('Y') }} {{ config('app.name', 'Laravel') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/backend/assets/js/bundle.js?ver=3.0.3"></script>
    <script src="/backend/assets/js/scripts.js?ver=3.0.3"></script>

    <script src="/backend/js/jquery.repeater.min.js"></script>
    <script src="/backend/js/form-repeater.js"></script>
    <script src="/js/filter.js"></script>
    
    @yield('script')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#iscompact').click(function(){
            var element = document.getElementById("div1");
            var elementtwo = document.getElementById("div2");
            
            $.ajax({
                type: "POST",
                url: '{{ route("iscompact") }}',
                dataType: 'JSON',
                data: {'compact': null},
                success: function(data) {
                    if (data.compact == 1) {
                        element.classList.remove("otherclass");
                        elementtwo.classList.remove("otherclass");
                    } else {
                        element.classList.add("otherclass");
                        elementtwo.classList.add("otherclass");
                    }
                },
                error: function(ajaxContext) {
                    alert(ajaxContext.responseText)
                }
            });
        });
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Chat Application')</title>
    
    <link rel="stylesheet" href="{{ asset('build/assets/app-DCdfOBS7.css') }}">
    <script src="{{ asset('build/assets/app-DZWHABEc.js') }}" defer></script>
    <script>
        console.log('Layout script loaded');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            console.log('App element:', document.getElementById('app'));
        });
    </script>
    
    @livewireStyles
    <script src="http://boostly.test/js/widget/chat-widget.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        @yield('content')
    </div>
</body>
</html> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Chat Application')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    <script src="http://boostly.test/js/widget/chat-widget.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        @yield('content')
    </div>
</body>
</html> 
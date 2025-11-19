<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>React Integration Test</title>
    @vite(['resources/css/app.css', 'resources/js/react-app.jsx'])
</head>
<body class="antialiased bg-gray-100">
    <div id="react-root"></div>
</body>
</html>

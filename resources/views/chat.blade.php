<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    @livewireStyles
</head>
<body style="margin: 0px;">
    
    <livewire:chat-component 
        :widget="$widget"
        :widget-action="$widgetAction"
        :media="$media"
        :widget-style="$widgetStyle"
    />
    
    @livewireScripts
</body>
</html> 
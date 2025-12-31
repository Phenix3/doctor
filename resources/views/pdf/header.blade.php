<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 5px 0;
        }
        
        .header-content {
            text-align: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .header-title {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .header-subtitle {
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header-content">
        @if(isset($title))
            <div class="header-title">{{ $title }}</div>
        @endif
        
        @if(isset($subtitle))
            <div class="header-subtitle">{{ $subtitle }}</div>
        @endif
        
        @if(isset($date))
            <div class="header-subtitle">{{ $date }}</div>
        @endif
    </div>
</body>
</html>
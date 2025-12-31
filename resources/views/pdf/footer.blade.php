<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 5px 0;
        }
        
        .footer-content {
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            color: #666;
        }
        
        .footer-text {
            margin-bottom: 2px;
        }
        
        .page-number {
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="footer-content">
        @if(isset($text))
            <div class="footer-text">{{ $text }}</div>
        @endif
        
        <div class="page-number">
            Page <span class="pageNumber"></span> sur <span class="totalPages"></span>
        </div>
    </div>
</body>
</html>
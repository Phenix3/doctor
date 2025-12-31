<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Administratif</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .document-container {
            padding: 20px;
            max-width: 100%;
        }
        
        .document-content {
            white-space: pre-line;
            word-wrap: break-word;
        }
        
        /* Administrative document styling */
        .header-section {
            margin-bottom: 30px;
            text-align: right;
        }
        
        .object-section {
            margin: 20px 0;
            font-weight: bold;
        }
        
        .body-section {
            margin: 20px 0;
            text-align: justify;
        }
        
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        
        .politeness-formula {
            margin: 30px 0;
        }
        
        /* Page break handling */
        .page-break {
            page-break-before: always;
        }
        
        /* Ensure proper spacing */
        p {
            margin: 10px 0;
        }
        
        /* Watermark styling */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            color: rgba(204, 204, 204, 0.1);
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="document-container">
        <div class="document-content">
            {!! $content !!}
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Backup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #e9ecef;
        }
        .message {
            margin-bottom: 25px;
            font-size: 16px;
        }
        .download-button {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            margin-top: 10px;
        }
        .download-button:hover {
            background-color: #0056b3;
        }
        .link-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 20px;
            word-break: break-all;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message">
            {{ $bodyText }}
        </div>

        <a href="{{ $link }}" class="download-button">Download Backup</a>

        <div class="link-text">
            Or copy this link: <br>
            {{ $link }}
        </div>

        <div class="footer">
            This is an automated message from your backup system. Please do not reply to this email.
        </div>
    </div>
</body>
</html>

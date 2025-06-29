<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
            border-radius: 0 0 5px 5px;
        }
        .field {
            margin-bottom: 20px;
        }
        .field-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .field-value {
            background-color: white;
            padding: 10px;
            border-radius: 3px;
            border: 1px solid #ced4da;
        }
        .message-box {
            min-height: 100px;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Contact Form Submission</h1>
    </div>
    
    <div class="content">
        <div class="field">
            <div class="field-label">Full Name:</div>
            <div class="field-value">{{ $contactData['fullName'] }}</div>
        </div>
        
        <div class="field">
            <div class="field-label">Email Address:</div>
            <div class="field-value">{{ $contactData['email'] }}</div>
        </div>
        
        <div class="field">
            <div class="field-label">Message:</div>
            <div class="field-value message-box">{{ $contactData['message'] }}</div>
        </div>
        
        <div class="field">
            <div class="field-label">Submitted At:</div>
            <div class="field-value">{{ $contactData['submitted_at']->format('F j, Y \a\t g:i A') }}</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This email was sent from your website's contact form.</p>
        <p>You can reply directly to this email to respond to {{ $contactData['fullName'] }}.</p>
    </div>
</body>
</html>
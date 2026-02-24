<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 20px -30px;
        }
        .content {
            padding: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .patient-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>labo.dz</h1>
            <h2>{{ $subject }}</h2>
        </div>
        
        <div class="content">
            <div class="patient-info">
                <p><strong>السيد/ة:</strong> {{ $patient->name }}</p>
                <p><strong>البريد الإلكتروني:</strong> {{ $patient->email }}</p>
            </div>
            
            <div class="message-content">
                {!! nl2br(e($content)) !!}
            </div>
        </div>
        
        <div class="footer">
            <p>مع تحيات،<br>فريق labo.dz</p>
            <p>هذا البريد الإلكتروني مرسل تلقائياً، يرجى عدم الرد عليه</p>
        </div>
    </div>
</body>
</html>
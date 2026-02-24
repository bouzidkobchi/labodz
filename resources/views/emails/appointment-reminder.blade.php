<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تذكير بموعد التحليل</title>
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
            background: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 20px -30px;
        }
        .content {
            padding: 20px 0;
        }
        .appointment-details {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .important-note {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ffeaa7;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .highlight {
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>labo.dz</h1>
            <h2>تذكير بموعد التحليل الطبي</h2>
        </div>

        <div class="content">
            <p>السيد/ة <span class="highlight">{{ $patient->name }}</span>،</p>

            <p>نود تذكيركم بموعد تحليلكم الطبي المقرر غداً.</p>

            <div class="appointment-details">
                <h3>تفاصيل الموعد:</h3>
                <p><strong>التحاليل المطلوبة:</strong></p>
                <ul>
                    @foreach($analyses as $analysis)
                        <li>{{ $analysis->name }}</li>
                    @endforeach
                </ul>
                <p><strong>التاريخ:</strong> {{ $appointment_date }}</p>
                <p><strong>الوقت:</strong> {{ $appointment_time }}</p>
                @if($patient->phone)
                <p><strong>رقم الهاتف:</strong> {{ $patient->phone }}</p>
                @endif
            </div>

            <div class="important-note">
                <h4>ملاحظات مهمة:</h4>
                <ul>
                    <li>يرجى الحضور قبل 15 دقيقة من الموعد المحدد</li>
                    <li>احضر بطاقة الهوية الشخصية</li>
                    <li>اتبع تعليمات الاستعداد الخاصة بالتحليل</li>
                    <li>في حالة عدم القدرة على الحضور، يرجى إبلاغنا مسبقاً</li>
                </ul>
            </div>

            <p>إذا كان لديكم أي استفسار، فلا تترددوا في الاتصال بنا.</p>
        </div>

        <div class="footer">
            <p>مع تحيات،<br>فريق labo.dz</p>
            <p>هذا البريد الإلكتروني مرسل تلقائياً، يرجى عدم الرد عليه</p>
            <p>للتواصل معنا: {{ config('mail.from.address') }}</p>
        </div>
    </div>
</body>
</html>

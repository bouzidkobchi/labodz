<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد حجز التحليل</title>
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
            background: #2c5282;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 20px -30px;
        }
        .content {
            padding: 20px 0;
        }
        .booking-details {
            background: #ebf8ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-right: 4px solid #2c5282;
        }
        .analyses-list {
            background: #f0fff4;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
            border-right: 4px solid #28a745;
        }
        .important-note {
            background: #fffaf0;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ecc94b;
            margin: 20px 0;
        }
        .pdf-note {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            border: 2px dashed #28a745;
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
            color: #2c5282;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>labo.dz</h1>
            <h2>تأكيد حجز التحليل الطبي</h2>
        </div>

        <div class="content">
            <p>السيد/ة <span class="highlight">{{ $patientName }}</span>،</p>

            <p>يسعدنا إبلاغكم بأنه تم <strong>تأكيد حجزكم</strong> للتحاليل الطبية بنجاح.</p>

            <div class="booking-details">
                <h3>تفاصيل الحجز:</h3>
                <p><strong>رقم الحجز:</strong> #{{ $reservationId }}</p>
                <p><strong>تاريخ الموعد:</strong> {{ $analysisDate }}</p>
                <p><strong>الوقت:</strong> {{ $analysisTime }}</p>
            </div>

            <div class="analyses-list">
                <h3>التحاليل المطلوبة:</h3>
                <ul>
                    @foreach($analyses as $analysis)
                        <li>{{ $analysis->name }}</li>
                    @endforeach
                </ul>
            </div>

            @if(isset($smartMessage) && $smartMessage)
            <div class="important-note" style="border: 2px solid #2c5282; background: #fff5f5;">
                <h4 style="color: #c53030;">💡 إرشاد هام للتحضير:</h4>
                <p style="font-size: 16px; font-weight: bold;">{{ $smartMessage }}</p>
            </div>
            @endif

            <div class="pdf-note">
                <p><strong>📎 ملف التحضير مرفق مع هذا البريد الإلكتروني</strong></p>
                <p>يرجى الاطلاع على المرفق للحصول على تعليمات التحضير الكاملة</p>
            </div>

            <div class="important-note">
                <h4>⚠️ ملاحظات مهمة:</h4>
                <ul>
                    <li>يرجى الحضور قبل 15 دقيقة من الموعد المحدد</li>
                    <li>احضر بطاقة الهوية الشخصية</li>
                    <li>اتبع تعليمات التحضير المرفقة بعناية</li>
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

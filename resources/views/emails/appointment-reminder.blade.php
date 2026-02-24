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
        .appointment-details {
            background: #ebf8ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-right: 4px solid #2c5282;
        }
        /* Preparation instructions section */
        .prep-section {
            background: #fffaf0;
            border: 2px solid #ecc94b;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .prep-section h3 {
            color: #b7791f;
            text-align: center;
            margin-top: 0;
            font-size: 16px;
        }
        .prep-item {
            background: white;
            border-right: 5px solid #ecc94b;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .prep-item .analysis-name {
            font-weight: bold;
            color: #2c5282;
            display: block;
            margin-bottom: 5px;
        }
        .prep-item .instruction {
            color: #4a5568;
            font-size: 14px;
        }
        .no-prep {
            color: #718096;
            font-size: 13px;
            text-align: center;
            font-style: italic;
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
            color: #2c5282;
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

            <p>نذكّركم بأن موعد تحاليلكم الطبية بعد <strong>14 ساعة تقريباً</strong>. يرجى الاطلاع على تعليمات التحضير أدناه.</p>

            <div class="appointment-details">
                <h3>تفاصيل الموعد:</h3>
                <p><strong>التاريخ:</strong> {{ $appointment_date }}</p>
                <p><strong>الوقت:</strong> {{ $appointment_time }}</p>
                @if($patient->phone)
                <p><strong>رقم الهاتف:</strong> {{ $patient->phone }}</p>
                @endif
                <p><strong>التحاليل المطلوبة:</strong></p>
                <ul>
                    @foreach($analyses as $analysis)
                        <li>{{ $analysis->name }}</li>
                    @endforeach
                </ul>
            </div>

            {{-- Preparation instructions per analysis --}}
            @php
                $analysesWithPrep = $analyses->filter(fn($a) => !empty($a->preparation_instructions));
            @endphp

            @if($analysesWithPrep->isNotEmpty())
            <div class="prep-section">
                <h3>⚠️ تعليمات التحضير للتحاليل</h3>
                <p style="text-align:center; color:#744210; margin-bottom:15px; font-size:13px;">
                    يرجى اتباع هذه التعليمات بدقة لضمان دقة النتائج
                </p>
                @foreach($analysesWithPrep as $analysis)
                <div class="prep-item">
                    <span class="analysis-name">• {{ $analysis->name }}</span>
                    <span class="instruction">{{ $analysis->preparation_instructions }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="prep-section">
                <h3>تعليمات التحضير</h3>
                <p class="no-prep">لا توجد متطلبات تحضير خاصة لهذه التحاليل</p>
            </div>
            @endif

            <div class="important-note">
                <h4>ملاحظات عامة:</h4>
                <ul>
                    <li>يرجى الحضور قبل 15 دقيقة من الموعد المحدد</li>
                    <li>احضر بطاقة الهوية الشخصية</li>
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

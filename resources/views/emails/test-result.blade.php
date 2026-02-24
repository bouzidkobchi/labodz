<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتيجة التحليل</title>
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
        .result-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .analysis-details {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .notes {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-right: 4px solid #ffc107;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>labo.dz</h1>
            <h2>نتيجة التحليل</h2>
        </div>
        
        <div class="content">
            <div class="result-info">
                <p><strong>اسم المريض:</strong> {{ $patient->name }}</p>
                <p><strong>تاريخ الحجز:</strong> {{ $reservation->analysis_date }}</p>
                <p><strong>وقت الحجز:</strong> {{ $reservation->time }}</p>
            </div>
            
            <div class="analysis-details">
                <h3>التحاليل المشمولة في هذه النتيجة:</h3>
                <ul>
                    @foreach($reservation->reservationAnalyses as $ra)
                        <li>
                            <strong>{{ $ra->analyse->name }}</strong>
                            @if($ra->analyse->normal_range)
                                <br><small>المعدل الطبيعي: {{ $ra->analyse->normal_range }}</small>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            
            @if($additional_notes)
            <div class="notes">
                <h3>ملاحظات إضافية:</h3>
                <p>{{ $additional_notes }}</p>
            </div>
            @endif
            
            <div class="instructions">
                <h3>توصيات:</h3>
                <p>• يرجى مراجعة الطبيب المختص لتحليل النتائج</p>
                <p>• الحفاظ على مواعيد المتابعة الدورية</p>
                <p>• الالتزام بتعليمات الطبيب فيما يتعلق بالعلاج أو النظام الغذائي</p>
            </div>
        </div>
        
        <div class="footer">
            <p>مع تحيات،<br>فريق labo.dz</p>
            <p>هذا البريد الإلكتروني مرسل تلقائياً، يرجى عدم الرد عليه</p>
        </div>
    </div>
</body>
</html>
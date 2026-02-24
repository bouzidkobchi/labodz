<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معلومات التحاليل - labo.dz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>

<body>
    <!-- Global Dynamic Background -->
    <div class="global-bg">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Navigation -->
    <nav>
        <ul class="nav-links">
            <li><a href="/"><i class="fas fa-home"></i> الرئيسية</a></li>
            <li><a href="/#analysis"><i class="fas fa-flask"></i> التحاليل</a></li>
            <li><a href="/#booking"><i class="fas fa-calendar-check"></i> حجز موعد</a></li>
            <li><a href="/#contact"><i class="fas fa-envelope"></i> اتصل بنا</a></li>
        </ul>
    </nav>

    <!-- Analysis Info Section -->
    <section class="section">
        <div class="container">
            <h1><i class="fas fa-flask"></i> معلومات التحاليل ونصائح الإعداد</h1>
            <p>فيما يلي معلومات مفصلة حول التحاليل المختلفة ونصائح الإعداد لكل منها</p>

            <div class="analysis-info-grid">
                @forelse($analyses as $analyse)
                <div class="info-card">
                    <h3><i class="fas fa-vial"></i> {{ $analyse->name }}</h3>
                    <div class="info-content">
                        <h4>حالة التحليل:</h4>
                        <p>
                            @if($analyse->availability == 1)
                            <span class="status-badge available-status">متوفر</span>
                            @else
                            <span class="status-badge unavailable-status">غير متوفر</span>
                            @endif
                        </p>
                        <h4>نصائح الإعداد:</h4>
                        {{$analyse->preparation_instructions ?? 'لا توجد نصائح إعدادية'}}

                        <h4>ملاحظات إضافية:</h4>
                        <p>{{ $analyse->description ?? 'لا توجد معلومات إضافية' }}</p>

                        <h4><i class="fas fa-clock"></i> المدة:</h4>
                        <p>{{ $analyse->duration }}</p>

                        <h4><i class="fas fa-money-bill-wave"></i> السعر:</h4>
                        <p class="price">{{ number_format($analyse->price, 2) }} دج</p>
                    </div>
                </div>
                @empty
                <div class="info-card">
                    <h3><i class="fas fa-vial"></i> لا توجد تحاليل متاحة</h3>
                    <div class="info-content">
                        <p>لا توجد أي تحاليل مسجلة في النظام حاليًا.</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Back to Home Button -->
    <section class="section">
        <div class="container text-center">
            <a href="/" class="cta-button"><i class="fas fa-arrow-right"></i> العودة إلى الصفحة الرئيسية</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>labo.dz</h3>
                <p>نقدم خدمات تحليلية دقيقة باستخدام أحدث التقنيات الطبية والكوادر المؤهلة لتقديم أفضل خدمة للمرضى.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>روابط سريعة</h3>
                <a href="/">الرئيسية</a>
                <a href="/#analysis">التحاليل</a>
                <a href="/#booking">حجز موعد</a>
                <a href="/#contact">اتصل بنا</a>
            </div>
            <div class="footer-section">
                <h3>معلومات الاتصال</h3>
                <p><i class="fas fa-map-marker-alt"></i> العنوان: labo.dz</p>
                <p><i class="fas fa-phone"></i> الهاتف: 0550123456</p>
                <p><i class="fas fa-envelope"></i> البريد: info@labo-dz.com</p>
                <p><i class="fas fa-clock"></i> أوقات العمل: 8:00 - 18:00</p>
            </div>
        </div>
        <div class="copyright">
            <p>جميع الحقوق محفوظة &copy; 2023 labo.dz - Labo_dz</p>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.lab_name') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        .btn-lang {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-lang:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-2px);
        }
        .lang-switcher-public {
            pointer-events: auto;
        }
        /* Fix for direction-dependent icons */
        [dir="ltr"] .fa-arrow-left { transform: rotate(180deg); }
        [dir="rtl"] .fa-arrow-right { transform: rotate(180deg); }

        .preparation-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid var(--accent-color);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-right: 5px solid var(--accent-color);
        }
        .preparation-box h3 {
            margin-top: 0;
            font-size: 1.1rem;
            color: var(--accent-color);
        }
        .preparation-box ul {
            margin-bottom: 0;
            padding-right: 20px;
        }
        .ocr-status {
            margin-top: 10px;
            font-weight: bold;
            color: var(--accent-color);
        }
        .fa-spinner {
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .highlight-found {
            animation: highlightPulse 2s ease;
            background-color: rgba(0, 255, 127, 0.2) !important;
            border-color: #00ff7f !important;
        }
        @keyframes highlightPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            z-index: 9999;
            display: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .notification.success { background: rgba(0, 255, 127, 0.8); border: 1px solid #00ff7f; }
        .notification.error { background: rgba(255, 69, 0, 0.8); border: 1px solid #ff4500; }
        .notification.info { background: rgba(30, 144, 255, 0.8); border: 1px solid #1e90ff; }
        .notification.warning { background: rgba(255, 165, 0, 0.8); border: 1px solid #ffa500; }

        /* Preparation Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            padding: 20px;
        }
        .prep-modal {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            overflow: hidden;
            animation: modalFadeIn 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
        }
        @keyframes modalFadeIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .prep-modal-header {
            background: var(--primary-color);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .prep-modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .prep-modal-body {
            padding: 30px;
            max-height: 70vh;
            overflow-y: auto;
        }
        .prep-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-right: 5px solid var(--accent-color);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .prep-item:hover {
            transform: scale(1.02);
        }
        .prep-item h4 {
            margin: 0 0 10px 0;
            color: var(--primary-color);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .prep-item p {
            margin: 0;
            line-height: 1.6;
            color: #4a5568;
        }
        .prep-modal-footer {
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        .close-prep-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(49, 130, 206, 0.3);
        }
        .close-prep-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(49, 130, 206, 0.4);
            background: #2b6cb0;
        }
        .prep-icon {
            font-size: 2rem;
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        .important-badge {
            background: #fed7d7;
            color: #c53030;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>
<script src="{{ asset('js/app.js') }}"></script>

<body>
    <!-- Global Dynamic Background -->
    <div class="global-bg">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Notification -->
    <div id="notification" class="notification"></div>

    <!-- Preparation Instructions Modal -->
    <div id="prepModalOverlay" class="modal-overlay">
        <div class="prep-modal">
            <div class="prep-modal-header">
                <i class="fas fa-exclamation-triangle prep-icon" style="color: white; margin-bottom: 10px;"></i>
                <h2>{{ __('messages.important_preparation_instructions') }}</h2>
            </div>
            <div class="prep-modal-body">
                <p class="text-center mb-4" style="color: #4a5568; font-weight: 500;">
                    {{ __('messages.prep_modal_intro') }}
                </p>
                <div id="prepContainer">
                    @if(session('preparations'))
                        @foreach(session('preparations') as $prep)
                            <div class="prep-item">
                                <span class="important-badge"><i class="fas fa-biohazard"></i> {{ __('messages.required') }}</span>
                                <h4><i class="fas fa-flask"></i> {{ $prep['name'] }}</h4>
                                <p>{{ $prep['instructions'] }}</p>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="prep-modal-footer">
                <button class="close-prep-btn" id="closePrepModal">{{ __('messages.i_understand') }}</button>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Navigation -->
    <nav>
        <div class="nav-container">
            <div class="logo">
                <a href="#home">{{ __('messages.lab_name') }}</a>
            </div>
            
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>

            <div class="lang-switcher-public">
                @if(app()->getLocale() == 'ar')
                    <a href="{{ route('lang.switch', 'fr') }}" class="btn-lang">Français</a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}" class="btn-lang">العربية</a>
                @endif
            </div>

            <ul class="nav-links" id="navLinks">
                <li><a href="#home"><i class="fas fa-home"></i> {{ __('messages.home') }}</a></li>
                <li><a href="#features"><i class="fas fa-star"></i> {{ __('messages.features') }}</a></li>
                <li><a href="#analysis"><i class="fas fa-flask"></i> {{ __('messages.analysis') }}</a></li>
                <li><a href="#tips"><i class="fas fa-lightbulb"></i> {{ __('messages.tips') }}</a></li>
                <li><a href="#booking"><i class="fas fa-calendar-check"></i> {{ __('messages.booking') }}</a></li>
                <li><a href="{{ route('access') }}" class="portal-nav-link text-warning fw-bold"><i class="fas fa-lock"></i> {{ __('messages.patient_portal') }}</a></li>
                <li><a href="#contact"><i class="fas fa-envelope"></i> {{ __('messages.contact') }}</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <h1>{{ __('messages.hero_title') }}</h1>
        <p>{{ __('messages.hero_subtitle') }}</p>
        <a href="#booking" class="cta-button">{{ __('messages.book_now') }} <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}"></i></a>
    </section>

    <!-- Features Section -->
    <section id="features" class="section">
        <div class="container">
            <h2><i class="fas fa-star"></i> {{ __('messages.our_features') }}</h2>
            <p>{{ __('messages.features_desc') }}</p>

            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-microscope"></i>
                    <h3>{{ __('messages.modern_equipment') }}</h3>
                    <p>{{ __('messages.modern_equipment_desc') }}</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-user-md"></i>
                    <h3>{{ __('messages.medical_expertise') }}</h3>
                    <p>{{ __('messages.medical_expertise_desc') }}</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bolt"></i>
                    <h3>{{ __('messages.fast_results') }}</h3>
                    <p>{{ __('messages.fast_results_desc') }}</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-headset"></i>
                    <h3>{{ __('messages.continuous_support') }}</h3>
                    <p>{{ __('messages.continuous_support_desc') }}</p>
                </div>
            </div>
        </div>
    </section>


    <!-- Analysis Section -->
    <section id="analysis" class="section">
        <div class="container">
            <h2><i class="fas fa-flask"></i> {{ __('messages.available_analyses_list') }}</h2>
            <p>{{ __('messages.available_analyses_desc') }}</p>
            <div class="analysis-list">
                @foreach($analyses as $analysis)
                <div class="analysis-item">
                    <span>{{ $analysis->name }}</span>
                    @if($analysis->availability == 1)
                    <button class='status-btn available'>{{ __('messages.available') }}</button>
                    @else
                    <button class='status-btn unavailable'>{{ __('messages.unavailable') }}</button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Analysis Info Button Section -->
    <section id="tips" class="section">
        <div class="container">
            <h2><i class="fas fa-lightbulb"></i> {{ __('messages.tips_title') }}</h2>
            <p>{{ __('messages.tips_desc') }}</p>
            <a href="{{ route('analysis.info') }}" class="cta-button">{{ __('messages.view_analysis_info') }} <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}"></i></a>
        </div>
    </section>

    <!-- Booking Section -->
    <section id="booking" class="section">
        <div class="container">
            <h2><i class="fas fa-calendar-check"></i> {{ __('messages.booking') }}</h2>
            <p>{{ __('messages.booking_desc') }}</p>
            <form id="bookingForm" action="{{ route('booking') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="name">{{ __('messages.full_name') }}</label>
                    <input type="text" id="name" name="name" placeholder="{{ __('messages.full_name') }}" required>
                </div>
                <div class="form-group">
                    <label for="phone">{{ __('messages.phone_number') }}</label>
                    <input type="tel" id="phone" name="phone" placeholder="{{ __('messages.phone_number') }}" required>
                </div>
                <div class="form-group">
                    <label for="email">{{ __('messages.email') }}</label>
                    <input type="email" id="email" name="email" placeholder="{{ __('messages.email') }}">
                </div>
                <div class="form-group">
                    <label for="gender">{{ __('messages.gender') }}</label>
                    <select id="gender" name="gender" required>
                        <option value="">{{ __('messages.select_gender') }}</option>
                        <option value="male">{{ __('messages.male') }}</option>
                        <option value="female">{{ __('messages.female') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="birth_date">{{ __('messages.birth_date') }}</label>
                    <input type="date" id="birth_date" name="birth_date" required>
                </div>
                <div class="form-group ocr-section">
                    <label for="prescription"><i class="fas fa-file-medical"></i> {{ __('تحميل الوصفة الطبية (اختياري)') }}</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="prescription" name="prescription" accept="image/*" class="file-input">
                        <div class="ocr-status" id="ocrStatus" style="display:none;">
                            <span class="spinner"><i class="fas fa-animated fa-spinner"></i></span>
                            <span class="status-text">جاري قراءة الوصفة...</span>
                        </div>
                    </div>
                    <small class="form-text text-muted">يمكنك تحميل صورة للوصفة الطبية وسيقوم النظام بمحاولة التعرف على التحاليل المطلوبة تلقائياً</small>
                </div>

                <div id="preparationBox" class="preparation-box" style="display:none;">
                    <h3><i class="fas fa-info-circle"></i> تعليمات التحضير للتحاليل المختارة:</h3>
                    <ul id="preparationList"></ul>
                </div>

                <div class="form-group">
                    <label>{{ __('messages.analysis_types') }} <span class="required">*</span></label>

                    <div class="checkbox-grid">
                      @foreach ($analyses as $analysis)
                        @if($analysis->availability == 1)
                       <div class="checkbox-item">
                  <input type="checkbox" name="analysisTypes[]" value="{{ $analysis->id }}" id="analysis_{{ $analysis->id }}" 
                         data-name="{{ $analysis->name }}" data-code="{{ $analysis->code }}" 
                         data-preparation="{{ $analysis->preparation_instructions }}">
                 <label for="analysis_{{ $analysis->id }}">{{ $analysis->name }}</label>
                  </div>
                       @endif
                           @endforeach
                           </div>
                            <small class="form-text text-muted">يمكنك اختيار تحليل واحد أو أكثر من القائمة أعلاه</small>
                </div>
                <div class="form-group">
                    <label for="date">{{ __('messages.date') }}</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="time">{{ __('messages.time') }}</label>
                    <input type="time" id="time" name="time" required>
                </div>
                <button type="submit"><i class="fas fa-paper-plane"></i> {{ __('messages.confirm_booking') }}</button>
            </form>
        </div>
    </section>

    <!-- Tesseract.js for OCR -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const prescriptionInput = document.getElementById('prescription');
            const ocrStatus = document.getElementById('ocrStatus');
            const checkboxes = document.querySelectorAll('input[name="analysisTypes[]"]');
            const preparationBox = document.getElementById('preparationBox');
            const preparationList = document.getElementById('preparationList');

            // Function to update preparations
            function updatePreparations() {
                preparationList.innerHTML = '';
                let hasPrep = false;
                
                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        const prep = cb.getAttribute('data-preparation');
                        if (prep && prep.trim() !== '') {
                            const li = document.createElement('li');
                            li.innerHTML = `<strong>${cb.getAttribute('data-name')}:</strong> ${prep}`;
                            preparationList.appendChild(li);
                            hasPrep = true;
                        }
                    }
                });

                preparationBox.style.display = hasPrep ? 'block' : 'none';
            }

            // Listen for checkbox changes
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updatePreparations);
            });

            // OCR Logic
            if (prescriptionInput) {
                prescriptionInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    ocrStatus.style.display = 'block';
                    ocrStatus.querySelector('.status-text').textContent = 'جاري قراءة الوصفة (قد يستغرق ذلك بضع ثوان)...';

                    // Clear previous debug
                    console.log("Starting OCR for:", file.name);

                    Tesseract.recognize(
                        file,
                        'ara+fra+eng',
                        { 
                            logger: m => {
                                if (m.status === 'recognizing text') {
                                    ocrStatus.querySelector('.status-text').textContent = `جاري التعرف: ${Math.round(m.progress * 100)}%`;
                                }
                            } 
                        }
                    ).then(({ data: { text } }) => {
                        console.log("Raw OCR Result:", text);
                        ocrStatus.style.display = 'none';
                        
                        if (!text || text.trim().length < 5) {
                            showNotification('لم يتم العثور على نص واضح في الصورة، يرجى التأكد من جودة الصورة', 'warning');
                            return;
                        }

                        let foundCount = 0;
                        const lowerText = text.toLowerCase();
                        
                        // Better normalization for matching
                        const normalize = (str) => {
                            if (!str) return '';
                            return str.toLowerCase()
                                .replace(/[^\w\u0621-\u064A]/g, ' ') // keep words and arabic chars, replace others with space
                                .replace(/\s+/g, ' ') // collapse spaces
                                .trim();
                        };

                        const normalizedInput = normalize(text);
                        console.log("Normalized Input:", normalizedInput);

                        checkboxes.forEach(cb => {
                            const name = cb.getAttribute('data-name');
                            const code = cb.getAttribute('data-code');
                            
                            const normalizedName = normalize(name);
                            const normalizedCode = normalize(code);

                            // Match Logic:
                            // 1. Exact code match (e.g. CBC)
                            // 2. Name as part of string
                            // 3. Normalized name as part of normalized input
                            let isMatch = false;
                            
                            if (code && lowerText.includes(code.toLowerCase())) {
                                isMatch = true;
                            } else if (name && text.includes(name)) {
                                isMatch = true;
                            } else if (normalizedName && normalizedName.length > 3 && normalizedInput.includes(normalizedName)) {
                                isMatch = true;
                            }

                            if (isMatch) {
                                if (!cb.checked) {
                                    cb.checked = true;
                                    cb.parentElement.classList.add('highlight-found'); // Visual feedback
                                    setTimeout(() => cb.parentElement.classList.remove('highlight-found'), 3000);
                                    foundCount++;
                                }
                            }
                        });

                        if (foundCount > 0) {
                            showNotification(`رائع! تم التعرف على ${foundCount} تحاليل بنجاح`, 'success');
                            updatePreparations();
                        } else {
                            showNotification('تمت القراءة بنجاح لكن لم نجد تحاليل مطابقة. جرب اختيارها يدوياً.', 'info');
                            // Log what was found to console for debugging
                            console.log("No matches found. Check if keywords exist in raw text.");
                        }
                    }).catch(err => {
                        console.error("OCR Error:", err);
                        ocrStatus.style.display = 'none';
                        showNotification('عذراً، حدث خطأ أثناء المعالجة. يرجى المحاولة مرة أخرى.', 'error');
                    });
                });
            }
        });
    </script>

    <!-- Contact Section -->
    <section id="contact" class="section">
        <div class="container">
            <h2><i class="fas fa-envelope"></i> {{ __('messages.contact') }}</h2>
            <p>{{ __('messages.contact_desc') }}</p>
            <form id="contactForm" action={{route('message')}} method="POST">
                @csrf
                <div class="form-group">
                    <label for="contact_name">{{ __('messages.name') }}</label>
                    <input type="text" name="name" id="contact_name" placeholder="{{ __('messages.name') }}" required>
                </div>
                <div class="form-group">
                    <label for="contact_email">{{ __('messages.email') }}</label>
                    <input type="email" id="contact_email" name="email" placeholder="{{ __('messages.email') }}" required>
                </div>
                <div class="form-group">
                    <label for="message">{{ __('messages.message') }}</label>
                    <textarea id="message" name="message" rows="5" placeholder="{{ __('messages.message') }}" required></textarea>
                </div>
                <button type="submit"><i class="fas fa-paper-plane"></i> {{ __('messages.send_message') }}</button>
            </form>
        </div>
    </section>

    <!-- Map Section -->
    <section class="section">
        <div class="container">
            <h2><i class="fas fa-map-marker-alt"></i> {{ __('messages.our_location') }}</h2>
            <p>{{ __('messages.location_desc') }}</p>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5693.052425601013!2d5.262623025838582!3d31.957933404038677!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x125d69d1688915f9%3A0xc65def288f0e9a57!2sLaboratoire%20Bela%C3%AFd%20d&#39;analyse%20m%C3%A9dical!5e0!3m2!1sen!2sdz!4v1761573361428!5m2!1sen!2sdz" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>{{ __('messages.admin_panel') }}</h3>
                <p>{{ __('messages.footer_about') }}</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>{{ __('messages.quick_links') }}</h3>
                <a href="#home">{{ __('messages.home') }}</a>
                <a href="#features">{{ __('messages.features') }}</a>
                <a href="#analysis">{{ __('messages.analysis') }}</a>
                <a href="#booking">{{ __('messages.booking') }}</a>
                <a href="{{ route('access') }}">{{ __('messages.patient_portal') }} / {{ __('messages.physician_portal') }}</a>
                <a href="#contact">{{ __('messages.contact') }}</a>
            </div>
            <div class="footer-section">
                <h3>{{ __('messages.contact_info') }}</h3>
                <p><i class="fas fa-map-marker-alt"></i> {{ __('messages.address') }}</p>
                <p><i class="fas fa-phone"></i> {{ __('messages.phone_val') }}</p>
                <p><i class="fas fa-envelope"></i> {{ __('messages.email_val') }}</p>
                <p><i class="fas fa-clock"></i> {{ __('messages.work_hours') }}</p>
            </div>
        </div>
        <div class="copyright">
            <p>{!! __('messages.all_rights_reserved') !!}</p>
        </div>
    </footer>

    {{-- Auto-trigger PDF download and form validation --}}
    <script>
        window.addEventListener('load', function() {
            // 1. Auto-download PDF if session exists
            @if(session('download_pdf'))
                var reservationId = {{ session('download_pdf') }};
                var downloadUrl = '{{ url("/reservation") }}/' + reservationId + '/pdf/request';
                
                var link = document.createElement('a');
                link.href = downloadUrl;
                link.download = 'reservation_confirmation.pdf';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            @endif

            // 2. Client-side validation for booking form
            const bookingForm = document.getElementById('bookingForm');
            const prescriptionInput = document.getElementById('prescription');
            const phoneInput = document.getElementById('phone');
            const dateInput = document.getElementById('date');
            const emailInput = document.getElementById('email');

            if (bookingForm) {
                // Set min date to today
                const today = new Date().toISOString().split('T')[0];
                if (dateInput) dateInput.setAttribute('min', today);

                bookingForm.addEventListener('submit', function(e) {
                    let errorMessage = null;

                    // Analysis/Prescription Validation
                    const checkboxes = this.querySelectorAll('input[name="analysisTypes[]"]:checked');
                    const hasFile = prescriptionInput && prescriptionInput.files.length > 0;
                    if (checkboxes.length === 0 && !hasFile) {
                        errorMessage = '{{ __('messages.at_least_one_analysis_or_prescription') }}';
                    }

                    // Phone Validation (Algerian Format)
                    const phoneRegex = /^(05|06|07)[0-9]{8}$/;
                    if (!errorMessage && phoneInput && !phoneRegex.test(phoneInput.value)) {
                        errorMessage = '{{ __('messages.invalid_phone_format') }}';
                    }

                    // Date Validation (Already helped by 'min' attribute, but for safety)
                    if (!errorMessage && dateInput && dateInput.value < today) {
                        errorMessage = '{{ __('messages.invalid_date_past') }}';
                    }

                    // Email Validation (Optional field, but must be valid if filled)
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!errorMessage && emailInput && emailInput.value.trim() !== '' && !emailRegex.test(emailInput.value)) {
                        errorMessage = '{{ __('messages.invalid_email_format') }}';
                    }

                    if (errorMessage) {
                        e.preventDefault();
                        showNotification(errorMessage, 'error');
                        
                        // Highlight the field with error if possible
                        if (errorMessage.includes('الهاتف') || errorMessage.includes('téléphone')) phoneInput.focus();
                        else if (errorMessage.includes('التاريخ') || errorMessage.includes('date')) dateInput.focus();
                        else if (errorMessage.includes('البريد') || errorMessage.includes('e-mail')) emailInput.focus();
                    }
                });
            }
        });

        // Global Notification Function
        function showNotification(message, type = 'info') {
            const notification = document.getElementById('notification');
            if (!notification) return;

            notification.textContent = message;
            notification.className = 'notification ' + type;
            notification.style.display = 'block';

            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        }

        // Preparation Modal Logic
        const prepModalOverlay = document.getElementById('prepModalOverlay');
        const closePrepModal = document.getElementById('closePrepModal');

        @if(session('preparations') && count(session('preparations')) > 0)
            prepModalOverlay.style.display = 'flex';
        @endif

        if (closePrepModal) {
            closePrepModal.addEventListener('click', () => {
                prepModalOverlay.style.display = 'none';
            });
        }

        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');
        
        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', () => {
                navLinks.classList.toggle('active');
                const icon = menuToggle.querySelector('i');
                if (navLinks.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });

            // Close menu when clicking a link
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    navLinks.classList.remove('active');
                    menuToggle.querySelector('i').classList.replace('fa-times', 'fa-bars');
                });
            });
        }
    </script>
</body>

</html>
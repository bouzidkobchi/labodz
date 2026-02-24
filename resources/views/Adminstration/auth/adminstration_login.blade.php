<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - labo.dz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{asset('css/auth/login.css')}}">
   
</head>
<body>
    <div class="login-container">
        <!-- Laboratory Information -->
        <div class="lab-info">
            <h3><i class="fas fa-microscope"></i> labo.dz</h3>
            <p>نظام إدارة المختبر الطبي</p>
        </div>

        <!-- Login Icon -->
        <div class="login-icon">
            <i class="fas fa-lock"></i>
        </div>

        <h2>تسجيل الدخول للإدارة</h2>
        <p>الرجاء إدخال بيانات الدخول للوصول إلى لوحة التحكم</p>

        <!-- Error Messages -->
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('auth.administrator') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> اسم المستخدم</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text"
                           id="username"
                           name="name"
                           class="form-control"
                           placeholder="أدخل اسم المستخدم"
                           value="{{ old('name') }}"
                           required
                           autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-key"></i> كلمة المرور</label>
                <div class="input-icon password-container">
                    <i class="fas fa-key"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           placeholder="أدخل كلمة المرور"
                           required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group" style="text-align: right;">
                <label style="display: inline-flex; align-items: center; font-weight: normal;">
                    <input type="checkbox" name="remember" style="margin-left: 8px;">
                    تذكرني
                </label>
            </div>

            <button type="submit" class="btn btn-primary" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>
                تسجيل الدخول
            </button>
        </form>

        <!-- Footer -->
        <div class="login-footer">
            <p><i class="fas fa-shield-alt"></i> نظام آمن ومحمي</p>
            <p>© 2024 labo.dz. جميع الحقوق محفوظة.</p>
        </div>
    </div>

   
</body>
</html>

<script src="{{asset('js/auth/login.js')}}"></script>

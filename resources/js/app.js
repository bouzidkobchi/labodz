// import './bootstrap';


// public/js/app.js
document.addEventListener('DOMContentLoaded', function () {
    // معالجة نموذج الاتصال
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = this.querySelector('[name="name"]').value;

            // محاكاة إرسال البيانات إلى الخادم
            setTimeout(() => {
                showNotification(`شكراً ${name}، تم إرسال رسالتك بنجاح وسنرد عليك في أقرب وقت`, 'success');
                this.reset();
            }, 1000);
        });
    }

    // وظيفة عرض الإشعارات
    function showNotification(message, type) {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.textContent = message;
            notification.className = `notification ${type} show`;

            setTimeout(() => {
                notification.classList.remove('show');
            }, 5000);
        }
    }

    // إضافة تأثيرات للروابط
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
});

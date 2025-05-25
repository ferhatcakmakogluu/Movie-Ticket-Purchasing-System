document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const phone = document.getElementById('phone');

    // Format phone number as user types
    phone.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) value = value.slice(0, 10);
        e.target.value = value;
    });

    // Real-time password validation
    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validateConfirmPassword);

    function validatePassword() {
        const value = password.value;
        let isValid = true;

        if (value.length < 6) {
            isValid = false;
        } else if (!/[A-Z]/.test(value)) {
            isValid = false;
        } else if (!/[a-z]/.test(value)) {
            isValid = false;
        } else if (!/[0-9]/.test(value)) {
            isValid = false;
        }

        if (!isValid) {
            password.classList.add('is-invalid');
        } else {
            password.classList.remove('is-invalid');
        }

        validateConfirmPassword();
    }

    function validateConfirmPassword() {
        if (confirmPassword.value !== password.value) {
            confirmPassword.classList.add('is-invalid');
        } else {
            confirmPassword.classList.remove('is-invalid');
        }
    }

    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Validate all required fields
        form.querySelectorAll('input[required]').forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        // Validate email format
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            email.classList.add('is-invalid');
            isValid = false;
        }

        // Validate phone number
        if (phone.value.length !== 10) {
            phone.classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
}); 
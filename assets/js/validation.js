/**
 * Generic client-side validation helpers.
 * Shows inline error messages and blocks submission until fixed.
 */

function showError(input, message) {
    input.classList.add('invalid');
    input.classList.remove('valid');
    let errorEl = input.parentElement.querySelector('.field-error');
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'field-error';
        input.parentElement.appendChild(errorEl);
    }
    errorEl.textContent = message;
    errorEl.classList.add('show');
}

function clearError(input) {
    input.classList.remove('invalid');
    input.classList.add('valid');
    const errorEl = input.parentElement.querySelector('.field-error');
    if (errorEl) errorEl.classList.remove('show');
}

const Validators = {
    required: (val) => val.trim().length > 0 || "This field is required.",
    minLen: (n) => (val) => val.trim().length >= n || `Must be at least ${n} characters.`,
    name: (val) => /^[A-Za-z ]{3,50}$/.test(val.trim()) || "Enter a valid name (letters only, 3-50 chars).",
    email: (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim()) || "Enter a valid email address.",
    phone: (val) => /^[6-9][0-9]{9}$/.test(val.trim()) || "Enter a valid 10-digit phone number.",
    rollNo: (val) => /^[A-Za-z0-9\-\/]{3,30}$/.test(val.trim()) || "Enter a valid roll number.",
    roomNo: (val) => /^[A-Za-z0-9\-]{1,10}$/.test(val.trim()) || "Enter a valid room number.",
    password: (val) => (val.length >= 6 && /[A-Z]/.test(val) && /[0-9]/.test(val)) ||
        "Min 6 chars, 1 uppercase letter, 1 number.",
};

/**
 * Attaches live + submit validation to a form.
 * rules = { fieldName: [validatorFn, validatorFn...] }
 */
function attachValidation(formEl, rules, extraCheck) {
    Object.keys(rules).forEach((fieldName) => {
        const input = formEl.elements[fieldName];
        if (!input) return;
        input.addEventListener('input', () => validateField(input, rules[fieldName]));
        input.addEventListener('blur', () => validateField(input, rules[fieldName]));
    });

    formEl.addEventListener('submit', (e) => {
        let allValid = true;
        Object.keys(rules).forEach((fieldName) => {
            const input = formEl.elements[fieldName];
            if (!input) return;
            const ok = validateField(input, rules[fieldName]);
            if (!ok) allValid = false;
        });
        if (extraCheck && !extraCheck()) allValid = false;
        if (!allValid) {
            e.preventDefault();
            const firstInvalid = formEl.querySelector('.invalid');
            if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}

function validateField(input, validators) {
    for (const validator of validators) {
        const result = validator(input.value);
        if (result !== true) {
            showError(input, result);
            return false;
        }
    }
    clearError(input);
    return true;
}

/* ---------------- Page-specific bindings ---------------- */

document.addEventListener('DOMContentLoaded', function () {
    // Registration form
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        attachValidation(regForm, {
            full_name: [Validators.required, Validators.name],
            roll_no: [Validators.required, Validators.rollNo],
            email: [Validators.required, Validators.email],
            phone: [Validators.required, Validators.phone],
            hostel_block: [Validators.required],
            room_no: [Validators.required, Validators.roomNo],
            password: [Validators.required, Validators.password],
            confirm_password: [Validators.required],
        }, function () {
            const pw = regForm.elements['password'];
            const cpw = regForm.elements['confirm_password'];
            if (pw.value !== cpw.value) {
                showError(cpw, "Passwords do not match.");
                return false;
            }
            clearError(cpw);
            return true;
        });
    }

    // Login forms (student/admin)
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const idField = loginForm.dataset.idfield || 'email';
        const rules = {
            password: [Validators.required],
        };
        rules[idField] = [Validators.required];
        attachValidation(loginForm, rules);
    }

    // Complaint form
    const complaintForm = document.getElementById('complaintForm');
    if (complaintForm) {
        attachValidation(complaintForm, {
            category: [Validators.required],
            subject: [Validators.required, Validators.minLen(5)],
            description: [Validators.required, Validators.minLen(15)],
            priority: [Validators.required],
            room_no: [Validators.required, Validators.roomNo],
        });

        // live character counter for description
        const desc = complaintForm.elements['description'];
        const counter = document.getElementById('descCounter');
        if (desc && counter) {
            desc.addEventListener('input', () => {
                counter.textContent = `${desc.value.length} characters`;
            });
        }
    }
});

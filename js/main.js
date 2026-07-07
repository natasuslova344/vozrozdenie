async function sendLead(data) {
    const response = await fetch('api/submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    const result = await response.json();
    if (!result.success) {
        throw new Error(result.message || 'Ошибка при отправке заявки');
    }
    return result;
}

const burgerMenu = document.querySelector('.burger-menu');
const navLinks = document.querySelector('.nav-links');
const body = document.body;

if (burgerMenu) {
    burgerMenu.addEventListener('click', () => {
        burgerMenu.classList.toggle('active');
        navLinks.classList.toggle('active');
        body.classList.toggle('menu-open');
    });

    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            burgerMenu.classList.remove('active');
            navLinks.classList.remove('active');
            body.classList.remove('menu-open');
        });
    });

    body.addEventListener('click', (e) => {
        if (body.classList.contains('menu-open') && !e.target.closest('.nav-links') && !e.target.closest('.burger-menu')) {
            burgerMenu.classList.remove('active');
            navLinks.classList.remove('active');
            body.classList.remove('menu-open');
        }
    });
}

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === "#" || href === "") return;
        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

function validatePhone(phone) {
    const digits = phone.replace(/\D/g, '');
    return digits.length === 11 && (digits[0] === '7' || digits[0] === '8');
}

function validateEmail(email) {
    if (!email) return true;
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);
}

const form = document.getElementById('callbackForm');
if (form) {
    const fields = {
        name: { input: document.getElementById('name'), error: document.getElementById('nameError') },
        phone: { input: document.getElementById('phone'), error: document.getElementById('phoneError') },
        email: { input: document.getElementById('email'), error: document.getElementById('emailError') },
        message: { input: document.getElementById('message'), error: document.getElementById('messageError') }
    };

    function clearError(field) {
        field.input.classList.remove('error');
        field.error.classList.remove('show');
    }

    function showError(field) {
        field.input.classList.add('error');
        field.error.classList.add('show');
    }

    Object.values(fields).forEach(field => {
        field.input.addEventListener('input', () => {
            if (field.input.classList.contains('error')) clearError(field);
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        Object.values(fields).forEach(clearError);

        let isValid = true;
        const data = {};

        data.name = fields.name.input.value.trim();
        if (!data.name) { showError(fields.name); isValid = false; }

        data.phone = fields.phone.input.value.trim();
        if (!data.phone || !validatePhone(data.phone)) {
            showError(fields.phone); isValid = false;
        }

        data.email = fields.email.input.value.trim();
        if (!validateEmail(data.email)) {
            showError(fields.email); isValid = false;
        }

        data.message = fields.message.input.value.trim();
        if (!data.message) { showError(fields.message); isValid = false; }

        if (isValid) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';

            sendLead({ ...data, source: 'Форма на сайте' })
                .then(() => {
                    form.reset();
                    document.getElementById('successModal').style.display = 'flex';
                })
                .catch((err) => {
                    alert('Не удалось отправить заявку. Позвоните нам, пожалуйста: +7 (932)-328-41-92.\n\n' + err.message);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        }
    });
}

const modal = document.getElementById('successModal');
const closeModal = document.getElementById('closeModal');

if (closeModal) {
    closeModal.addEventListener('click', () => modal.style.display = 'none');
}

window.addEventListener('click', (e) => {
    if (e.target === modal) modal.style.display = 'none';
});

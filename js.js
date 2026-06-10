// ===== БУРГЕР-МЕНЮ =====
const burgerMenu = document.querySelector('.burger-menu');
const navLinks = document.querySelector('.nav-links');
const body = document.body;

if (burgerMenu) {
    burgerMenu.addEventListener('click', () => {
        burgerMenu.classList.toggle('active');
        navLinks.classList.toggle('active');
        body.classList.toggle('menu-open');
    });

    // Закрытие меню при клике на ссылку
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            burgerMenu.classList.remove('active');
            navLinks.classList.remove('active');
            body.classList.remove('menu-open');
        });
    });

    // Закрытие меню при клике на затемненный фон
    body.addEventListener('click', (e) => {
        if (body.classList.contains('menu-open') && !e.target.closest('.nav-links') && !e.target.closest('.burger-menu')) {
            burgerMenu.classList.remove('active');
            navLinks.classList.remove('active');
            body.classList.remove('menu-open');
        }
    });
}

// ===== Плавная прокрутка к якорям =====
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

// ===== Функции валидации =====
function validatePhone(phone) {
    const digits = phone.replace(/\D/g, '');
    // Достаточно проверить, что 11 цифр и первая из них 7 или 8
    return digits.length === 11 && (digits[0] === '7' || digits[0] === '8');
}

function validateEmail(email) {
    if (!email) return true; // Поле необязательное
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);
}

// ===== Работа с формой =====
const form = document.getElementById('callbackForm');
if (form) {
    // Объединяем поля и их ошибки в один удобный объект
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

    // Очистка ошибки при вводе текста (один цикл вместо 4-х одинаковых блоков)
    Object.values(fields).forEach(field => {
        field.input.addEventListener('input', () => {
            if (field.input.classList.contains('error')) clearError(field);
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Сбрасываем все ошибки перед новой проверкой
        Object.values(fields).forEach(clearError);
        
        let isValid = true;
        const data = {};

        // 1. Имя
        data.name = fields.name.input.value.trim();
        if (!data.name) { showError(fields.name); isValid = false; }

        // 2. Телефон
        data.phone = fields.phone.input.value.trim();
        if (!data.phone || !validatePhone(data.phone)) { 
            showError(fields.phone); isValid = false; 
        }

        // 3. Email
        data.email = fields.email.input.value.trim();
        if (!validateEmail(data.email)) { 
            showError(fields.email); isValid = false; 
        }

        // 4. Сообщение
        data.message = fields.message.input.value.trim();
        if (!data.message) { showError(fields.message); isValid = false; }

        // Если форма валидна
        if (isValid) {
            console.log('Заявка:', data); // Данные в консоли (для проверки)
            
            form.reset();
            document.getElementById('successModal').style.display = 'flex';
        }
    });
}

// ===== Модальное окно =====
const modal = document.getElementById('successModal');
const closeModal = document.getElementById('closeModal');

if (closeModal) {
    closeModal.addEventListener('click', () => modal.style.display = 'none');
}

window.addEventListener('click', (e) => {
    if (e.target === modal) modal.style.display = 'none';
});

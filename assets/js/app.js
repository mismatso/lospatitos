document.addEventListener('DOMContentLoaded', function () {
    initFormValidation();
    initConfirmActions();
    initCartAutoSubmit();
});

/* =====================================================================
 * Validación de formularios en cliente
 * ===================================================================== */
function initFormValidation() {
    var forms = document.querySelectorAll('form[data-validate]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            clearFieldErrors(form);

            var isValid = true;
            var mode = form.getAttribute('data-validate');

            if (mode === 'login') {
                isValid = validateLoginForm(form);
            } else if (mode === 'register') {
                isValid = validateRegisterForm(form);
            } else if (mode === 'contact') {
                isValid = validateContactForm(form);
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    });
}

function validateLoginForm(form) {
    var identifier = form.querySelector('[name="identifier"]');
    var password = form.querySelector('[name="password"]');
    var valid = true;

    if (!identifier.value.trim()) {
        showFieldError(identifier, 'Ingrese su usuario o correo.');
        valid = false;
    }

    if (!password.value.trim()) {
        showFieldError(password, 'Ingrese su contraseña.');
        valid = false;
    }

    return valid;
}

function validateRegisterForm(form) {
    var nombre = form.querySelector('[name="nombre"]');
    var correo = form.querySelector('[name="correo"]');
    var usuario = form.querySelector('[name="usuario"]');
    var password = form.querySelector('[name="password"]');
    var passwordConfirm = form.querySelector('[name="password_confirm"]');
    var valid = true;
    var userPattern = /^[a-zA-Z0-9._-]{4,50}$/;

    if (nombre.value.trim().length < 3) {
        showFieldError(nombre, 'El nombre debe tener al menos 3 caracteres.');
        valid = false;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo.value.trim())) {
        showFieldError(correo, 'Ingrese un correo electrónico válido.');
        valid = false;
    }

    if (!userPattern.test(usuario.value.trim())) {
        showFieldError(usuario, 'El usuario solo puede contener letras, números, punto, guion o guion bajo.');
        valid = false;
    }

    if (password.value.length < 8) {
        showFieldError(password, 'La contraseña debe tener al menos 8 caracteres.');
        valid = false;
    }

    if (password.value !== passwordConfirm.value) {
        showFieldError(passwordConfirm, 'Las contraseñas no coinciden.');
        valid = false;
    }

    return valid;
}

function validateContactForm(form) {
    var nombre = form.querySelector('[name="nombre"]');
    var correo = form.querySelector('[name="correo"]');
    var asunto = form.querySelector('[name="asunto"]');
    var mensaje = form.querySelector('[name="mensaje"]');
    var valid = true;

    if (nombre.value.trim().length < 3) {
        showFieldError(nombre, 'Indique su nombre.');
        valid = false;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo.value.trim())) {
        showFieldError(correo, 'Ingrese un correo electrónico válido.');
        valid = false;
    }

    if (asunto.value.trim().length < 3) {
        showFieldError(asunto, 'Indique un asunto.');
        valid = false;
    }

    if (mensaje.value.trim().length < 10) {
        showFieldError(mensaje, 'El mensaje debe tener al menos 10 caracteres.');
        valid = false;
    }

    return valid;
}

function showFieldError(input, message) {
    input.classList.add('input-error');

    var error = document.createElement('p');
    error.className = 'field-error';
    error.textContent = message;

    input.insertAdjacentElement('afterend', error);
}

function clearFieldErrors(form) {
    form.querySelectorAll('.input-error').forEach(function (input) {
        input.classList.remove('input-error');
    });

    form.querySelectorAll('.field-error').forEach(function (error) {
        error.remove();
    });
}

/* =====================================================================
 * Confirmación para acciones destructivas (eliminar pedido/reseña)
 * ===================================================================== */
function initConfirmActions() {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || '¿Está seguro?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

/* =====================================================================
 * Carrito: al cambiar la cantidad, envía el formulario automáticamente
 * ===================================================================== */
function initCartAutoSubmit() {
    document.querySelectorAll('[data-cart-qty]').forEach(function (input) {
        input.addEventListener('change', function () {
            var form = input.closest('form');
            if (form) {
                form.submit();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('form[data-validate]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            clearFieldErrors(form);

            var isValid = true;
            var mode = form.getAttribute('data-validate');

            if (mode === 'login') {
                isValid = validateLoginForm(form);
            }

            if (mode === 'register') {
                isValid = validateRegisterForm(form);
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    });
});

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

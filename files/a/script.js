function validatePassword(password) {
    if (password.length < 6 || password.length > 10) {
        return false;
    }

    let isOk = false;

    for (let i = 0; i < password.length; i++) {
        if (password[i] >= 'A' && password[i] <= 'Z') {
            isOk = true;
            break;
        }
    }

    if (!isOk) return isOk;
    
    /*
     * Тук не разбрах дали трябва да съдържа само малки и големи букви и цифри или
     * трябва да съдържа поне една малка и голяма буква и цифра. Направих го с поне една.
     */
    for (let i = 0; i < password.length; i++) {
        if (password[i] >= 'a' && password[i] <= 'z') {
            isOk = true;
            break;
        }
    }

    if (!isOk) return isOk;

    return /\d/.test(password);
}

function validatePostalCode(postalCode) {
    arr = postalCode.split('-');

    if (arr.length != 1) {
        if (arr.length != 2) {
            return false;
        }

        if (!/^\d+$/.test(arr[0])) {
            return false;
        }

        if (!/^\d+$/.test(arr[1])) {
            return false;
        }
    } else {
        return /^\d+$/.test(arr[0]);
    }
}

function validateFormAndGetInvalidInput(body) {
    if (body["username"].length < 3 || body["username"].length > 10) {
        return "username";
    }

    if (body["name"].length > 50) {
        return "name";
    }

    if (body["family-name"].length > 50) {
        return "family-name";
    }

    if (!validatePassword(body["password"])) {
        return "password";
    }

    if (body["email"].split("@").length == 1) {
        return "email";
    }

    if (body["postal-code"] != "") {
        if (!validatePostalCode(body["postal-code"])) {
            return "postal-code";
        }
    }

    return "ok";
}

function displayError(msg) {
    const errorMsg = document.createElement('div');
    errorMsg.setAttribute('class', 'error');
    errorMsg.innerText = msg;

    document.getElementById('register-form').appendChild(errorMsg);
}

function displaySuccess(msg) {
    const successMsg = document.createElement('div');
    successMsg.setAttribute('id', 'success');
    successMsg.innerText = msg;

    document.getElementById('register-form').appendChild(successMsg);
}

function checkIfUserExists(body, users) {
    for (let i = 0; i < users.length; i++) {
        if (body.username === users[i].username) {
            return true;
        }

        if (body.email === users[i].email) {
            return true;
        }
    }

    return false;
}

function clearErrorMessages() {
    document.querySelectorAll('#register-form .error')
        .forEach(errorMessage => {
            errorMessage.parentElement.removeChild(errorMessage);
        })
}

function clearSuccessMessages() {
    const success = document.getElementById('success');

    if (success != null) {
        success.parentElement.removeChild(success);
    }
}

document.getElementById("register-form").addEventListener("submit", (event) => {
    event.preventDefault();
    clearErrorMessages();
    clearSuccessMessages();
    const form = event.target;

    const body = {
        'username' : form.username.value,
        'name' : form.name.value,
        'family-name' : form['family-name'].value,
        'email' : form.email.value,
        'password' : form.password.value,
        'street' : form.street.value,
        'city' : form.city.value,
        'postal-code' : form['postal-code'].value
    };

    const isFormValid = validateFormAndGetInvalidInput(body);

    switch(isFormValid) {
        case 'username':
        {
            displayError('Невалидно потребителско име. Потребителското име трябва да е между 3 и 10 символа.');
            return;
        }
        case 'name':
        {
            displayError('Невалидно име. Името трябва да е до 50 символа.');
            return;
        }
        case 'family-name':
        {
            displayError('Невалиднo фамилно име. Фамилията трябва да е до 50 символа.');
            return;
        }
        case 'email':
        {
            displayError('Невалиден e-mail.');
            return;
        }
        case 'password':
        {
            displayError('Невалидна парола. Паролата трябва да е между 6 и 10 символа, да съдържа поне една голяма и малка буква и поне една цифра.');
            return;
        }
        case 'postal-code':
        {
            displayError('Невалиден пощенски код. Той трябва да е от формат "1111" или "1111-1111".');
            return;
        }
        case 'ok':
        {
            fetch('https://jsonplaceholder.typicode.com/users', {
                method: "GET",
            })
            .then(response =>  {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error();
                }
            })
            .then(result => {
                if (checkIfUserExists(body, result)) {
                    displayError('Потребителското име вече е заето.');
                    return; 
                } else {
                    displaySuccess('Успешно се регистрирахте.');
                    return;
                }
            })
            .catch((e) => {
                console.log(e.stack);
            });
        }
    }
})
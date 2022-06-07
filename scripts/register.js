function validatePassword(password) {
    return password.match(/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/);
}


const registerMethods = {
    removeFormElement: () => {
        const registerForm = document.getElementById('register-form');

        if (registerForm) {
            registerForm.parentElement.removeChild(registerForm);
        }
    },

    displayForm: () => {
        registerMethods.removeFormElement();
        loginMethods.removeFormElement();

        const registerForm = document.createElement('div');

        registerForm.innerHTML = `
        <form id="register-form">
            <input type="text" name="username" placeholder="Потребителско име" required/>
            <input type="password" name="password" placeholder="Парола" required/>
            <input type="password" name="matchPassword" placeholder="Въведете повторно парола" required/>
            <input type="email" id="email" placeholder="Email" required/>
            <input type="submit" value="Регистрирай се!">
        </form>`;

        document.getElementById("content-wrapper").appendChild(registerForm);
    
        document.getElementById("register-form").addEventListener("submit", registerMethods.submitForm)
    },

    displayErrorMessage: message => {
        const errorElement = document.createElement('div');
        errorElement.innerText = message;
        errorElement.setAttribute("class", "error-message");
    
        document.getElementById('register-form').appendChild(errorElement);
    },

    clearErrorMessages: () => {
        document.querySelectorAll('#register-form .error-message')
            .forEach(errorMessage => {
                errorMessage.parentElement.removeChild(errorMessage);
            })
    }, 

    displaySuccessMessage: message => {
        const messageElement = document.createElement('div');
        messageElement.innerText = message;
        messageElement.setAttribute("class", "success-message");

        document.getElementById('register-form').appendChild(messageElement);
    },

    clearSuccessMessages: () => {
        document.querySelectorAll('#register-form .message')
            .forEach(message => {
                message.parentElement.removeChild(message);
            })
    },

    submitForm: event => {

        event.preventDefault();

        registerMethods.clearErrorMessages();
        registerMethods.clearSuccessMessages();
    
        const form = event.target;

        if (!validatePassword(form.password.value)) {
            registerMethods.displayErrorMessage("Паролата трябва да е поне 8 символа, да съдържа поне 1 цифра, поне една главна буква. Трябва да е на латиница");
            return;
        }

        if (form.password.value !== form.matchPassword.value) {
            registerMethods.displayErrorMessage("Двете пароли не са еднакви ");
            return;
        }
    
        const body = {
            'username': form.username.value,
            'password': form.password.value,
            'email' : form.email.value
        }
    
        fetch('./endpoints/register.php', {
                method: "POST",
                body: JSON.stringify(body)
            })
            .then(response =>  {
                console.log(response);
                return response.json();
            })
            .then(result => {
                if (result.status) {
                    console.log('we also in')
                    registerMethods.displaySuccessMessage(result.message);
                    registerMethods.displaySuccessMessage("Цъкнете 'Влез', за да влезете в профила си.");
                } else {
                    registerMethods.displayErrorMessage(result.message);
                }
            })
            .catch((err) => {
                console.log(err.stack);
                registerMethods.clearErrorMessages();
                registerMethods.clearSuccessMessages();
                registerMethods.displayErrorMessage("Неуспешен опит за регистрация. Опитайте отново след малко.");
            });
    }
}

document.getElementById('register').addEventListener('click', registerMethods.displayForm);
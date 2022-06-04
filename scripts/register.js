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
            <input type="text" name="username" placeholder="Потребителско име" />
            <input type="password" name="password" placeholder="Парола"/>
            <input type="email" id="email" placeholder="Email"/>
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

    displayMessage: message => {
        const messageElement = document.createElement('div');
        messageElement.innerText = message;
        messageElement.setAttribute("class", "message");

        document.getElementById('register-form').appendChild(messageElement);
    },

    clearMessages: () => {
        document.querySelectorAll('#register-form .message')
            .forEach(message => {
                message.parentElement.removeChild(message);
            })
    },

    submitForm: event => {

        event.preventDefault();
    
        const form = event.target;
    
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
                registerMethods.clearErrorMessages();
                registerMethods.clearMessages();
                console.log(result);
                if (result.status) {
                    console.log('we also in')
                    registerMethods.displayMessage(result.message);
                    registerMethods.displayMessage("Цъкнете 'Влез', за да влезете в профила си.");
                } else {
                    registerMethods.displayErrorMessage(result.message);
                }
            })
            .catch((err) => {
                console.log(err.stack);
                registerMethods.clearErrorMessages();
                registerMethods.clearMessages();
                registerMethods.displayErrorMessage("Неуспешен опит за регистрация. Опитайте отново след малко.");
            });
    }
}

document.getElementById('register').addEventListener('click', registerMethods.displayForm);
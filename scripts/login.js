const loginMethods = {

    checkLoginStatus: () => {
        return fetch('./endpoints/session.php')
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error();
                }
            })
    },

    removeFormElement: () => {
        const loginForm = document.getElementById("login-form");
        if (loginForm) {
            loginForm.parentElement.removeChild(loginForm);
        }
    },

    displayForm: () => {

        loginMethods.removeFormElement();
        registerMethods.removeFormElement();
    
        const loginFormElement = document.createElement('div');
        loginFormElement.innerHTML = `
            <form id="login-form">
                <input type="text" name="username" placeholder="Потребителско име" />
                <input type="password" name="password" placeholder="Парола"/>
                <input type="submit" value="Влез!">
            </form>`;
    
        document.getElementById("content-wrapper").appendChild(loginFormElement);
    
        document.getElementById("login-form").addEventListener("submit", loginMethods.submitForm)
    },

    clearErrorMessages: () => {
        document.querySelectorAll('#login-form .error-message')
            .forEach(errorMessage => {
                errorMessage.parentElement.removeChild(errorMessage);
            })
    },

    displayErrorMessage: errorMessage => {

        const errorElement = document.createElement('div');
        errorElement.innerText = errorMessage;
        errorElement.setAttribute("class", "error-message");
    
        document.getElementById('login-form').appendChild(errorElement);
    },

    submitForm: event => {

        event.preventDefault();
    
        const form = event.target;
    
        const body = {
            'username': form.username.value,
            'password': form.password.value
        }
    
        fetch('./endpoints/session.php', {
                method: "POST",
                body: JSON.stringify(body)
            })
            .then(response =>  {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error();
                }
            })
            .then(result => {
                loginMethods.clearErrorMessages();
                if (result.success) {
                    document.location.reload();
                } else {
                    // display error message
                    loginMethods.displayErrorMessage("Потребител с такава парола не съществува");
                }
            })
            .catch(() => {
                loginMethods.clearErrorMessages();
                loginMethods.displayErrorMessage("Неуспешен опит за влизане. Опитайте отново след малко.");
            });
    }
}

const logout = () => {
    fetch('./endpoints/session.php', {
        method: 'DELETE'
    })
    .then(() => {
        document.location.reload();
    });
}

const loginButton = document.getElementById('login');
const logoutButton = document.getElementById('logout')

if (loginButton) {
    loginButton.addEventListener('click', loginMethods.displayForm);
}
if (logoutButton) {
    logoutButton.addEventListener('click', logout);
}

console.log(loginMethods.checkLoginStatus())

loginMethods.checkLoginStatus()
    .then(loginStatus => {
        if (loginStatus.logged) {
            document.getElementById('logged-buttons').setAttribute('style', "display: block");
            document.getElementById('username-greeting').innerText = loginStatus.session.user_name;
        } else { // not logged
            const notLoggedButtons = document.getElementById('not-logged-buttons');
            if (notLoggedButtons) {
                notLoggedButtons.setAttribute('style', "display: block");
            }
        }
    });
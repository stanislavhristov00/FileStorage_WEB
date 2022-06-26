uploadFormMethods = {
    displayForm: (element) => {
        if (document.getElementById('upload-form') == null) {
            const form = document.createElement('div')
            form.innerHTML = `
            <form action="./endpoints/upload.php" method="POST" enctype="multipart/form-data" id="upload-form">
                <input type="file" name="file[]" multiple/>
                <input type="submit" />
            </form>
            `;
    
            element.appendChild(form);
        }
    },

    displaySuccess : (msg) => {
        const successMsg = document.createElement('div');
        successMsg.setAttribute('class', 'success-message');
        successMsg.innerText = msg;

        document.getElementById('menu').appendChild(successMsg);
    },

    displayError : (msg) => {
        const errorMsg = document.createElement('div');
        errorMsg.setAttribute('class', 'error-message');
        errorMsg.innerText = msg;

        document.getElementById('menu').appendChild(errorMsg);
    },

    removeMessages : () => {
        document.querySelectorAll('#menu .error-message').forEach(
            (message) => {
                message.parentElement.removeChild(message);
            }
        )

        document.querySelectorAll('#menu .success-message').forEach(
            (message) => {
                message.parentElement.removeChild(message);
            }
        )
    }
}

function getUploadStatus() {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
      });
      let value = params.status;
      window.history.pushState({}, document.title, window.location.pathname);

      return value;
}

const displayMenu = () => {
    uploadFormMethods.removeMessages();

    const menuElement = document.createElement('div');
    menuElement.setAttribute('id', 'menu');
    menuElement.innerHTML = `
    <span id="submitFile">Качете файл</span>
    <a href="gallery.html" style="color: #333333;">
        <span id="gallery">Качени файлове</span>
    </a>
    `;

    document.getElementById("content-wrapper").appendChild(menuElement);

    const button = document.getElementById("submitFile");
    button.addEventListener('click', () => {
        uploadFormMethods.displayForm(document.getElementById('content-wrapper'))
    });
}

loginMethods.checkLoginStatus()
    .then(loginStatus => {
        if (loginStatus.logged) {
            const logo1 = document.getElementById('logo-1');
            logo1.parentElement.removeChild(logo1);
            const logo2 = document.getElementById('logo-2');
            logo2.parentElement.removeChild(logo2);
            
            displayMenu();

            (function(){
                const result = getUploadStatus();
            
                if (result != null) {
                    if (result == 'success') {
                        uploadFormMethods.displaySuccess('Успешно добавяне.');
                        return;
                    }

                    if (result.startsWith('alreadyExists')) {
                        const name = result.split('-')[1];
                        uploadFormMethods.displayError(`${name} вече е качен.`);
                        return;
                    }

                    if (result == 'empty') {
                        uploadFormMethods.displayError('Трябва да изберете файл.');
                        return;
                    }

                    if (result == 'failed') {
                        uploadFormMethods.displayError('Нещо се обърка, опитайте пак по-късно.');
                        return;
                    }

                    if (result.startsWith('hash')) {
                        const name = result.split('-')[1];
                        uploadFormMethods.displayError(`Вече имате качен файл със същото съдържание на име ${name}`);
                        return;
                    }

                    if (result == 'databaseErr') {
                        uploadFormMethods.displayError(`Имаме проблем с базата данни. Моля свържете се с администратор :(`);
                        return;
                    }
                }
            })();
        }
    });
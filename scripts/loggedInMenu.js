uploadFormMethods = {
    displayForm: (element) => {
        if (document.getElementById('upload-form') == null) {
            const form = document.createElement('div')
            form.innerHTML = `
            <form action="./endpoints/upload.php" method="POST" enctype="multipart/form-data" id="upload-form">
                <input type="file" name="file" />
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
    <button id="submitFile">Качете файл</a>
    <a href="gallery.html" style="color: #333333;">
        <button>Качени файлове</button>
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
                    switch(result) {
                        case 'success':
                        {
                            uploadFormMethods.displaySuccess('Файлът беше добавен успешно.');
                            return;
                        }
                        case 'alreadyExists':
                        {
                            uploadFormMethods.displayError('Този файл вече е качен.');
                            return;
                        }
                        case 'empty':
                        {
                            uploadFormMethods.displayError('Трябва да изберете файл.');
                            return;
                        }
                        case 'failed':
                        {
                            uploadFormMethods.displayError('Нещо се обърка, опитайте пак по-късно.');
                            return;
                        }
                    }
                }
            })();
        }
    });
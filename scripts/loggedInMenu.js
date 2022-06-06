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
}

const displayMenu = () => {
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
            displayMenu();
        }
    });
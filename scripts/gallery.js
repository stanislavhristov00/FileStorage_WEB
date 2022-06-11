function getExtension(name) {
    parts = name.split('/')
    fileName = parts[parts.length - 1];

    fileNameParts = fileName.split('.');
    ext = fileNameParts[fileNameParts.length - 1];

    return ext;
}

function createRow(filename) {
    parts = filename.split('/')
    baseName = parts[parts.length - 1];

    const row = document.createElement('div');
    row.setAttribute('class', 'row');
    row.innerHTML = `
        <div class="item-left"><span>${baseName}</span></div>
        <div class="item-right"><span>Виж</span><span>Изтегли</span><span>Сподели</span></div>
    `;

    return row;
}

(function(){
    loginMethods.checkLoginStatus()
    .then(loginStatus => {
        if (!loginStatus.logged) {
            document.getElementById('logged-buttons').setAttribute('style', "display: none");
            document.getElementById('username-greeting').innerText = '';

            const notAuthMsg = document.createElement('div');
            notAuthMsg.innerHTML = `
            <h1>Нямате достъп до тази страница!</h1>
            <h2>Влезте в профил, за да продължите</h2>
            <a href="index.html" style="color: #333333;">
                <button>Влез</button>
            </a>
            `

            document.getElementById('content-wrapper').appendChild(notAuthMsg);
            return;
        }

        const logoutButton = document.getElementById('logout');

        if (logoutButton) {
            logoutButton.addEventListener('click', () => {
                location.href = './index.html';
            })
        }

        fetch("../endpoints/files.php", {
            method: 'GET'
        }).then(response => {
            if(response.ok) {
                return response.json();
            }
            throw new Error();
        }).then(result => {
            const contentWrapper = document.getElementById('content-wrapper');

            for(let file of result.files) {
                console.log(`FILE: ${file}`);
                console.log(`EXTENSION : ${getExtension(file)}`);

                contentWrapper.appendChild(createRow(file));
            }
        }).catch((e) => {
            console.log(e);
        })
    });


})();
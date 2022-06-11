function getExtension(name) {
    parts = name.split('/')
    fileName = parts[parts.length - 1];

    fileNameParts = fileName.split('.');
    ext = fileNameParts[fileNameParts.length - 1];

    return ext;
}

function createRow(filename, id) {
    parts = filename.split('/')
    baseName = parts[parts.length - 1];

    const row = document.createElement('div');
    row.setAttribute('class', 'row');
    row.innerHTML = `
        <div class="item-left openpop" id="openpop-div-${id}"><span>${baseName}</span></div>
        <div class="item-right">
            <span class="openpop" id="openpop-span-${id}">Виж</span>
            <span><a href="endpoints/download.php?file_name=${baseName}" target="_blank">Изтегли</a></span>
            <span>Сподели</span>
        </div>
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

        document.getElementById('close-frame').addEventListener('click', () => {
            const frame = document.getElementById('frame');
            frame.style.display = 'none';

            const contentWrapper = document.getElementById('content-wrapper');
            contentWrapper.style.display = 'block';
        })

        fetch("../endpoints/files.php", {
            method: 'GET'
        }).then(response => {
            if(response.ok) {
                return response.json();
            }
            throw new Error();
        }).then(result => {
            const contentWrapper = document.getElementById('content-wrapper');
            let id = 0;
            for(let file of result.files) {
                console.log(`FILE: ${file}`);
                console.log(`EXTENSION : ${getExtension(file)}`);

                contentWrapper.appendChild(createRow(file, id));
                document.getElementById(`openpop-div-${id}`).addEventListener('click', (e) => {
                    const contentWrapper = document.getElementById('content-wrapper');
                    contentWrapper.style.display = 'none';

                    const frame = document.getElementById('frame');
                    frame.style.display = 'block';

                    const iframe = document.getElementById('actual-frame');
                    iframe.setAttribute('src', `${file.slice(3)}`)
                })

                document.getElementById(`openpop-span-${id}`).addEventListener('click', (e) => {
                    const contentWrapper = document.getElementById('content-wrapper');
                    contentWrapper.style.display = 'none';

                    const frame = document.getElementById('frame');
                    frame.style.display = 'block';

                    const iframe = document.getElementById('actual-frame');
                    iframe.setAttribute('src', `${file.slice(3)}`)
                })
                id++;
            }
        }).catch((e) => {
            console.log(e);
        })
    });


})();
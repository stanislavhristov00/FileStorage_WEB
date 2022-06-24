function getExtension(name) {
    parts = name.split('/')
    fileName = parts[parts.length - 1];

    fileNameParts = fileName.split('.');
    ext = fileNameParts[fileNameParts.length - 1];

    return ext;
}

function getBaseFileName(fileName) {
    const parts = fileName.split('/');
    return parts[parts.length - 1];
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
            <span id="delete-${id}" class="openpop">Изтрий</span>
            <span id="share-${id}" class="openpop">Сподели</span>
        </div>
    `;

    return row;
}

function deleteFile(fileName, event) {
    const body = {
        'file_name': fileName
    }

    fetch('./endpoints/delete.php', {
        method: 'POST',
        body: JSON.stringify(body)
    }).then(response => {
        if (response.ok) {
            return response.json();
        }

        throw new Error();
    }).then(result => {
        if (result.error) {
            console.log("Something wrong happened!")
            console.log(result.error);
            return;
        } else {
            deleteRow(event);
        }
    }).catch(e => {
        console.log(e);
        return false;
    })
}

function deleteRow(event) {
    const span = event.target;
    const divToDelete = span.parentElement.parentElement;

    divToDelete.parentElement.removeChild(divToDelete);
    window.location.href = window.location.href;
}

var make_handler = function (fileName) {
  return function (event) {
    deleteFile(fileName, event);
  };
};

function shareFile(fileName) {
    const body = {
        "file_name": fileName
    };
    
    fetch('./endpoints/share.php', {
        method: "POST",
        body: JSON.stringify(body)
    }).then(response => {
        if (response.ok) {
            return response.json();
        }

        throw new Error();
    }).then(result => {
        if (result.error) {
            console.log(error);
        } else {
            const url = window.location.href;
            const lastIndex = url.lastIndexOf('/');

            const newUrl = `${url.slice(0, lastIndex)}/endpoints/share.php?hash=${result.md5}&id=${result.id}`;
            alert(newUrl);
        }
    }).catch(e => {
        console.log(e);
    })
}   

var share_handler = function (fileName) {
    return function (event) {
      shareFile(fileName);
    };
  };

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

        const backButton = document.getElementById('back');

        if (backButton) {
            backButton.addEventListener('click', () => {
                location.href = './index.html';
            })
        }

        document.getElementById('close-frame').addEventListener('click', () => {
            const frame = document.getElementById('frame');
            frame.style.display = 'none';

            const contentWrapper = document.getElementById('content-wrapper');
            contentWrapper.style.display = 'block';
        })

        fetch("./endpoints/files.php", {
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

                document.getElementById(`delete-${id}`).addEventListener('click', make_handler(getBaseFileName(file)));

                document.getElementById(`share-${id}`).addEventListener('click', share_handler(getBaseFileName(file)));
                id++;
            }
        }).catch((e) => {
            console.log(e);
        })
    });
})();
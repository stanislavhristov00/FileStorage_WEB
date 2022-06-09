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
        }

        const logoutButton = document.getElementById('logout');

        if (logoutButton) {
            logoutButton.addEventListener('click', () => {
                location.href = './index.html';
            })
        }
    });


})();
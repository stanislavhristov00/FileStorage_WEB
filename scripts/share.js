const show = document.getElementById('show');

show.addEventListener("click", () => {
    const frame = document.getElementById('frame');
    if (frame.style.display == "none") { 
        frame.style.display = "block";
        show.innerText = "Скрий"
    } else {
        frame.style.display = "none";
        show.innerText = "Покажи";
    }
})
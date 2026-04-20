document.addEventListener('DOMContentLoaded', () => {

    const alertaExito = document.getElementById('alerta-exito');

    if (alertaExito && !alertaExito.classList.contains('d-none')) {

        setTimeout(() => {
            alertaExito.style.transition = "opacity 0.5s ease";
            alertaExito.style.opacity = "0";


            setTimeout(() => {
                alertaExito.classList.add('d-none');
            }, 500);

        }, 5000);
    }
});
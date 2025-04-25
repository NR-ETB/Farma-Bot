$(document).ready(function() {
    // Guarda los valores originales de height
    const originalMainContainerHeight = $('.main-container').css('height');
    const originalContainerBotHeight = $('.container-bot').css('height');

    // Función para aplicar el aumento de height
    function aumentarHeight() {
        $('.main-container').css('height', '1500px');
        $('.container-bot').css('height', '1500px');
        $('.main').css('height', '1160px');
    }

    // Función para revertir a los estilos originales
    function revertirHeight() {
        $('.main-container').css('height', originalMainContainerHeight);
        $('.container-bot').css('height', originalContainerBotHeight);
    }

    // Función para verificar la presencia de la tabla y aplicar/revertir estilos
    function verificarTabla() {
        const tablaPresente = $('.table.table-bordered.mt-3').length > 0;
        if (tablaPresente) {
            aumentarHeight();
        } else {
            revertirHeight();
        }
    }

    // Ejecutar la verificación al cargar la página
    verificarTabla();

    // Observador para detectar cambios en el DOM (para carga dinámica)
    const observer = new MutationObserver(function(mutationsList, observer) {
        for (const mutation of mutationsList) {
            if (mutation.type === 'childList' || mutation.type === 'subtree') {
                verificarTabla();
                break; // Puedes detener la observación si solo necesitas reaccionar a la primera aparición/desaparición
            }
        }
    });

    // Selecciona el elemento padre donde se inserta/elimina la tabla
    const contenedorPrincipal = document.querySelector('.result'); // Ajusta el selector según tu HTML
    if (contenedorPrincipal) {
        observer.observe(contenedorPrincipal, { childList: true, subtree: true });
    }
});
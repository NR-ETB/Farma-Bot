$(document).ready(function() {
    // Guarda los valores originales de height
    const originalMainContainerHeight = $('.main-container').css('height');
    const originalContainerBotHeight = $('.container-bot').css('height');
    
    // Variable para rastrear si la altura ya ha sido aumentada
    let heightAumentado = false;

    // Función para aplicar el aumento de height
    function aumentarHeight() {
        if (!heightAumentado) { // Solo aumenta si no está ya aumentado
            $('.main-container').css('height', '1500px');
            $('.container-bot').css('height', '1500px');
            $('.main').css('height', '1160px');
            heightAumentado = true;
        }
    }

    // Función para revertir a los estilos originales
    function revertirHeight() {
        if (heightAumentado) { // Solo revierte si está aumentado
            $('.main-container').css('height', originalMainContainerHeight);
            $('.container-bot').css('height', originalContainerBotHeight);
            heightAumentado = false;
        }
    }

    // Función para verificar la presencia específicamente de la tabla de sugerencias
    function verificarTablaSugerencias() {
        // Busca el encabezado de sugerencias seguido de una tabla
        const sugerenciasPresentes = $('h2.mt-4:contains("Sugerencias de Reemplazo")').length > 0;
        
        if (sugerenciasPresentes) {
            aumentarHeight();
        } else if (heightAumentado) {
            // Solo revierte si anteriormente se aumentó la altura
            revertirHeight();
        }
    }

    // Ejecutar la verificación al cargar la página
    verificarTablaSugerencias();

    // Observador para detectar cambios en el DOM
    const observer = new MutationObserver(function(mutationsList, observer) {
        for (const mutation of mutationsList) {
            if (mutation.type === 'childList' || mutation.type === 'subtree') {
                verificarTablaSugerencias();
                break;
            }
        }
    });

    // Selecciona el elemento padre donde se inserta/elimina la tabla
    const contenedorPrincipal = document.querySelector('.result');
    if (contenedorPrincipal) {
        observer.observe(contenedorPrincipal, { childList: true, subtree: true });
    }
});
/**
 * Script para mejorar la funcionalidad del buscador y eliminar columnas fijas
 * en el modal de previsualización de Excel.
 */
$(document).ready(function() {    // Función para inicializar el buscador cuando se muestre el modal
    $('#previewModal').on('shown.bs.modal', function() {
        // Asegurarse de que no haya columnas fijas
        setTimeout(function() {
            // Remover cualquier clase o estilo que pueda causar columnas fijas
            $('.dataTables_scrollHead, .dataTables_scrollHeadInner').css({
                'width': '100%',
                'position': 'static'
            });
            
            // Eliminar cualquier columna fija
            $('.DTFC_LeftWrapper, .DTFC_RightWrapper, .DTFC_LeftBodyWrapper, .DTFC_RightBodyWrapper').remove();
            
            // Eliminar también elementos de la columna derecha fija que pudieran quedar
            $('.dataTables_scroll, .DTFC_ScrollWrapper, .DTFC_RightHeadWrapper, .DTFC_LeftHeadWrapper').remove();
            
            // Eliminar cualquier columna que no corresponda a las definidas en la tabla
            $('#previewTable tr').each(function() {
                // Si hay más celdas que encabezados, eliminar las adicionales
                const numHeaderCells = $('#previewTable thead tr:first th').length;
                const row = $(this);
                if (row.find('td, th').length > numHeaderCells) {
                    row.find('td, th').slice(numHeaderCells).remove();
                }
            });
            
            // Reconstruir la tabla para forzar reajuste del layout
            if ($.fn.dataTable.isDataTable('#previewTable')) {
                // Forzar redibujado de la tabla
                $('#previewTable').DataTable().columns.adjust().draw(false);
            }
            
            // Enfocar el campo de búsqueda para facilitar su uso
            $('#table-search').val('').focus();
        }, 200);
    });
    
    // Función de búsqueda mejorada para trabajar sin depender de DataTables
    function buscarEnTabla(texto) {
        if (!texto || texto.length === 0) {
            // Si no hay texto, mostrar todas las filas
            $('#previewTableBody tr').show();
            $('#previewTableBody').unmark();
            return;
        }
        
        // Convertir a minúsculas para búsqueda insensible a mayúsculas/minúsculas
        const textoLower = texto.toLowerCase();
        
        // Buscar en cada fila
        $('#previewTableBody tr').each(function() {
            const fila = $(this);
            const contenido = fila.text().toLowerCase();
            
            if (contenido.indexOf(textoLower) >= 0) {
                fila.show();
            } else {
                fila.hide();
            }
        });
        
        // Resaltar coincidencias
        $('#previewTableBody').unmark();
        if (texto.length > 1) {
            $('#previewTableBody').mark(texto, {
                element: 'span',
                className: 'mark',
                accuracy: 'partially',
                separateWordSearch: false
            });
        }
    }
    
    // Asociar la función de búsqueda al campo de búsqueda
    $(document).on('keyup', '#table-search', function() {
        buscarEnTabla($(this).val());
    });
    
    // Función para limpiar la búsqueda
    $(document).on('click', '#clear-search', function() {
        $('#table-search').val('').focus();
        buscarEnTabla('');
    });
});

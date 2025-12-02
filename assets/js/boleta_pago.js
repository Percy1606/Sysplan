$(document).ready(function() {
    // =================================================================
    // INICIALIZACIÓN Y CARGA DE DATOS INICIALES
    // =================================================================

    // Cargar la lista de trabajadores al iniciar la página
    cargarTrabajadores();

    // =================================================================
    // ASIGNACIÓN DE EVENTOS A LOS BOTONES Y SELECTORES
    // =================================================================

    // Buscar automáticamente al cambiar trabajador, mes o año
    $('#select_trabajador, #select_mes, #input_ano').on('change', function() {
        buscarBoleta(false); // No mostrar alerta al cambiar los selectores
    });

    $('#btnGuardar').on('click', function() {
        guardarBoleta();
    });

    $('#btnExportarPDF').on('click', function() {
        exportarPDF();
    });

    // Eventos para colapsar/expandir secciones de conceptos
    $('.toggle-conceptos').on('click', function() {
        const target = $($(this).data('target'));
        const icon = $(this).find('i');
        target.slideToggle('fast', function() {
            if (target.is(':visible')) {
                icon.removeClass('fa-plus').addClass('fa-minus');
            } else {
                icon.removeClass('fa-minus').addClass('fa-plus');
            }
        });
    });

    // =================================================================
    // FUNCIONES PRINCIPALES
    // =================================================================

    /**
     * Carga la lista de trabajadores desde el endpoint y los añade al selector.
     */
    function cargarTrabajadores() {
        $.ajax({
            url: 'ws/boleta_pago.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'get_trabajadores' },
            success: function(response) {
                if (response.status === 'success') {
                    const select = $('#select_trabajador');
                    select.empty().append('<option value="">Seleccione un trabajador</option>');
                    response.data.forEach(trabajador => {
                        // Corregido: Se usa 'nombresApellidos' que es lo que devuelve el backend ahora.
                        select.append(`<option value="${trabajador.id}">${trabajador.nombresApellidos}</option>`);
                    });
                } else {
                    alert('Error al cargar trabajadores: ' + response.message);
                }
            },
            error: function() {
                alert('No se pudo conectar al servidor para cargar los trabajadores.');
            }
        });
    }

    /**
     * Busca una boleta existente o inicia el proceso para una nueva.
     * @param {boolean} showAlert - Indica si se debe mostrar una alerta de éxito.
     */
    function buscarBoleta(showAlert = false) {
        const id_trabajador = $('#select_trabajador').val();
        const mes = $('#select_mes').val();
        const ano = $('#input_ano').val();

        if (!id_trabajador) {
            limpiarFormulario();
            return;
        }

        $.ajax({
            url: 'ws/boleta_pago.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'get_boleta',
                id_trabajador: id_trabajador,
                mes: mes,
                ano: ano
            },
            success: function(response) {
                if (response.status === 'success') {
                    rellenarFormulario(response.data);
                    if (showAlert) {
                        alert(`Boleta ${response.type} cargada correctamente.`);
                    }
                } else {
                    alert('Error al buscar la boleta: ' + response.message);
                }
            },
            error: function() {
                alert('No se pudo conectar al servidor para buscar la boleta.');
            }
        });
    }

    /**
     * Exporta la boleta actual a un archivo PDF.
     */
    function exportarPDF() {
        const id_boleta = $('#id_boleta').val();

        if (!id_boleta || id_boleta === '0') {
            alert('Por favor, guarde la boleta antes de exportarla a PDF.');
            return;
        }

        // Abrimos el script de generación de PDF en una nueva pestaña
        window.open(`generar_pdf_boleta.php?id_boleta=${id_boleta}`, '_blank');
    }

    let allIngresos = [];
    let allDescuentos = [];
    let sueldoBasicoTrabajador = 0; // Variable para almacenar el sueldo básico

    /**
     * Limpia todos los campos del formulario de la boleta.
     */

    function limpiarFormulario() {
        $('#id_boleta').val('0');
        $('#nombres_apellidos').text('');
        $('#documento').text('');
        $('#fecha_ingreso').text('');
        $('#area').text('');
        $('#sueldo_basico').text('S/ 0.00');
        $('#dias_laborados').text('0');
        $('#dias_faltados').text('0');
        $('#dias_mes').text('0');
        $('#table_ingresos tbody').empty();
        $('#table_descuentos tbody').empty();
        $('#observaciones').val('');
        allIngresos = [];
        allDescuentos = [];
        sueldoBasicoTrabajador = 0; // Resetear sueldo básico
        actualizarTotales(); // Resets totals to S/ 0.00
    }

    function rellenarFormulario(data) {
        // Datos del trabajador y asistencia
        $('#nombres_apellidos').text(data.nombres_apellidos || '');
        $('#documento').text(data.documento || '');
        $('#fecha_ingreso').text(data.fecha_ingreso || '');
        $('#area').text(data.area || ''); // TODO: Obtener el área real del trabajador
        sueldoBasicoTrabajador = parseFloat(data.sueldo_basico_trabajador) || 0; // Almacenar el sueldo básico
        $('#sueldo_basico').text('S/ ' + sueldoBasicoTrabajador.toFixed(2));
        $('#dias_laborados').text(data.dias_laborados || '0');
        $('#dias_faltados').text(data.dias_faltados || '0');
        $('#dias_mes').text(data.dias_mes || '0');

        // Observaciones y ID de la boleta
        $('#observaciones').val(data.observaciones || '');
        $('#id_boleta').val(data.id || '0');

        // Guardar todos los conceptos para el filtrado
        allIngresos = data.conceptos_disponibles.ingresos || [];
        allDescuentos = data.conceptos_disponibles.descuentos || [];

        // Renderizar conceptos iniciales
        renderConceptos(allIngresos, '#table_ingresos tbody', 'ingreso');
        renderConceptos(allDescuentos, '#table_descuentos tbody', 'descuento');

        // Adjuntar eventos a los checkboxes y campos de monto
        $('.concepto-checkbox, .concepto-monto-input').off('change input').on('change input', actualizarTotales);

        actualizarTotales();
    }

    /**
     * Renderiza los conceptos en la tabla especificada.
     * @param {Array} conceptos - Array de objetos concepto.
     * @param {string} tbodySelector - Selector del tbody de la tabla.
     * @param {string} tipo - 'ingreso' o 'descuento'.
     */
    function renderConceptos(conceptos, tbodySelector, tipo) {
        const tbody = $(tbodySelector);
        tbody.empty();
        conceptos.forEach(concepto => {
            const isChecked = concepto.aplicar ? 'checked' : '';
            const row = `
                <tr data-codigo="${concepto.codigo}" data-monto="${concepto.monto}">
                    <td>${concepto.codigo}</td>
                    <td>${concepto.descripcion}</td>
                    <td>
                        <input type="number" class="form-control concepto-monto-input" 
                               id="${tipo}_${concepto.codigo.replace(/\s/g, '_')}" 
                               value="${concepto.monto || '0.00'}" step="0.01">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="concepto-checkbox" ${isChecked}>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    /**
     * Guarda los datos de la boleta en la base de datos.
     */
    function guardarBoleta() {
        const id_trabajador = $('#select_trabajador').val();
        if (!id_trabajador) {
            alert('Por favor, seleccione un trabajador antes de guardar.');
            return;
        }

        // Recolectar todos los datos del formulario
        const boletaData = {
            op: 'save_boleta',
            id_boleta: $('#id_boleta').val(),
            id_trabajador: id_trabajador,
            mes: $('#select_mes').val(),
            ano: $('#input_ano').val(),
            dias_laborados: $('#dias_laborados').text(),
            dias_faltados: $('#dias_faltados').text(),
            ingresos_dinamicos: [],
            descuentos_dinamicos: [],

            total_ingresos: parseFloat($('#total_ingresos').text().replace('S/ ', '')),
            total_descuentos: parseFloat($('#total_descuentos').text().replace('S/ ', '')),
            total_neto: parseFloat($('#total_neto').text().replace('S/ ', '')),
            observaciones: $('#observaciones').val()
        };

        // Recolectar ingresos dinámicos seleccionados
        $('#table_ingresos tbody tr').each(function() {
            const checkbox = $(this).find('.concepto-checkbox');
            const montoInput = $(this).find('.concepto-monto-input');
            if (checkbox.is(':checked')) {
                boletaData.ingresos_dinamicos.push({
                    codigo_concepto: $(this).data('codigo'),
                    descripcion: $(this).find('td:nth-child(2)').text(),
                    monto: montoInput.val(),
                    aplicar: true
                });
            }
        });

        // Recolectar descuentos dinámicos seleccionados
        $('#table_descuentos tbody tr').each(function() {
            const checkbox = $(this).find('.concepto-checkbox');
            const montoInput = $(this).find('.concepto-monto-input');
            if (checkbox.is(':checked')) {
                boletaData.descuentos_dinamicos.push({
                    codigo_concepto: $(this).data('codigo'),
                    descripcion: $(this).find('td:nth-child(2)').text(),
                    monto: montoInput.val(),
                    aplicar: true
                });
            }
        });

        $.ajax({
            url: 'ws/boleta_pago.php',
            type: 'POST',
            dataType: 'json',
            data: boletaData,
            success: function(response) {
                if (response.status === 'success') {
                    if(response.new_id) {
                        $('#id_boleta').val(response.new_id);
                    }
                    alert(response.message);
                    
                    // Disparar un evento para que el header actualice las notificaciones
                    $(document).trigger('actualizarNotificaciones');

                    limpiarFormulario(); // Limpiar el formulario después de guardar exitosamente
                    cargarTrabajadores(); // Recargar trabajadores para asegurar que el selector esté actualizado
                } else {
                    // Mensaje de error más detallado
                    const errorMessage = 'Error al guardar la boleta: ' + (response.message || 'No se recibió un mensaje de error específico.');
                    console.error('Respuesta del servidor (error):', response);
                    alert(errorMessage);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Error de conexión o de servidor
                console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
                console.error('Respuesta completa del servidor:', jqXHR.responseText);
                alert('No se pudo conectar al servidor o ocurrió un error inesperado. Revise la consola del navegador (F12) para más detalles.');
            }
        });
    }

    /**
     * Calcula y actualiza los totales de ingresos, descuentos y neto a pagar.
     */
    function actualizarTotales() {
        let totalIngresos = sueldoBasicoTrabajador; // Empezar con el sueldo básico del trabajador
        let totalDescuentos = 0;

        // Sumar todos los campos de ingresos dinámicos seleccionados
        $('#table_ingresos tbody tr').each(function() {
            const checkbox = $(this).find('.concepto-checkbox');
            if (checkbox.is(':checked')) {
                totalIngresos += parseFloat($(this).find('.concepto-monto-input').val()) || 0;
            }
        });

        // Sumar todos los campos de descuentos dinámicos seleccionados
        $('#table_descuentos tbody tr').each(function() {
            const checkbox = $(this).find('.concepto-checkbox');
            if (checkbox.is(':checked')) {
                totalDescuentos += parseFloat($(this).find('.concepto-monto-input').val()) || 0;
            }
        });

        const netoPagar = totalIngresos - totalDescuentos;

        // Actualizar la interfaz
        $('#total_ingresos').text('S/ ' + totalIngresos.toFixed(2));
        $('#total_descuentos').text('S/ ' + totalDescuentos.toFixed(2));
        $('#total_neto').text('S/ ' + netoPagar.toFixed(2));
    }
});

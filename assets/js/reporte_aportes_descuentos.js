$(document).ready(function() {
    console.log("reporte_aportes_descuentos.js cargado y ejecutándose.");

    // Cargar el reporte al iniciar la página
    loadReporteAportesDescuentos();

    // Recargar el reporte cuando se hace clic en el botón de filtrar
    $('#btn_filtrar').on('click', function() {
        loadReporteAportesDescuentos();
    });

    function loadReporteAportesDescuentos() {
        if ($.fn.DataTable.isDataTable('#tb_reporte_aportes_descuentos')) {
            $('#tb_reporte_aportes_descuentos').DataTable().destroy();
        }

        // Mostrar un mensaje de carga
        $('#tb_reporte_aportes_descuentos tbody').html('<tr><td colspan="7" class="text-center">Cargando datos...</td></tr>');

        // Retrasar la inicialización de DataTables para asegurar que el DOM esté completamente listo
        setTimeout(function() {
            var tableElement = $('#tb_reporte_aportes_descuentos');
            console.log("Elemento de la tabla:", tableElement);
            if (tableElement.length && tableElement.is('table')) {
                tableElement.DataTable({
                    "ajax": {
                        "url": "ws/reporte_aportes_descuentos.php",
                        "type": "POST",
                        "data": {
                            op: 'get_reporte',
                            mes: $('#filtro_mes').val(),
                            anio: $('#filtro_anio').val()
                        },
                        "dataSrc": function (json) {
                            if (json === null || json.status === 'error' || !json.data) {
                                console.error("Error o datos nulos recibidos del servidor:", json);
                                if (json && json.debug_info) {
                                    console.warn("Información de depuración del servidor:", json.debug_info);
                                    alert("Error al cargar los datos: " + json.debug_info.message + "\nConsulta la consola para más detalles (SQL y parámetros).");
                                } else {
                                    alert("Error al cargar los datos: " + (json ? (json.message || "Respuesta vacía o inválida del servidor.") : "Respuesta vacía o inválida del servidor."));
                                }
                                return [];
                            }
                            console.log("Datos recibidos del servidor:", json.data);
                            if (json.debug_info) {
                                console.warn("Información de depuración del servidor (aunque se encontraron datos):", json.debug_info);
                            }
                            return json.data;
                        },
                        "error": function (xhr, error, thrown) {
                            alert("Error al cargar los datos: " + thrown + ". Consulta la consola para más detalles.");
                            console.error("Error AJAX:", xhr.responseText);
                            // Intenta parsear la respuesta de error si es JSON
                            try {
                                const errorResponse = JSON.parse(xhr.responseText);
                                console.error("Respuesta de error del servidor (JSON):", errorResponse);
                                if (errorResponse.message) {
                                    alert("Error del servidor: " + errorResponse.message);
                                }
                            } catch (e) {
                                console.error("Respuesta de error del servidor (texto plano):", xhr.responseText);
                            }
                        }
                    },
                    "columns": [{
                        "data": "id"
                    }, {
                        "data": "trabajador"
                    }, {
                        "data": "concepto"
                    }, {
                        "data": "tipo"
                    }, {
                        "data": "monto"
                    }, {
                        "data": "fecha"
                    }, {
                        "data": "estado" // Nueva columna para el estado
                    }],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                    }
                });
            } else {
                console.error("El elemento de la tabla #tb_reporte_aportes_descuentos no se encontró o no es una tabla válida.");
                $('#tb_reporte_aportes_descuentos tbody').html('<tr><td colspan="7" class="text-center text-danger">Error: No se pudo inicializar la tabla.</td></tr>');
            }
        }, 100); // Retraso de 100ms
    }

    window.editRegistro = function(id, source_table) {
    }

    window.deleteRegistro = function(id, source_table) {
        if (confirm('¿Está seguro de eliminar este registro (ID: ' + id + ' de ' + source_table + ')? Esta acción es irreversible.')) {
            $.ajax({
                url: 'ws/reporte_aportes_descuentos.php',
                type: 'POST',
                data: { op: 'delete_registro', id: id, source_table: source_table },
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    if (response.status === 'success') {
                        $('#tb_reporte_aportes_descuentos').DataTable().ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar registro:", status, error, xhr.responseText);
                    alert("Error al eliminar registro: " + xhr.responseText);
                }
            });
        }
    }

    // Función para cargar los conceptos en el select del modal
    function loadConceptos(tipo, selectedConceptoNombre = null) {
        $('#edit_concepto').empty(); // Limpiar opciones anteriores
        $.ajax({
            url: 'ws/reporte_aportes_descuentos.php',
            type: 'POST',
            data: { op: 'get_conceptos', tipo: tipo },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data.length > 0) {
                    $.each(response.data, function(i, concepto) {
                        $('#edit_concepto').append($('<option>', {
                            value: concepto.nombre, // Usar el nombre como valor para el backend
                            text: concepto.nombre
                        }));
                    });
                    // Seleccionar el concepto actual si se proporciona
                    if (selectedConceptoNombre) {
                        $('#edit_concepto').val(selectedConceptoNombre);
                    }
                } else {
                    console.warn("No se encontraron conceptos para el tipo: " + tipo);
                    $('#edit_concepto').append($('<option>', {
                        value: '',
                        text: 'No hay conceptos disponibles'
                    }));
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar conceptos:", status, error, xhr.responseText);
                alert("Error al cargar conceptos: " + xhr.responseText);
            }
        });
    }

    // Implementación de la función de edición
    window.editRegistro = function(id, source_table) {
        $.ajax({
            url: 'ws/reporte_aportes_descuentos.php',
            type: 'POST',
            data: { op: 'get_registro_by_id', id: id, source_table: source_table },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    var registro = response.data;
                    $('#edit_id_registro').val(registro.id);
                    $('#edit_source_table').val(registro.source_table);
                    $('#edit_trabajador').val(registro.trabajador);
                    $('#edit_tipo').val(registro.tipo);
                    $('#edit_monto').val(registro.monto);

                    // Cargar los conceptos según el tipo de registro
                    var tipoConcepto = (registro.tipo === 'Aporte') ? 'aporte' : 'descuento';
                    loadConceptos(tipoConcepto, registro.concepto);

                    $('#editRegistroModal').modal('show');
                } else {
                    alert("Error al obtener los datos del registro: " + (response.message || "Registro no encontrado."));
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener registro para edición:", status, error, xhr.responseText);
                alert("Error al obtener registro para edición: " + xhr.responseText);
            }
        });
    }

    // Evento para guardar los cambios del modal
    $('#btnGuardarCambios').on('click', function() {
        var id = $('#edit_id_registro').val();
        var source_table = $('#edit_source_table').val();
        var monto = $('#edit_monto').val();
        var concepto = $('#edit_concepto').val();

        if (!monto || !concepto) {
            alert('Por favor, complete todos los campos.');
            return;
        }

        $.ajax({
            url: 'ws/reporte_aportes_descuentos.php',
            type: 'POST',
            data: {
                op: 'save_registro',
                id: id,
                source_table: source_table,
                monto: monto,
                concepto: concepto
            },
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.status === 'success') {
                    $('#editRegistroModal').modal('hide');
                    $('#tb_reporte_aportes_descuentos').DataTable().ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al guardar cambios:", status, error, xhr.responseText);
                alert("Error al guardar cambios: " + xhr.responseText);
            }
        });
    });
});

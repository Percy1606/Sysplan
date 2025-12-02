$(document).ready(function() {
    console.log("reporte_boletas_pagadas.js cargado y ejecutándose."); // Log para verificar la carga del script


    // Cargar boletas al iniciar la página
    cargarBoletas();

    // Evento para el botón de búsqueda
    $('#btnBuscar').on('click', function(e) {
        e.preventDefault(); // Evitar el envío del formulario
        cargarBoletas();
    });

    function cargarBoletas() {
        // Obtener valores de los filtros
        const mes = $('#filtroMes').val();
        const anio = $('#filtroAnio').val();

        $.ajax({
            url: 'ws/reporte_boletas.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'get_boletas',
                mes: mes,
                anio: anio
            },
            success: function(response) {
                const tbody = $('#boletas_table_body');
                tbody.empty(); // Limpiar filas existentes
                console.log("Respuesta del servidor:", response); // Log de la respuesta completa

                if (response.status === 'success' && response.data && response.data.length > 0) {
                    response.data.forEach(boleta => {
                        const fechaPago = boleta.fecha_creacion ? new Date(boleta.fecha_creacion).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' }) : 'N/A';
                        const periodo = `${obtenerNombreMes(boleta.mes)} ${boleta.ano}`;
                        const row = `
                            <tr>
                                <td>${boleta.id}</td>
                                <td>${boleta.nombresApellidos}</td>
                                <td>${periodo}</td>
                                <td>S/ ${parseFloat(boleta.total_neto).toFixed(2)}</td>
                                <td>${fechaPago}</td>
                                <td>
                                    <button class="btn btn-info btn-sm btn-ver-detalle" data-id="${boleta.id}" title="Ver Detalle"><i class="fa fa-eye"></i></button>
                                    <button class="btn btn-danger btn-sm btn-eliminar-boleta" data-id="${boleta.id}" title="Eliminar Boleta"><i class="fa fa-trash"></i></button>
                                    <button class="btn btn-primary btn-sm btn-imprimir-boleta-tabla" data-id="${boleta.id}" title="Imprimir Boleta"><i class="fa fa-print"></i></button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="6" class="text-center">' + (response.message || 'No se encontraron boletas.') + '</td></tr>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error AJAX al cargar boletas:", textStatus, errorThrown);
                console.error("Respuesta completa del servidor:", jqXHR.responseText);
                alert('Error al cargar las boletas. Consulte la consola para más detalles.');
            }
        });
    }

    // Función para obtener el nombre del mes
    function obtenerNombreMes(numeroMes) {
        const meses = [
            'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO',
            'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'
        ];
        return meses[numeroMes - 1] || 'Desconocido';
    }

    // Evento para eliminar boleta
    $(document).on('click', '.btn-eliminar-boleta', function() {
        const id_boleta = $(this).data('id');
        
        if (confirm('¿Está seguro de que desea eliminar esta boleta? Esta acción es irreversible.')) {
            $('#boletaIdToDelete').val(id_boleta);
            $('#modalConfirmarEliminacion').modal('show');
        }
    });

    $(document).on('click', '#btnConfirmarEliminacion', function() {
        const id_boleta = $('#boletaIdToDelete').val();
        const admin_password = $('#adminPasswordInput').val();

        if (admin_password.trim() === '') {
            alert('Por favor, ingrese la contraseña del administrador.');
            return;
        }

        $.ajax({
            url: 'ws/reporte_boletas.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'delete_boleta',
                id: id_boleta,
                admin_password: admin_password
            },
            success: function(response) {
                $('#modalConfirmarEliminacion').modal('hide');
                $('#adminPasswordInput').val(''); // Limpiar contraseña

                if (response.status === 'success') {
                    alert(response.message);
                    cargarBoletas();
                } else {
                    alert('Error al eliminar la boleta: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#modalConfirmarEliminacion').modal('hide');
                $('#adminPasswordInput').val(''); // Limpiar contraseña
                console.error("Error AJAX:", textStatus, errorThrown);
                console.error("Respuesta del servidor:", jqXHR.responseText);
                alert('Error al eliminar la boleta. Consulte la consola para más detalles.');
            }
        });
    });

    // Evento para ver detalle de boleta (abre nueva página)
    $(document).on('click', '.btn-ver-detalle', function() {
        const id_boleta = $(this).data('id');
        window.open(`detalle_boleta.php?id=${id_boleta}`, '_blank');
    });

    // Evento para imprimir desde la tabla principal (abre una nueva pestaña para la impresión de tickets)
    $(document).on("click", ".btn-imprimir-boleta-tabla", function() {
        const id_boleta = $(this).data("id");
        // Abrir una nueva ventana que cargará el script de impresión del ticket
        const printWindow = window.open(`ArchivosImpresion/imprimir_boleta_ticket.php?id_boleta=${id_boleta}`, '_blank');
        // Opcional: cerrar la ventana de impresión después de un tiempo si no se cierra automáticamente
        setTimeout(() => {
            if (printWindow && !printWindow.closed) {
                // printWindow.close(); // Descomentar si se desea cerrar automáticamente
            }
        }, 5000); // Cerrar después de 5 segundos
    });

    $(document).on('click', '#btnImprimirTodo', function() {
        const ids = [];
        $('.btn-imprimir-boleta-tabla').each(function() {
            ids.push($(this).data('id'));
        });

        if (ids.length === 0) {
            alert('No hay boletas para imprimir.');
            return;
        }

        const terminal = document.cookie.split('; ').find(row => row.startsWith('t='))?.split('=')[1] || 'default';

        if (confirm('¿Desea enviar todas las boletas a la cola de impresión?')) {
            $.ajax({
                url: 'ws/reporte_boletas.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    op: 'queue_print_all',
                    ids: JSON.stringify(ids),
                    terminal: terminal
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error AJAX al encolar impresión:", textStatus, errorThrown);
                    alert('Error al enviar a la cola de impresión. Consulte la consola.');
                }
            });
        }
    });

    // Evento para imprimir desde el modal (imprime el HTML del modal)
    $(document).on('click', '#btnImprimirModal', function() {
        const printContent = $('#boleta_final_modal').html();
        const originalBody = document.body.innerHTML;
        
        // Crear una ventana de impresión temporal
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Boleta de Pago</title>');
        // Incluir los estilos del modal directamente para la impresión
        printWindow.document.write(`
            <style>
                body { font-family: "Helvetica", sans-serif; font-size: 12px; margin: 0; padding: 0; }
                .boleta-print-area {
                    font-family: "Helvetica", sans-serif;
                    font-size: 12px;
                    width: 100%;
                    margin: 0 auto;
                    padding: 10px;
                    box-sizing: border-box;
                }
                .boleta-print-area .header { text-align: left; margin-bottom: 15px; }
                .boleta-print-area .header p { margin: 2px 0; }
                .boleta-print-area .header h1 { margin: 0; font-size: 16px; }
                .boleta-print-area .details-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                .boleta-print-area .details-table th, .boleta-print-area .details-table td { border: 1px solid #ddd; padding: 5px; text-align: left; }
                .boleta-print-area .details-table th { background-color: #f2f2f2; font-size: 10px; }
                .boleta-print-area .details-table td { font-size: 10px; }
                .boleta-print-area .totals-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                .boleta-print-area .totals-table td { border: 1px solid #ddd; padding: 5px; }
                .boleta-print-area .totals-table .label { font-weight: bold; }
                .boleta-print-area .text-right { text-align: right; }
                .boleta-print-area .text-center { text-align: center; }
                .boleta-print-area hr { border: 0; border-top: 1px solid #eee; margin: 10px 0; }
                /* Estilos específicos para la impresión */
                @media print {
                    body {
                        -webkit-print-color-adjust: exact; /* Para Chrome/Safari */
                        print-color-adjust: exact; /* Estándar */
                    }
                    .header p {
                        margin: 0; /* Eliminar márgenes extra en la impresión */
                    }
                    .header p:nth-child(5), /* BOLETA DE PAGO */
                    .header p:nth-child(6), /* Periodo */
                    .header p:nth-child(7), /* Fecha de Emisión */
                    .header p:nth-child(8), /* DATOS DEL TRABAJADOR */
                    .header p:nth-child(9), /* Nombre */
                    .header p:nth-child(10), /* DNI */
                    .header p:nth-child(11) { /* Área */
                        text-align: center !important;
                    }
                    .totals-table .label {
                        font-weight: bold !important;
                        font-size: 1.1em !important;
                    }
                    .totals-table .text-right {
                        font-weight: bold !important;
                        font-size: 1.1em !important;
                    }
                    .signatures-container {
                        margin-top: 80px !important; /* Aumentado el espacio para la firma */
                        display: flex;
                        justify-content: space-around;
                        text-align: center;
                    }
                }
            </style>
        `);
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="boleta-print-area">' + printContent + '</div>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    });

    // Función para generar el HTML de la boleta para el modal de detalle
    function generateBoletaDetailHtml(boleta, empresa, ingresos, descuentos) {
        let htmlContent = "";
        htmlContent += `
            <div class="header" style="text-align: left;">
                <p style="font-size: 14px; font-weight: bold;">${empresa.nombre_empresa || 'USQAY ES CLAVE'}</p>
                <p>RUC: ${empresa.ruc_empresa || 'N/A'}</p>
                <p>${empresa.direccion_empresa || 'Jr. Tacna Nro. 258 Int. 1b Piura Cercado'}</p>
                <p>${empresa.telefono_empresa || '943 741 577'}</p>
                <p style="font-size: 16px; font-weight: bold; margin-top: 15px; text-align: center;">BOLETA DE PAGO</p>
                <p style="text-align: center;">Periodo: ${boleta.periodo_formateado}</p>
                <p style="text-align: center;">Fecha de Emisión: ${boleta.fecha_pago_formateada}</p>
                <p style="margin-top: 15px; font-weight: bold; text-align: center;">DATOS DEL TRABAJADOR</p>
                <p style="text-align: center;">Nombre: ${boleta.nombresApellidos}</p>
                <p style="text-align: center;">DNI: ${boleta.documento}</p>
                <p style="text-align: center;">Área: ${boleta.area_trabajador || 'N/A'}</p>
            </div>
        `;

        // Tabla de Ingresos
        htmlContent += `
            <table class="details-table">
                <thead><tr><th colspan="2">Ingresos</th></tr></thead>
                <tbody>`;
        let totalIngresos = 0;
        if (ingresos.length > 0) {
            ingresos.forEach(item => {
                const monto = parseFloat(item.monto);
                htmlContent += `<tr><td>${item.descripcion}</td><td class="text-right">S/ ${monto.toFixed(2)}</td></tr>`;
                totalIngresos += monto;
            });
        } else {
            htmlContent += `<tr><td colspan="2">No hay ingresos registrados.</td></tr>`;
        }
        htmlContent += `</tbody></table>`;

        // Tabla de Descuentos
        htmlContent += `
            <table class="details-table" style="margin-top:10px;">
                <thead><tr><th colspan="2">Descuentos</th></tr></thead>
                <tbody>`;
        let totalDescuentos = 0;
        if (descuentos.length > 0) {
            descuentos.forEach(item => {
                const monto = parseFloat(item.monto);
                htmlContent += `<tr><td>${item.descripcion}</td><td class="text-right">S/ ${monto.toFixed(2)}</td></tr>`;
                totalDescuentos += monto;
            });
        } else {
            htmlContent += `<tr><td colspan="2">No hay descuentos registrados.</td></tr>`;
        }
        htmlContent += `</tbody></table>`;

        // Tabla de Totales
        htmlContent += `
            <div class="text-right" style="margin-top: 10px; font-size: 14px;">
                <table class="totals-table">
                    <tr>
                        <td class="label">Total Ingresos:</td>
                        <td class="text-right">S/ ${totalIngresos.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Descuentos:</td>
                        <td class="text-right">S/ ${totalDescuentos.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="label" style="font-weight: bold; font-size: 1.1em;">Sueldo Total (Neto):</td>
                        <td class="text-right" style="font-weight: bold; font-size: 1.1em;">S/ ${parseFloat(boleta.total_neto).toFixed(2)}</td>
                    </tr>
                </table>
            </div>
            <div class="signatures-container" style="margin-top: 60px; display: flex; justify-content: space-around; text-align: center;">
                <div>
                    <p>_________________________</p>
                    <p>Firma del Empleador</p>
                </div>
                <div>
                    <p>_________________________</p>
                    <p>Firma del Trabajador</p>
                </div>
            </div>
            <div class="text-center" style="margin-top: 20px; font-size: 10px;">
                <p>Este documento es una representación de su boleta de pago.</p>
                <p style="margin: 5px 0;">Para cualquier consulta, contacte a recursos humanos.</p>
                <p style="margin: 10px 0 0 0;">Generado por SysPlan</p>
            </div>
        `;
        return htmlContent;
    }

    // Función para generar el HTML de la boleta para impresión (formato de ticket)
    function generateBoletaPrintHtml(boleta, empresa, ingresos, descuentos, aportes_trabajador, aportes_empleador) {
        let htmlContent = "";
        htmlContent += `
            <div class="header" style="text-align: center; font-weight: bold;">
                BOLETA DE PAGO
                <br>
                SysPlan
            </div>
            <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>
            <div>Fecha: ${new Date().toLocaleDateString("es-ES", { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' })}</div>
            <div>Periodo: ${boleta.mes}/${boleta.anio}</div>
            <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>
            <div>Trabajador: ${boleta.nombresApellidos}</div>
            <div>Documento: ${boleta.documento}</div>
            <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>

            <h3 style="margin: 5px 0;">INGRESOS</h3>
        `;
        if (ingresos && ingresos.length > 0) {
            ingresos.forEach(item => {
                htmlContent += `
                    <div style="display: flex; justify-content: space-between;">
                        <span style="width: 70%;">${item.descripcion}</span>
                        <span style="width: 30%; text-align: right;">${parseFloat(item.monto).toFixed(2)}</span>
                    </div>
                `;
            });
        } else {
            htmlContent += `<div>No hay ingresos registrados.</div>`;
        }
        htmlContent += `<div style="border-top: 1px dashed #000; margin: 5px 0;"></div>`;

        htmlContent += `<h3 style="margin: 5px 0;">DESCUENTOS</h3>`;
        if (descuentos && descuentos.length > 0) {
            descuentos.forEach(item => {
                htmlContent += `
                    <div style="display: flex; justify-content: space-between;">
                        <span style="width: 70%;">${item.descripcion}</span>
                        <span style="width: 30%; text-align: right;">${parseFloat(item.monto).toFixed(2)}</span>
                    </div>
                `;
            });
        } else {
            htmlContent += `<div>No hay descuentos registrados.</div>`;
        }
        htmlContent += `<div style="border-top: 1px dashed #000; margin: 5px 0;"></div>`;

        htmlContent += `<h3 style="margin: 5px 0;">APORTES TRABAJADOR</h3>`;
        if (aportes_trabajador && aportes_trabajador.length > 0) {
            aportes_trabajador.forEach(item => {
                htmlContent += `
                    <div style="display: flex; justify-content: space-between;">
                        <span style="width: 70%;">${item.nombre_concepto}</span>
                        <span style="width: 30%; text-align: right;">${parseFloat(item.monto).toFixed(2)}</span>
                    </div>
                `;
            });
        } else {
            htmlContent += `<div>No hay aportes de trabajador registrados.</div>`;
        }
        htmlContent += `<div style="border-top: 1px dashed #000; margin: 5px 0;"></div>`;

        htmlContent += `
            <div style="display: flex; justify-content: space-between;">
                <span style="width: 70%;"><b>TOTAL INGRESOS:</b></span>
                <span style="width: 30%; text-align: right;"><b>${parseFloat(boleta.total_ingresos).toFixed(2)}</b></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="width: 70%;"><b>TOTAL DESCUENTOS:</b></span>
                <span style="width: 30%; text-align: right;"><b>${parseFloat(boleta.total_descuentos).toFixed(2)}</b></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="width: 70%;"><b>NETO A PAGAR:</b></span>
                <span style="width: 30%; text-align: right;"><b>${parseFloat(boleta.total_neto).toFixed(2)}</b></span>
            </div>
            <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>
        `;

        htmlContent += `<h3 style="margin: 5px 0;">APORTES EMPLEADOR</h3>`;
        if (aportes_empleador && aportes_empleador.length > 0) {
            aportes_empleador.forEach(item => {
                htmlContent += `
                    <div style="display: flex; justify-content: space-between;">
                        <span style="width: 70%;">${item.nombre_concepto}</span>
                        <span style="width: 30%; text-align: right;">${parseFloat(item.monto).toFixed(2)}</span>
                    </div>
                `;
            });
        } else {
            htmlContent += `<div>No hay aportes de empleador registrados.</div>`;
        }
        htmlContent += `<div style="border-top: 1px dashed #000; margin: 5px 0;"></div>`;
        htmlContent += `<div style="text-align: center;">-- Fin de la Boleta --</div>`;

        return htmlContent;
    }

    // Nueva función para obtener el HTML de la boleta para impresión o modal
    function getBoletaHtmlForPrint(id_boleta, callback) {
        $.ajax({
            url: 'ws/reporte_boletas.php', // Usar el WS para obtener todos los datos necesarios
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'get_boleta_details',
                id: id_boleta
            },
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const boleta = response.data.boleta;
                    const empresa = response.data.config_empresa;
                    const ingresos = response.data.ingresos || [];
                    const descuentos = response.data.descuentos || [];
                    const aportes_trabajador = response.data.aportes_trabajador || [];
                    const aportes_empleador = response.data.aportes_empleador || [];
                    callback(true, { boleta, empresa, ingresos, descuentos, aportes_trabajador, aportes_empleador }, response);
                } else {
                    alert('Error al cargar el detalle de la boleta: ' + (response.message || 'Desconocido'));
                    callback(false, null, response);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error AJAX al cargar detalle de boleta:", textStatus, errorThrown);
                console.error("Respuesta completa del servidor:", jqXHR.responseText);
                alert('Error al cargar el detalle de la boleta. Consulte la consola para más detalles.');
                callback(false, null, null);
            }
        });
    }

    // Evento para imprimir desde el modal (imprime el HTML del modal)
    $(document).on("click", "#btnImprimirModal", function() {
        const id_boleta = $("#modalDetalleBoleta").data("boletaId"); // Obtener el ID de la boleta del modal
        if (!id_boleta) {
            alert("No se pudo obtener el ID de la boleta para imprimir.");
            return;
        }

        getBoletaHtmlForPrint(id_boleta, function(success, data, response) {
            if (success) {
                const htmlContent = generateBoletaPrintHtml(data.boleta, data.empresa, data.ingresos, data.descuentos, data.aportes_trabajador, data.aportes_empleador);
                printBoletaContent(htmlContent);
            }
        });
    });

    // Función para imprimir el contenido de la boleta
    function printBoletaContent(htmlContent) {
        const printWindow = window.open("", "_blank");
        printWindow.document.write("<html><head><title>Boleta de Pago</title>");
        printWindow.document.write(`
            <style>
                body { font-family: 'Courier New', Courier, monospace; font-size: 12px; max-width: 300px; margin: 0; padding: 5px; }
                .header { text-align: center; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .line { border-top: 1px dashed #000; margin: 5px 0; }
                .item { display: flex; justify-content: space-between; }
                .item .name { width: 70%; }
                .item .amount { width: 30%; text-align: right; }
                h3 { margin: 5px 0; }
            </style>
        `);
        printWindow.document.write("</head><body>");
        printWindow.document.write("<div class=\"boleta-print-area\">" + htmlContent + "</div>");
        printWindow.document.write("</body></html>");
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }

    function cargarDetalleBoletaEnModal(id_boleta) {
        getBoletaHtmlForPrint(id_boleta, function(success, data, response) {
            if (success) {
                const htmlContent = generateBoletaDetailHtml(data.boleta, data.empresa, data.ingresos, data.descuentos);
                $("#boleta_final_modal").html(htmlContent);
                $("#modalDetalleBoleta").data("boletaResponse", response);
                $("#modalDetalleBoleta").data("boletaId", id_boleta); // Guardar el ID para la impresión directa
                $("#modalDetalleBoleta").modal("show");
            }
        });
    }

    // Función para generar el ticket POS en texto plano
    function generatePosTicket(boleta, empresa, ingresos, descuentos) {
        const width = 48;
        let ticket = '';

        // Función auxiliar para centrar texto
        const centerText = (text) => {
            const padding = Math.max(0, width - text.length);
            const padLeft = Math.floor(padding / 2);
            const padRight = padding - padLeft;
            return ' '.repeat(padLeft) + text + ' '.repeat(padRight);
        };

        // Función auxiliar para alinear izquierda y derecha
        const alignText = (left, right) => {
            const space = Math.max(1, width - left.length - right.length);
            return left + ' '.repeat(space) + right;
        };

        // Encabezado
        ticket += centerText(empresa.nombre_empresa || 'USQAY ES CLAVE') + '\n';
        ticket += centerText('RUC: ' + (empresa.ruc_empresa || 'N/A')) + '\n';
        ticket += centerText(empresa.direccion_empresa || 'Jr. Tacna Nro. 258 Int. 1b Piura Cercado') + '\n';
        ticket += centerText('Telf: ' + (empresa.telefono_empresa || '943 741 577')) + '\n';
        ticket += '\n';
        ticket += centerText('BOLETA DE PAGO') + '\n';
        ticket += centerText('Periodo: ' + boleta.periodo_formateado) + '\n';
        ticket += centerText('Fecha Emision: ' + boleta.fecha_pago_formateada) + '\n';
        ticket += '\n';
        ticket += centerText('DATOS DEL TRABAJADOR') + '\n';
        ticket += alignText('Nombre:', boleta.nombre_trabajador) + '\n';
        ticket += alignText('DNI:', boleta.dni_trabajador) + '\n';
        ticket += alignText('Area:', (boleta.area_trabajador || 'N/A')) + '\n';
        ticket += '-'.repeat(width) + '\n';

        // Ingresos
        ticket += centerText('INGRESOS') + '\n';
        ticket += '-'.repeat(width) + '\n';
        let totalIngresos = 0;
        if (ingresos.length > 0) {
            ingresos.forEach(item => {
                const monto = parseFloat(item.monto).toFixed(2);
                const desc = item.descripcion.substring(0, width - 10); // Limitar descripción
                ticket += alignText(desc, 'S/ ' + monto) + '\n';
                totalIngresos += parseFloat(item.monto);
            });
        } else {
            ticket += centerText('No hay ingresos registrados.') + '\n';
        }
        ticket += '-'.repeat(width) + '\n';

        // Descuentos
        ticket += centerText('DESCUENTOS') + '\n';
        ticket += '-'.repeat(width) + '\n';
        let totalDescuentos = 0;
        if (descuentos.length > 0) {
            descuentos.forEach(item => {
                const monto = parseFloat(item.monto).toFixed(2);
                const desc = item.descripcion.substring(0, width - 10); // Limitar descripción
                ticket += alignText(desc, 'S/ ' + monto) + '\n';
                totalDescuentos += parseFloat(item.monto);
            });
        } else {
            ticket += centerText('No hay descuentos registrados.') + '\n';
        }
        ticket += '-'.repeat(width) + '\n';

        // Totales
        ticket += alignText('Total Ingresos:', 'S/ ' + totalIngresos.toFixed(2)) + '\n';
        ticket += alignText('Total Descuentos:', 'S/ ' + totalDescuentos.toFixed(2)) + '\n';
        ticket += alignText('Sueldo Total (Neto):', 'S/ ' + parseFloat(boleta.total_neto).toFixed(2)) + '\n';
        ticket += '-'.repeat(width) + '\n';

        // Firmas
        ticket += '\n';
        ticket += '\n'; // Espacio adicional para la firma
        ticket += centerText('_________________________') + '\n';
        ticket += centerText('Firma del Empleador') + '\n';
        ticket += '\n';
        ticket += '\n'; // Espacio adicional para la firma
        ticket += centerText('_________________________') + '\n';
        ticket += centerText('Firma del Trabajador') + '\n';
        ticket += '\n';

        // Pie de página
        ticket += centerText('Este documento es una representacion') + '\n';
        ticket += centerText('de su boleta de pago.') + '\n';
        ticket += centerText('Para cualquier consulta, contacte a') + '\n';
        ticket += centerText('recursos humanos.') + '\n';
        ticket += centerText('Generado por SysPlan') + '\n';
        ticket += '\n';
        ticket += '\n'; // Espacio para cortar el papel

        return ticket;
    }

    // Nueva función para mostrar el ticket POS en una ventana de texto plano
    function displayPosTicket(ticketText) {
        const printWindow = window.open('', '_blank', 'width=400,height=600');
        printWindow.document.write('<html><head><title>Ticket POS</title>');
        printWindow.document.write('<style>body { font-family: monospace; white-space: pre; margin: 0; padding: 10px; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(ticketText);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
    }
});

<?php
require_once('globales_sistema.php');
require_once('nucleo/include/MasterConexion.php');
require_once('nucleo/include/SuperClass.php');
include_once('header.php');
?>

<link rel="stylesheet" href="assets/css/trabajador.css?v=<?php echo uniqid(); ?>">
<!-- CDN para html2pdf.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>

<h1 class="header-title text-center">Trabajadores</h1>

<div class="container-fluid mt-4">
    <form id="form-trabajador">
        <input type="hidden" id="id" name="id">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="nombres_y_apellidos">Nombres y Apellidos</label>
                <input type="text" class="form-control" id="nombres_y_apellidos" name="nombres_y_apellidos" placeholder="Nombre" required>
            </div>
            <div class="form-group col-md-3">
                <label for="tipo_documento">Tipo Documento</label>
                <select id="tipo_documento" name="tipo_documento" class="form-control">
                    <option value="1" selected>DOC. NACIONAL DE IDENTIDAD</option>
                    <option value="2">CARNET DE EXTRANJERIA</option>
                    <option value="3">PASAPORTE</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="documento">Documento</label>
                <input type="text" class="form-control" id="documento" name="documento" placeholder="Documento" required>
            </div>
            <div class="form-group col-md-3">
                <label for="sueldo_basico">Sueldo</label>
                <input type="number" class="form-control" id="sueldo_basico" name="sueldo_basico" placeholder="Sueldo" step="0.01" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="ocupacion">Ocupacion</label>
                <input type="text" class="form-control" id="ocupacion" name="ocupacion" placeholder="ocupacion" required>
            </div>
            <div class="form-group col-md-3">
                <label for="contrato">Contrato</label>
                <select id="contrato" name="contrato" class="form-control">
                    <option value="1" selected>A PLAZO INDETERMINADO</option>
                    <option value="2">A TIEMPO PARCIAL</option>
                    <option value="3">POR INICIO O INCREMENTO DE ACTIVIDAD</option>
                    <option value="4">POR NECESIDADES DEL MERCADO</option>
                    <option value="5">POR RECONVERSIÓN EMPRESARIAL</option>
                    <option value="6">OCASIONAL</option>
                    <option value="7">DE SUPLENCIA</option>
                    <option value="8">DE EMERGENCIA</option>
                    <option value="9">PARA OBRA DETERMINADA O SERVICIO ESPECÍFICO</option>
                    <option value="10">INTERMITENTE</option>
                    <option value="11">DE TEMPORADA</option>
                    <option value="12">DE EXPORTACIÓN NO TRADICIONAL</option>
                    <option value="13">DE EXTRANJERO</option>
                    <option value="14">ADMINISTRATIVO DE SERVICIOS</option>
                    <option value="15">OTROS</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="condicion">Condicion</label>
                <select id="condicion" name="condicion" class="form-control">
                    <option value="1" selected>DOMICILIADO</option>
                    <option value="2">NO DOMICILIADO</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="fecha_cese">Fecha Cese</label>
                <input type="date" class="form-control" id="fecha_cese" name="fecha_cese" placeholder="AAAA-MM-DD">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="situacion">Situacion</label>
                <select id="situacion" name="situacion" class="form-control">
                    <option value="1" selected>ACTIVO O SUBSIDIADO</option>
                    <option value="2">BAJA</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="fecha_de_ingreso">Fecha Ingreso</label>
                <input type="date" class="form-control" id="fecha_de_ingreso" name="fecha_de_ingreso" placeholder="AAAA-MM-DD" required>
            </div>
            <div class="form-group col-md-3">
                <label for="regimen_pensionario">Regimen Pensionario</label>
                <select id="regimen_pensionario" name="regimen_pensionario" class="form-control">
                    <option value="1" selected>NO</option>
                    <option value="2">DECRETO LEY 19990 - SISTEMA NACIONAL DE PENSIONES - ONP</option>
                    <option value="3">DECRETO LEY 20530 - SISTEMA NACIONAL DE PENSIONES</option>
                    <option value="4">CAJA DE BENEFICIOS DE SEGURIDAD SOCIAL DEL PESCADOR</option>
                    <option value="5">CAJA DE PENSIONES MILITAR</option>
                    <option value="6">CAJA DE PENSIONES POLICIAL</option>
                    <option value="7">OTROS REGIMENES PENSIONARIOS</option>
                    <option value="8">SPP INTEGRA</option>
                    <option value="9">SPP HABITAT</option>
                    <option value="10">SPP PROFUTURO</option>
                    <option value="11">SPP PRIMA</option>
                    <option value="12">SIN REGIMEN PENSIONARIO</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="cuspp">CUSPP</label>
                <input type="text" class="form-control" id="cuspp" name="cuspp" placeholder="CUSPP">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="asignacion_familiar">Asignacion Familiar</label>
                <select id="asignacion_familiar" name="asignacion_familiar" class="form-control">
                    <option value="0" selected>NO TIENE</option>
                    <option value="1">SI TIENE</option>
                </select>
            </div>
            <div class="form-group col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>


    <div class="form-row mt-3 align-items-center justify-content-between">
        <div class="col-md-6 text-left">
            <button type="button" class="btn btn-success" id="btnExportExcel">Excel</button>
            <button type="button" class="btn btn-danger" id="btnExportPdf">PDF</button>
            <button type="button" class="btn btn-info" id="btnCopy">Copiar</button>
        </div>
        <div class="col-md-3 text-right">
            <input type="text" id="buscar" class="form-control" placeholder="Buscar trabajador...">
        </div>
    </div>
</form>

    <div class="mt-3">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="sortable-header" data-sort-by="id">Id <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="nombresApellidos">Nombre <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="tipoDocumento">Tipo Doc <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="documento">Documento <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="sueldoBasico">Sueldo<br>Basico <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="ocupacion">Ocupacion <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="contrato">Contrato <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="condicion">Condicion <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="situacion">Situacion <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="fechaIngreso">Fecha Ingreso <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="fechaCese">Fecha Cese <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="asignacionFamiliar">Asig <br>Familiar <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="regimenPensionario">Reg<br>Pensionario <i class="fa fa-sort sort-icon"></i></th>
                        <th class="sortable-header" data-sort-by="idSocioRegimenPensionario">ID Socio <i class="fa fa-sort sort-icon"></i></th>
                    </tr>
                </thead>
                <tbody id="tablaTrabajadores">
                    <!-- Los datos de los trabajadores se cargarán aquí dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>

<script>
$(document).ready(function() {
    const API_URL = 'nucleo/trabajador_controller.php';
    let allTrabajadores = []; // Para almacenar todos los trabajadores
    let currentSortColumn = null;
    let currentSortDirection = 'asc'; // 'asc' o 'desc'


    // Función para cargar trabajadores
    function loadTrabajadores() {
        $.ajax({
            url: API_URL,
            method: 'POST',
            data: { operation: 'get_all' },
            dataType: 'json',
            success: function(data) {
                if (Array.isArray(data)) {
                    allTrabajadores = data; // Almacenar los datos
                    renderTable(allTrabajadores); // Renderizar la tabla inicialmente
                } else {
                    console.warn("La respuesta de 'get_all' no es un array:", data);
                    $('#tablaTrabajadores').html('<tr><td colspan="15" class="text-center">No hay trabajadores registrados o hubo un error al cargar los datos.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar trabajadores:", status, error);
            }
        });
    }

    // Función para renderizar la tabla
    function renderTable(trabajadoresToRender) {
        let rows = '';
        if (Array.isArray(trabajadoresToRender) && trabajadoresToRender.length > 0) {
            trabajadoresToRender.forEach((trabajador, index) => {
                rows += `
                    <tr class="worker-row ${index % 2 === 0 ? 'even' : 'odd'}">
                        <td class="details-control">
                            <i class="fa fa-plus-circle toggle-icon" style="cursor: pointer; margin-right: 8px;"></i>
                            ${trabajador.id}
                        </td>
                        <td>${trabajador.nombresApellidos}</td>
                        <td>${getTipoDocumentoText(trabajador.tipoDocumento)}</td>
                        <td>${trabajador.documento}</td>
                        <td>${parseFloat(trabajador.sueldoBasico).toFixed(2)}</td>
                        <td>${trabajador.ocupacion}</td>
                        <td>${getContratoText(trabajador.contrato)}</td>
                        <td>${getCondicionText(trabajador.condicion)}</td>
                        <td>${getSituacionText(trabajador.situacion)}</td>
                        <td>${formatDate(trabajador.fechaIngreso)}</td>
                        <td>${formatDate(trabajador.fechaCese)}</td>
                        <td>${getAsignacionFamiliarText(trabajador.asignacionFamiliar)}</td>
                        <td>${getRegimenPensionarioText(trabajador.regimenPensionario)}</td>
                        <td>${trabajador.idSocioRegimenPensionario}</td>
                    </tr>
                    <tr class="details-row" style="display: none;">
                        <td colspan="14" style="padding: 15px;">
                            <b>OPCIONES:</b>
                            <div class="btn-group" role="group" style="margin-left: 10px;">
                                <a href="asistencias.php?id_trabajador=${trabajador.id}" class="btn btn-sm btn-primary" title="Asistencia"><i class="fa fa-calendar-check-o"></i></a>
                                <a href="boleta_pago.php?id=${trabajador.id}" class="btn btn-sm btn-success" title="Boleta de Pago"><i class="fa fa-money"></i></a>
                                <button class="btn btn-sm btn-info edit-btn" data-id="${trabajador.id}" title="Editar"><i class="fa fa-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${trabajador.id}" title="Eliminar"><i class="fa fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        } else {
            rows = '<tr><td colspan="15" class="text-center">No hay trabajadores registrados o hubo un error al cargar los datos.</td></tr>';
        }
        $('#tablaTrabajadores').html(rows);
    }

    // Función para ordenar la tabla
    function sortTable(column) {
        // Si se hace clic en la misma columna, invertir la dirección
        if (currentSortColumn === column) {
            currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortColumn = column;
            currentSortDirection = 'asc'; // Por defecto, ordenar ascendente al hacer clic en una nueva columna
        }

        // Restablecer todos los iconos de ordenamiento
        $('.sortable-header').removeClass('asc desc');
        $('.sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');

        // Actualizar el icono de la columna actual
        const currentHeader = $(`[data-sort-by="${column}"]`);
        currentHeader.addClass(currentSortDirection);
        currentHeader.find('.sort-icon').removeClass('fa-sort').addClass(currentSortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');

        // Ordenar los datos
        allTrabajadores.sort((a, b) => {
            let valA = a[column];
            let valB = b[column];

            // Manejar tipos de datos (números, fechas, strings)
            if (typeof valA === 'string' && typeof valB === 'string') {
                // Intentar convertir a número si es posible
                if (!isNaN(valA) && !isNaN(valB) && valA.trim() !== '' && valB.trim() !== '') {
                    valA = parseFloat(valA);
                    valB = parseFloat(valB);
                } else {
                    // Comparación de cadenas insensible a mayúsculas/minúsculas
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }
            }

            if (valA < valB) {
                return currentSortDirection === 'asc' ? -1 : 1;
            }
            if (valA > valB) {
                return currentSortDirection === 'asc' ? 1 : -1;
            }
            return 0;
        });

        renderTable(allTrabajadores);
    }

    // Manejar clics en los encabezados para ordenar
    $(document).on('click', '.sortable-header', function() {
        const sortBy = $(this).data('sort-by');
        if (sortBy) {
            sortTable(sortBy);
        }
    });

    // Funciones para obtener el texto de los selects
    function getTipoDocumentoText(value) {
        const options = {
            '1': 'DNI',
            '2': 'CARNET DE EXTRANJERIA',
            '3': 'PASAPORTE'
        };
        return options[value] || value;
    }

    function getContratoText(value) {
        const options = {
            '1': 'A PLAZO INDETERMINADO',
            '2': 'A TIEMPO PARCIAL',
            '3': 'POR INICIO O INCREMENTO DE ACTIVIDAD',
            '4': 'POR NECESIDADES DEL MERCADO',
            '5': 'POR RECONVERSIÓN EMPRESARIAL',
            '6': 'OCASIONAL',
            '7': 'DE SUPLENCIA',
            '8': 'DE EMERGENCIA',
            '9': 'PARA OBRA DETERMINADA O SERVICIO ESPECÍFICO',
            '10': 'INTERMITENTE',
            '11': 'DE TEMPORADA',
            '12': 'DE EXPORTACIÓN NO TRADICIONAL',
            '13': 'DE EXTRANJERO',
            '14': 'ADMINISTRATIVO DE SERVICIOS',
            '15': 'OTROS'
        };
        return options[value] || value;
    }

    function getCondicionText(value) {
        const options = {
            '1': 'DOMICILIADO',
            '2': 'NO DOMICILIADO'
        };
        return options[value] || value;
    }

    function getSituacionText(value) {
        const options = {
            '1': 'ACTIVO O SUBSIDIADO',
            '2': 'BAJA'
        };
        return options[value] || value;
    }

    function getAsignacionFamiliarText(value) {
        const options = {
            '0': 'NO',
            '1': 'SI'
        };
        return options[value] || value;
    }

    function getRegimenPensionarioText(value) {
        const options = {
            '1': 'NO',
            '2': 'DECRETO LEY 19990 - SISTEMA NACIONAL DE PENSIONES - ONP',
            '3': 'DECRETO LEY 20530 - SISTEMA NACIONAL DE PENSIONES',
            '4': 'CAJA DE BENEFICIOS DE SEGURIDAD SOCIAL DEL PESCADOR',
            '5': 'CAJA DE PENSIONES MILITAR',
            '6': 'CAJA DE PENSIONES POLICIAL',
            '7': 'OTROS REGIMENES PENSIONARIOS',
            '8': 'SPP INTEGRA',
            '9': 'SPP HABITAT',
            '10': 'SPP PROFUTURO',
            '11': 'SPP PRIMA',
            '12': 'SIN REGIMEN PENSIONARIO'
        };
        return options[value] || value;
    }

    function formatDate(dateString) {
        if (!dateString || dateString === '0000-00-00') return '';
        const [year, month, day] = dateString.split('-');
        return `${day}-${month}-${year}`;
    }

    // Manejar el despliegue de las opciones
    $(document).on('click', '.details-control', function() {
        const clickedIcon = $(this).find('i');
        const clickedTr = $(this).closest('tr');
        const clickedDetailsRow = clickedTr.next('.details-row');

        // Cerrar todas las otras filas de detalles abiertas
        $('.details-row').not(clickedDetailsRow).hide();
        $('.toggle-icon').not(clickedIcon).removeClass('fa-minus-circle').addClass('fa-plus-circle');

        // Alternar la visibilidad de la fila de detalles clicada
        clickedDetailsRow.toggle();

        // Actualizar el icono de la fila clicada
        if (clickedDetailsRow.is(':visible')) {
            clickedIcon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
        } else {
            clickedIcon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
        }
    });

    // Cargar datos iniciales
    loadTrabajadores();

    // Eventos para gestionar aria-hidden en el modal
    $('#modalConfirmarEliminacionTrabajador').on('shown.bs.modal', function () {
        $(this).attr('aria-hidden', 'false');
    });

    $('#modalConfirmarEliminacionTrabajador').on('hidden.bs.modal', function () {
        $(this).attr('aria-hidden', 'true');
    });

    // Manejar el envío del formulario
    $('#form-trabajador').submit(function(e) {
        e.preventDefault();
        const formData = {
            operation: 'save',
            id: $('#id').val(),
            nombres_y_apellidos: $('#nombres_y_apellidos').val(),
            tipo_documento: $('#tipo_documento').val(),
            documento: $('#documento').val(),
            sueldo_basico: $('#sueldo_basico').val(),
            ocupacion: $('#ocupacion').val(),
            contrato: $('#contrato').val(),
            condicion: $('#condicion').val(),
            situacion: $('#situacion').val(),
            fecha_de_ingreso: $('#fecha_de_ingreso').val(),
            fecha_cese: $('#fecha_cese').val(),
            asignacion_familiar: $('#asignacion_familiar').val(),
            regimen_pensionario: $('#regimen_pensionario').val(),
            cuspp: $('#cuspp').val()
        };

        $.ajax({
            url: API_URL,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                try {
                    const jsonResponse = response;
                    alert(jsonResponse.message);
                    loadTrabajadores();
                    clearForm();
                } catch (e) {
                    console.error("Error al parsear JSON:", e);
                    console.log("Respuesta cruda del servidor:", response);
                    alert("Error al guardar trabajador: " + response);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al guardar trabajador:", status, error);
                console.log("Respuesta de error del servidor:", xhr.responseText);
                alert("Error al guardar trabajador: " + xhr.responseText);
            }
        });
    });

    // Manejar el botón de editar
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        // Cargar datos del trabajador
        $.ajax({
            url: API_URL,
            method: 'POST',
            data: { operation: 'get_by_id', id: id },
            dataType: 'json',
            success: function(trabajador) {
                if (trabajador) {
                    $('#id').val(trabajador.id);
                    $('#nombres_y_apellidos').val(trabajador.nombresApellidos);
                    $('#tipo_documento').val(trabajador.tipoDocumento);
                    $('#documento').val(trabajador.documento);
                    $('#sueldo_basico').val(trabajador.sueldoBasico);
                    $('#ocupacion').val(trabajador.ocupacion);
                    $('#contrato').val(trabajador.contrato);
                    $('#condicion').val(trabajador.condicion);
                    $('#situacion').val(trabajador.situacion);
                    $('#fecha_de_ingreso').val(trabajador.fechaIngreso);
                    $('#fecha_cese').val(trabajador.fechaCese);
                    $('#asignacion_familiar').val(trabajador.asignacionFamiliar);
                    $('#regimen_pensionario').val(trabajador.regimenPensionario);
                    $('#cuspp').val(trabajador.cuspp);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar trabajador para edición:", status, error);
                alert("Error al cargar trabajador para edición.");
            }
        });
    });


    // Manejar el botón de eliminar
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if (confirm('¿Está seguro de que desea eliminar este trabajador? Esto también eliminará los conceptos asignados.')) {
            // Si el usuario confirma, proceder directamente con la eliminación
            $.ajax({
                url: API_URL,
                method: 'POST',
                data: { 
                    operation: 'delete', 
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    loadTrabajadores();
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar trabajador:", status, error);
                    let errorMessage = "Error al eliminar trabajador.";
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse && errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                    } catch (e) {
                        console.warn("No se pudo parsear la respuesta de error como JSON:", xhr.responseText);
                        errorMessage += " Detalles: " + xhr.responseText;
                    }
                    alert(errorMessage);
                }
            });
        }
    });

    function clearForm() {
        $('#form-trabajador')[0].reset();
        $('#id').val('');
    }

    // Funcionalidad para exportar a Excel
    $('#btnExportExcel').click(async function() {
        let table = document.querySelector('#tablaTrabajadores');
        let name = 'Trabajadores';
        let uri = 'data:application/vnd.ms-excel;base64,';
        let template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"></head><body><table>{table}</table></body></html>';
        let base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) };
        let format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) };

        let ctx = { worksheet: name || 'Worksheet', table: table.innerHTML };
        let excelContent = format(template, ctx);
        let excelBase64 = base64(excelContent);

        if ('showSaveFilePicker' in window) {
            try {
                const fileHandle = await window.showSaveFilePicker({
                    suggestedName: name + '.xls',
                    types: [{
                        description: 'Archivos de Excel',
                        accept: { 'application/vnd.ms-excel': ['.xls'] },
                    }],
                });
                const writableStream = await fileHandle.createWritable();
                const byteCharacters = atob(excelBase64);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel' });

                await writableStream.write(blob);
                await writableStream.close();
            } catch (err) {
                if (err.name === 'AbortError') {
                    // El usuario canceló el guardado
                } else {
                    console.error("Error al guardar el archivo Excel con showSaveFilePicker:", err);
                    // Fallback a la descarga automática si hay un error inesperado
                    let a = document.createElement('a');
                    a.href = uri + excelBase64;
                    a.download = name + '.xls';
                    a.click();
                }
            }
        } else {
            // Fallback para navegadores que no soportan showSaveFilePicker
            let a = document.createElement('a');
            a.href = uri + excelBase64;
            a.download = name + '.xls';
            a.click();
        }
    });

    // Funcionalidad para exportar a PDF (usando html2pdf.js)
    $('#btnExportPdf').click(async function() {
        const originalElement = document.querySelector('.table-responsive');
        const clonedElement = originalElement.cloneNode(true);

        $(clonedElement).find('tr').each(function() {
            $(this).find('th:first, td:first').remove();
        });
        $(clonedElement).find('.details-row').remove();

        const pdfOptions = {
            margin: 2,
            filename: 'Trabajadores.pdf',
            html2canvas: { scale: 0.6 },
            jsPDF: { unit: 'mm', format: 'a3', orientation: 'landscape' }
        };

        if ('showSaveFilePicker' in window) {
            try {
                const pdfBlob = await html2pdf().from(clonedElement).set(pdfOptions).output('blob');

                const fileHandle = await window.showSaveFilePicker({
                    suggestedName: 'Trabajadores.pdf',
                    types: [{
                        description: 'Archivos PDF',
                        accept: { 'application/pdf': ['.pdf'] },
                    }],
                });
                const writableStream = await fileHandle.createWritable();
                await writableStream.write(pdfBlob);
                await writableStream.close();
            } catch (err) {
                if (err.name === 'AbortError') {
                    // El usuario canceló el guardado
                } else {
                    console.error("Error al guardar el archivo PDF con showSaveFilePicker:", err);
                    // Fallback a la descarga automática
                    html2pdf().from(clonedElement).set(pdfOptions).save();
                }
            }
        } else {
            // Fallback para navegadores que no soportan showSaveFilePicker
            html2pdf().from(clonedElement).set(pdfOptions).save();
        }
    });

    // Funcionalidad para copiar la tabla al portapapeles
    $('#btnCopy').click(function() {
        let table = document.querySelector('#tablaTrabajadores');
        let range = document.createRange();
        range.selectNode(table);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        try {
            document.execCommand('copy');
            alert('Tabla copiada al portapapeles.');
        } catch (err) {
            alert('No se pudo copiar la tabla.');
        }
        window.getSelection().removeAllRanges();
    });

    // Implementar búsqueda (filtrado en el cliente)
    $('#buscar').on('keyup', function() {
        const searchText = $(this).val().toLowerCase();
        if (searchText === '') {
            renderTable(allTrabajadores);
        } else {
            const filteredTrabajadores = allTrabajadores.filter(trabajador => {
                // Busca en todos los valores del objeto trabajador
                return Object.values(trabajador).some(value =>
                    String(value).toLowerCase().includes(searchText)
                );
            });
            renderTable(filteredTrabajadores);
        }
    });
});
</script>

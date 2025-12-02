$(document).ready(function() {
    let monthlyPayrollChart, workersByAreaChart, topIngresosChart, topDescuentosChart;

    function initializeCharts() {
        // Monthly Payroll Chart
        const ctxMonthly = document.getElementById('monthlyPayrollChart').getContext('2d');
        monthlyPayrollChart = new Chart(ctxMonthly, {
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'Total Planilla', data: [], backgroundColor: 'rgba(75, 192, 192, 0.6)', borderColor: 'rgba(75, 192, 192, 1)', borderWidth: 1 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, title: { display: true, text: 'Monto (S/)' } }, x: { title: { display: true, text: 'Mes' } } }, plugins: { legend: { display: false } } }
        });

        // Workers by Area Chart
        const ctxArea = document.getElementById('workersByAreaChart').getContext('2d');
        workersByAreaChart = new Chart(ctxArea, {
            type: 'doughnut',
            data: { labels: [], datasets: [{ label: 'Número de Trabajadores', data: [], backgroundColor: [], hoverOffset: 4 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 15,
                        right: 15,
                        top: 15,
                        bottom: 15
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            boxWidth: 15,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                cutout: '55%', // Ajustar el tamaño del agujero para más espacio
                radius: '75%' // Reducir el radio exterior para más espacio
            }
        });

        // Top 5 Ingresos Chart
        const ctxIngresos = document.getElementById('topIngresosChart').getContext('2d');
        topIngresosChart = new Chart(ctxIngresos, {
            type: 'pie',
            data: { labels: [], datasets: [{ label: 'Monto de Ingresos', data: [], backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'], hoverOffset: 4 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 15,
                        right: 15,
                        top: 15,
                        bottom: 15
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            boxWidth: 15,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                radius: '75%' // Reducir el radio exterior para más espacio
            }
        });

        // Top 5 Descuentos Chart
        const ctxDescuentos = document.getElementById('topDescuentosChart').getContext('2d');
        topDescuentosChart = new Chart(ctxDescuentos, {
            type: 'pie',
            data: { labels: [], datasets: [{ label: 'Monto de Descuentos', data: [], backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'], hoverOffset: 4 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 15,
                        right: 15,
                        top: 15,
                        bottom: 15
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            boxWidth: 15,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                radius: '75%' // Reducir el radio exterior para más espacio
            }
        });
    }

    function updateDashboard(period) {
        $.ajax({
            url: `ws/dashboard_data.php?period=${period}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Update summary cards
                $('#totalPlanilla').text('S/ ' + data.totalPlanilla);
                $('#trabajadoresActivos').text(data.trabajadoresActivos);
                $('#proximoPago').text(data.proximoPago);
                $('#boletasEmitidas').text(data.boletasEmitidas);
                $('#asistenciasPresentes').text(data.asistencias.Presente);
                $('#asistenciasTardanzas').text(data.asistencias.Tardanza);
                $('#asistenciasFaltas').text(data.asistencias.Falta);

                // Update charts
                updateChart(monthlyPayrollChart, data.monthlyPayrollChart.labels, data.monthlyPayrollChart.data);
                updateChart(workersByAreaChart, data.workersByAreaChart.labels, data.workersByAreaChart.data, data.workersByAreaChart.colors);
                updateChart(topIngresosChart, data.topIngresosChart.labels, data.topIngresosChart.data);
                updateChart(topDescuentosChart, data.topDescuentosChart.labels, data.topDescuentosChart.data);

                // Update titles
                let titleSuffix = '';
                if (period === 'current_month') titleSuffix = ' (Mes Actual)';
                else if (period === 'last_3_months') titleSuffix = ' (Últimos 3 Meses)';
                else if (period === 'last_6_months') titleSuffix = ' (Últimos 6 Meses)';
                
                $('#monthlyPayrollChartTitle').text('Planilla Mensual' + titleSuffix);
                $('#asistenciasTitle').text('Resumen de Asistencias' + titleSuffix);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching dashboard data:', error);
            }
        });
    }

    function updateChart(chart, labels, data, colors = null) {
        chart.data.labels = labels;
        chart.data.datasets[0].data = data;
        if (colors) {
            chart.data.datasets[0].backgroundColor = colors;
        }
        chart.update();
    }

    $('.filter-btn').on('click', function() {
        const period = $(this).data('period');
        
        // Update button styles
        $('.filter-btn').removeClass('btn-primary').addClass('btn-secondary');
        $(this).removeClass('btn-secondary').addClass('btn-primary');
        
        updateDashboard(period);
    });

    // Initial load
    initializeCharts();
    updateDashboard('current_month');
});

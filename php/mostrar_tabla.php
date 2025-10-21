<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoreo de Sensores</title>

    <!-- CSS de DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <!-- JS y librerías necesarias -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f4f6f8;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        #chart-container {
            margin: 40px auto;
            padding: 20px;
            width: 90%;
            max-width: 900px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        #checkbox-container {
            text-align: center;
            margin-bottom: 20px;
        }
        #checkbox-container label {
            margin-right: 15px;
            font-weight: bold;
        }
        #btnGrafico {
            margin-top: 15px;
            padding: 10px 25px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        #btnGrafico:hover {
            background-color: #43a047;
        }
        #mesFiltro {
            margin: 10px auto;
            display: block;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>

<?php
include 'conexion.php'; 

if ($conexion->connect_error) {
    echo "<p style='color:red;'>Error de conexión: " . $conexion->connect_error . "</p>";
    exit;
}

// Consulta todos los datos
$sql = "SELECT id, humedad_suelo1, humedad_suelo2, humedad_suelo3, humedad_aire, temperatura, fecha FROM lecturas ORDER BY fecha ASC";
$result = $conexion->query($sql);

$datos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }
}
$jsonDatos = json_encode($datos);

// Generar lista de meses disponibles
$mesesDisponibles = [];
foreach ($datos as $fila) {
    $fecha = new DateTime($fila['fecha']);
    $anioMes = $fecha->format('Y-m');
    if (!in_array($anioMes, $mesesDisponibles)) {
        $mesesDisponibles[] = $anioMes;
    }
}
rsort($mesesDisponibles); // orden de más reciente a más antiguo
?>

<h2>Datos de Monitoreo</h2>

<div style="width:90%; margin:auto;">
    <table id="tablaDatos" class="display" style="width:100%;">
        <thead>
            <tr style="background-color:#4CAF50; color:white;">
                <th>ID</th>
                <th>Humedad Suelo 1</th>
                <th>Humedad Suelo 2</th>
                <th>Humedad Suelo 3</th>
                <th>Humedad Aire</th>
                <th>Temperatura</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($datos as $row) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['humedad_suelo1']}%</td>
                        <td>{$row['humedad_suelo2']}%</td>
                        <td>{$row['humedad_suelo3']}%</td>
                        <td>{$row['humedad_aire']}%</td>
                        <td>{$row['temperatura']}°C</td>
                        <td>{$row['fecha']}</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Filtro por mes + Checkboxes -->
<div id="chart-container">
    <div id="checkbox-container">
        <label for="mesFiltro"><strong>Selecciona mes:</strong></label>
        <select id="mesFiltro">
            <option value="">-- Selecciona un mes --</option>
            <?php
            setlocale(LC_TIME, 'es_ES.UTF-8');
            foreach ($mesesDisponibles as $anioMes) {
                $fecha = DateTime::createFromFormat('Y-m', $anioMes);
                $nombreMes = strftime('%B', $fecha->getTimestamp());
                $anio = $fecha->format('Y');
                echo "<option value='$anioMes'>" . ucfirst($nombreMes) . " $anio</option>";
            }
            ?>
        </select>

        <br><br>
        <label><input type="checkbox" name="columna" value="humedad_suelo1"> Humedad Suelo 1</label>
        <label><input type="checkbox" name="columna" value="humedad_suelo2"> Humedad Suelo 2</label>
        <label><input type="checkbox" name="columna" value="humedad_suelo3"> Humedad Suelo 3</label>
        <label><input type="checkbox" name="columna" value="humedad_aire"> Humedad Aire</label>
        <label><input type="checkbox" name="columna" value="temperatura"> Temperatura</label>
        <br>
        <button id="btnGrafico">Generar Gráfico</button>
    </div>

    <canvas id="graficoSensores" height="120"></canvas>
</div>

<script>
const datosPHP = <?php echo $jsonDatos; ?>;
let myChart = null;

// Inicializa DataTables sin filtros en encabezados
$(document).ready(function() {
    $('#tablaDatos').DataTable({
        responsive: true,
        searching: false, 
        paging: true,
        ordering: true,
        info: true,
        dom: 'Bfrtip', // Mantiene botones de exportación
        buttons: [
            { extend: 'excelHtml5', text: 'Exportar Excel' },
            { extend: 'csvHtml5', text: 'Exportar CSV' },
            { extend: 'pdfHtml5', text: 'Exportar PDF' },
            { extend: 'print', text: 'Imprimir' }
        ],
        language: {
            lengthMenu: "Mostrar _MENU_ registros por página",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando página _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                first: "Primero", last: "Último",
                next: "Siguiente", previous: "Anterior"
            }
        }
    });
});

// Generar gráfico con filtro de mes
document.getElementById('btnGrafico').addEventListener('click', function() {
    const seleccionadas = Array.from(document.querySelectorAll('input[name="columna"]:checked'))
        .map(cb => cb.value);
    const mesSeleccionado = document.getElementById('mesFiltro').value;

    if (seleccionadas.length === 0) {
        alert("Selecciona al menos una variable para graficar.");
        return;
    }

    if (!mesSeleccionado) {
        alert("Selecciona un mes para generar el gráfico.");
        return;
    }

    // Filtra los datos por el mes elegido
    const [anio, mes] = mesSeleccionado.split("-");
    const datosFiltrados = datosPHP.filter(d => {
        const fecha = new Date(d.fecha);
        return fecha.getFullYear() == anio && (fecha.getMonth() + 1) == parseInt(mes);
    });

    if (datosFiltrados.length === 0) {
        alert("No hay datos para el mes seleccionado.");
        return;
    }

    const colorPalette = [
        'rgba(255, 99, 132, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)'
    ];

    const datasets = seleccionadas.map((columna, i) => ({
        label: columna.replace(/_/g, ' ').toUpperCase(),
        data: datosFiltrados.map(d => ({ x: d.fecha, y: parseFloat(d[columna]) })),
        borderColor: colorPalette[i % colorPalette.length],
        backgroundColor: colorPalette[i % colorPalette.length].replace('1)', '0.3)'),
        borderWidth: 2,
        tension: 0.3,
        fill: false
    }));

    const ctx = document.getElementById('graficoSensores').getContext('2d');
    if (myChart) myChart.destroy();

    myChart = new Chart(ctx, {
        type: 'line',
        data: { datasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                title: {
                    display: true,
                    text: 'Gráfico de Variables - ' + mesSeleccionado
                },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: {
                    type: 'time',
                    time: { tooltipFormat: 'dd/MM/yyyy HH:mm:ss' },
                    title: { display: true, text: 'Fecha' }
                },
                y: {
                    title: { display: true, text: 'Valor' }
                }
            }
        }
    });
});
</script>

</body>
</html>

<?php
session_start();
require_once 'config.php';

// Obtener estadísticas
$stats = [
    'total_pacientes' => $pdo->query('SELECT COUNT(*) FROM pacientes')->fetchColumn(),
    'citas_por_mes' => $pdo->query('SELECT DATE_FORMAT(fecha, "%Y-%m") as mes, COUNT(*) as total FROM citas GROUP BY mes ORDER BY mes DESC LIMIT 6')->fetchAll(),
    'tratamientos_populares' => $pdo->query('SELECT tratamiento, COUNT(*) as total FROM citas WHERE estado = "realizada" GROUP BY tratamiento ORDER BY total DESC LIMIT 5')->fetchAll(),
    'pacientes_activos' => $pdo->query('SELECT p.*, COUNT(c.id) as citas_totales FROM pacientes p LEFT JOIN citas c ON p.id = c.paciente_id GROUP BY p.id ORDER BY citas_totales DESC LIMIT 5')->fetchAll()
];

// Filtrar citas por fecha
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-7 days'));
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

$citas_filtradas = $pdo->prepare('SELECT c.*, p.nombre, p.apellido FROM citas c JOIN pacientes p ON c.paciente_id = p.id WHERE c.fecha BETWEEN ? AND ? ORDER BY c.fecha, c.hora');
$citas_filtradas->execute([$fecha_inicio, $fecha_fin]);
$citas = $citas_filtradas->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Clínica Odontológica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
    <style>
        .report-card {
            transition: transform 0.2s;
        }
        .report-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            height: 300px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Clínica Odontológica</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="citas.php">Citas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="historias.php">Historias Clínicas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Reportes</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Filtrar Reportes</h5>
                    </div>
                    <div class="card-body">
                        <form class="row g-3" action="" method="GET">
                            <div class="col-md-4">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $fecha_inicio ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= $fecha_fin ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary mt-4">Filtrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Estadísticas Generales -->
            <div class="col-md-6">
                <div class="card report-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Estadísticas Generales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Total de Pacientes</h6>
                                <h3><?= $stats['total_pacientes'] ?></h3>
                            </div>
                            <div class="col-md-6">
                                <h6>Citas en el Periodo</h6>
                                <h3><?= count($citas) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Citas por Mes -->
            <div class="col-md-6">
                <div class="card report-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Citas por Mes</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="citasPorMesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tratamientos Populares -->
        <div class="row">
            <div class="col-12">
                <div class="card report-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tratamientos Populares</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tratamiento</th>
                                        <th>Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['tratamientos_populares'] as $tratamiento): ?>
                                    <tr>
                                        <td><?= $tratamiento['tratamiento'] ?></td>
                                        <td><?= $tratamiento['total'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pacientes Activos -->
        <div class="row">
            <div class="col-12">
                <div class="card report-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pacientes más Activos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Paciente</th>
                                        <th>Citas Totales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['pacientes_activos'] as $paciente): ?>
                                    <tr>
                                        <td><?= $paciente['nombre'] . ' ' . $paciente['apellido'] ?></td>
                                        <td><?= $paciente['citas_totales'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        // Gráfico de Citas por Mes
        const ctx = document.getElementById('citasPorMesChart').getContext('2d');
        const citasPorMesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(function($item) { return $item['mes']; }, $stats['citas_por_mes'])) ?>,
                datasets: [{
                    label: 'Citas por Mes',
                    data: <?= json_encode(array_map(function($item) { return $item['total']; }, $stats['citas_por_mes'])) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>

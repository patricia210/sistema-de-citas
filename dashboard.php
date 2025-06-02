<?php
session_start();
require_once 'config.php';

// Obtener estadísticas
$stats = [
    'total_pacientes' => $pdo->query('SELECT COUNT(*) FROM pacientes')->fetchColumn(),
    'citas_hoy' => $pdo->query('SELECT COUNT(*) FROM citas WHERE fecha = CURDATE()')->fetchColumn(),
    'citas_pendientes' => $pdo->query('SELECT COUNT(*) FROM citas WHERE estado = "pendiente"')->fetchColumn(),
    'historias' => $pdo->query('SELECT COUNT(*) FROM historias_clinicas')->fetchColumn()
];

// Obtener próximas citas
$proximas_citas = $pdo->query('SELECT c.*, p.nombre, p.apellido FROM citas c JOIN pacientes p ON c.paciente_id = p.id WHERE c.fecha >= CURDATE() ORDER BY c.fecha, c.hora LIMIT 5')->fetchAll();

// Obtener últimas historias
$ultimas_historias = $pdo->query('SELECT h.*, p.nombre, p.apellido FROM historias_clinicas h JOIN pacientes p ON h.paciente_id = p.id ORDER BY h.fecha_creacion DESC LIMIT 5')->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clínica Odontológica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            height: 100%;
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .stats-card {
            min-height: 150px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Clínica Odontológica</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="citas.php">Citas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="historias.php">Historias Clínicas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reportes.php">Reportes</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Estadísticas -->
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users"></i> Pacientes Totales</h5>
                        <h2 class="card-text"><?= $stats['total_pacientes'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-calendar-check"></i> Citas Hoy</h5>
                        <h2 class="card-text"><?= $stats['citas_hoy'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-clock"></i> Citas Pendientes</h5>
                        <h2 class="card-text"><?= $stats['citas_pendientes'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-file-medical"></i> Historias Clínicas</h5>
                        <h2 class="card-text"><?= $stats['historias'] ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximas Citas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-calendar-alt"></i> Próximas Citas</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximas_citas as $cita): ?>
                                <tr>
                                    <td><?= $cita['nombre'] . ' ' . $cita['apellido'] ?></td>
                                    <td><?= $cita['fecha'] ?></td>
                                    <td><?= $cita['hora'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $cita['estado'] === 'pendiente' ? 'warning' : ($cita['estado'] === 'confirmada' ? 'success' : 'secondary') ?>">
                                            <?= ucfirst($cita['estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimas Historias -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-file-medical"></i> Últimas Historias Clínicas</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Fecha</th>
                                    <th>Diagnóstico</th>
                                    <th>Tratamiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_historias as $historia): ?>
                                <tr>
                                    <td><?= $historia['nombre'] . ' ' . $historia['apellido'] ?></td>
                                    <td><?= $historia['fecha_creacion'] ?></td>
                                    <td><?= substr($historia['diagnostico'], 0, 50) . '...' ?></td>
                                    <td><?= substr($historia['tratamiento'], 0, 50) . '...' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

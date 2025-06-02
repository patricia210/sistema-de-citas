<?php
session_start();
require_once 'config.php';

// Obtener pacientes para el select
$pacientes = $pdo->query('SELECT id, nombre, apellido FROM pacientes ORDER BY nombre')->fetchAll();

// Procesar el formulario de nueva cita
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare('INSERT INTO citas (paciente_id, fecha, hora, estado, tratamiento) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['paciente_id'],
            $_POST['fecha'],
            $_POST['hora'],
            'pendiente',
            $_POST['tratamiento']
        ]);
        
        $pdo->commit();
        $success_message = "Cita agendada exitosamente";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Error al agendar la cita: " . $e->getMessage();
    }
}

// Obtener todas las citas
$citas = $pdo->query('SELECT c.*, p.nombre, p.apellido FROM citas c JOIN pacientes p ON c.paciente_id = p.id ORDER BY c.fecha, c.hora')->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas - Clínica Odontológica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .cita-card {
            transition: transform 0.2s;
        }
        .cita-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
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
                        <a class="nav-link active" href="#">Citas</a>
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
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Agendar Nueva Cita</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaCitaModal">
                            <i class="fas fa-plus"></i> Nueva Cita
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?= $success_message ?></div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?= $error_message ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Lista de Citas</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                    <th>Tratamiento</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($citas as $cita): ?>
                                <tr>
                                    <td><?= $cita['nombre'] . ' ' . $cita['apellido'] ?></td>
                                    <td><?= $cita['fecha'] ?></td>
                                    <td><?= $cita['hora'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $cita['estado'] === 'pendiente' ? 'warning' : ($cita['estado'] === 'confirmada' ? 'success' : 'secondary') ?>">
                                            <?= ucfirst($cita['estado']) ?>
                                        </span>
                                    </td>
                                    <td><?= $cita['tratamiento'] ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editarCitaModal-<?= $cita['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="confirmarEliminacion(<?= $cita['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Cita -->
    <div class="modal fade" id="nuevaCitaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="paciente_id" class="form-label">Paciente</label>
                            <select name="paciente_id" id="paciente_id" class="form-select" required>
                                <option value="">Seleccione un paciente</option>
                                <?php foreach ($pacientes as $paciente): ?>
                                <option value="<?= $paciente['id'] ?>">
                                    <?= $paciente['nombre'] . ' ' . $paciente['apellido'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="hora" class="form-label">Hora</label>
                            <input type="time" name="hora" id="hora" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="tratamiento" class="form-label">Tratamiento</label>
                            <textarea name="tratamiento" id="tratamiento" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminacion(id) {
            if (confirm('¿Está seguro de que desea eliminar esta cita?')) {
                window.location.href = 'eliminar_cita.php?id=' + id;
            }
        }
    </script>
</body>
</html>

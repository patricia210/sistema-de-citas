<?php
session_start();
require_once 'config.php';

// Obtener pacientes para el select
$pacientes = $pdo->query('SELECT id, nombre, apellido FROM pacientes ORDER BY nombre')->fetchAll();

// Procesar el formulario de nueva historia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare('INSERT INTO historias_clinicas (paciente_id, fecha_creacion, diagnostico, tratamiento, observaciones) VALUES (?, CURDATE(), ?, ?, ?)');
        $stmt->execute([
            $_POST['paciente_id'],
            $_POST['diagnostico'],
            $_POST['tratamiento'],
            $_POST['observaciones']
        ]);
        
        $pdo->commit();
        $success_message = "Historia clínica registrada exitosamente";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Error al registrar la historia: " . $e->getMessage();
    }
}

// Obtener todas las historias clínicas
$historias = $pdo->query('SELECT h.*, p.nombre, p.apellido FROM historias_clinicas h JOIN pacientes p ON h.paciente_id = p.id ORDER BY h.fecha_creacion DESC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historias Clínicas - Clínica Odontológica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .history-card {
            transition: transform 0.2s;
        }
        .history-card:hover {
            transform: translateY(-5px);
        }
        .expandable-content {
            max-height: 50px;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
        }
        .expandable-content.expanded {
            max-height: none;
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
                        <a class="nav-link active" href="#">Historias Clínicas</a>
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
                        <h5 class="card-title mb-0">Nueva Historia Clínica</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaHistoriaModal">
                            <i class="fas fa-plus"></i> Nueva Historia
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
                        <h5 class="card-title mb-0">Historias Clínicas</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Fecha</th>
                                    <th>Diagnóstico</th>
                                    <th>Tratamiento</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historias as $historia): ?>
                                <tr>
                                    <td><?= $historia['nombre'] . ' ' . $historia['apellido'] ?></td>
                                    <td><?= $historia['fecha_creacion'] ?></td>
                                    <td>
                                        <div class="expandable-content" id="diagnostico-<?= $historia['id'] ?>">
                                            <?= $historia['diagnostico'] ?>
                                        </div>
                                        <button class="btn btn-link btn-sm" onclick="toggleExpand('diagnostico-<?= $historia['id'] ?>')">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="expandable-content" id="tratamiento-<?= $historia['id'] ?>">
                                            <?= $historia['tratamiento'] ?>
                                        </div>
                                        <button class="btn btn-link btn-sm" onclick="toggleExpand('tratamiento-<?= $historia['id'] ?>')">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#verHistoriaModal-<?= $historia['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="confirmarEliminacion(<?= $historia['id'] ?>)">
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

    <!-- Modal Nueva Historia -->
    <div class="modal fade" id="nuevaHistoriaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Historia Clínica</h5>
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
                            <label for="diagnostico" class="form-label">Diagnóstico</label>
                            <textarea name="diagnostico" id="diagnostico" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="tratamiento" class="form-label">Tratamiento</label>
                            <textarea name="tratamiento" id="tratamiento" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3"></textarea>
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
        function toggleExpand(elementId) {
            const element = document.getElementById(elementId);
            element.classList.toggle('expanded');
        }

        function confirmarEliminacion(id) {
            if (confirm('¿Está seguro de que desea eliminar esta historia clínica?')) {
                window.location.href = 'eliminar_historia.php?id=' + id;
            }
        }
    </script>
</body>
</html>

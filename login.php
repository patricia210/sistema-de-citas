<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header('Location: index.php?error=Por favor, complete todos los campos.');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            header('Location: index.php?error=Usuario o contraseña incorrectos.');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: index.php?error=Error al procesar el inicio de sesión.');
        exit;
    }
}
?>

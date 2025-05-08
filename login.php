<?php
require 'config.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Anna käyttäjätunnus ja salasana.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Väärä käyttäjänimi tai salasana.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirjaudu sisään</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>Kirjaudu sisään</h1>
    </header>

    <form method="POST" action="login.php">
        <label for="username">Käyttäjätunnus:</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Salasana:</label>
        <input type="password" name="password" id="password" required>
        <p><button type="submit" class="login-btn">Kirjaudu sisään</button></p>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
    <p>Etkö ole vielä tehnyt tiliä?</p>
    <p><a href="registration.php" class="register-btn">Rekisteröidy </a></p>
    <footer>
        <p>&copy; Matteus Kolppanen ja Milka Koivupalo 2025</p>
</body>
</html>
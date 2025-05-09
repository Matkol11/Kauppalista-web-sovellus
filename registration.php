<?php
require 'config.php';
session_start();
// Insert new user into the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if($username === '' || $password === '') {
        $error = 'Anna käyttäjätunnus ja salasana.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        try {
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === 23000) { // Duplicate entry error code
                $error = 'Käyttäjänimi on jo käytössä.';
            } else {
                throw $e;
            }
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekisteröidy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Rekisteröidy</h1>
    </header>

    <main>
        <p>Rekisteröidy käyttämään ostoslistapalvelua.</p>
    </main>
<form method="POST" action="registration.php"> 
    <label for="username" >Käyttäjätunnus:</label>
    <input type="text" name="username" id="username" style='display:flex;' required>
    <p><label for="password">Salasana:</label>
    <input type="password" name="password" id="password" style='display:flex;' required></p></p>
    <p><button class="register-btn" type="submit">Rekisteröidy</button></p>
    <p>Onko sinulla jo tili? <a href="login.php" class="login-btn">Kirjaudu sisään</a></p>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</form>
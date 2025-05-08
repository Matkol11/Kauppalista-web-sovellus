<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM lists WHERE id = ?");
    $stmt->execute([$delId]);
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id, title, created_at FROM lists WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$lists = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ostoslistani</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Listat</h1>
    <a href="list.php" class="button">+ Uusi lista</a>
    <a href="logout.php" class="logout-btn">Kirjaudu Ulos</a>
  </header>

  <?php if (count($lists) === 0): ?>
    <p>Et ole vielä tehnyt ostoslistoja.</p> 
    <?php else: ?>
    <ul class="list-overview">
      <?php foreach ($lists as $list): ?>
        <li>
          <a href="list.php?id=<?= htmlspecialchars($list['id']) ?>">
            <?= htmlspecialchars($list['title']) ?>
          </a>
          <form method="post" class="delete-form" onsubmit="return confirm('Haluatko poistaa ostoslistan?');">
            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($list['id']) ?>">
            <button type="submit" class="btn-delete">🗑️</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <footer>
  <p>&copy; Matteus Kolppanen ja Milka Koivupalo 2025</p>
</body>
</html>
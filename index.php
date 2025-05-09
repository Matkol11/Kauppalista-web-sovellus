<?php
require 'config.php';
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Handle list deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("SELECT user_id FROM lists WHERE id = ?");
    $stmt->execute([$delId]);
    $list = $stmt->fetch();

    // Check if the list belongs to the owner
    // or if the user is the one who shared it
    // this ensures that only the owner can delete the list
    // and the shared user can only delete their own copy
    if ($list && $list['user_id'] === $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM lists WHERE id = ?");
        $stmt->execute([$delId]);
        header("Location: index.php");
        exit;
    } else {
        $stmt = $pdo->prepare("DELETE FROM shared_lists WHERE list_id = ? AND shared_with_user_id = ?");
        $stmt->execute([$delId, $_SESSION['user_id']]);
    }
    header("Location: index.php");
    exit;
}
// Fetch lists that the user owns or lists that are shared with them
$stmt = $pdo->prepare("
    SELECT lists.id, lists.title, lists.created_at, lists.user_id,
           CASE
             WHEN lists.user_id = ? THEN
               (SELECT COUNT(*) FROM shared_lists WHERE shared_lists.list_id = lists.id) > 0
             ELSE 1
           END AS is_shared
    FROM lists
    LEFT JOIN shared_lists ON lists.id = shared_lists.list_id
    WHERE lists.user_id = ? OR shared_lists.shared_with_user_id = ?
    GROUP BY lists.id
    ORDER BY lists.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
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
            <form method="post" class="delete-form" onsubmit="return confirm('Haluatko poistaa ostoslistan: <?= htmlspecialchars($list['title']) ?>?');">
            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($list['id']) ?>">
            <button type="submit" class="btn-delete">🗑️</button>
            </form>
          <?php if ($list['is_shared']): ?>
            <p> Jaettu Lista</p>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <footer>
  <p>&copy; Matteus Kolppanen ja Milka Koivupalo 2025</p>
  </footer>
</body>
</html>
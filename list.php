<?php
// list.php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

 //new list creation
if (!isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  if ($title === '') {
    $error = 'Anna listalle nimi.';
  } else {
    $stmt = $pdo->prepare("INSERT INTO lists (user_id, title) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title]);
    header("Location: list.php?id=" . $pdo->lastInsertId());
    exit;
  }
}
// check if list ID is provided in the URL
if (isset($_GET['id'])) {
  $listId = (int)$_GET['id'];
}
  // handle new-item submission
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'])) {
    $name  = trim($_POST['item_name']);
    $price = trim($_POST['price'] ?? '');
    if ($name !== '') {
      $stmt = $pdo->prepare("INSERT INTO items (list_id, name, price) VALUES (?, ?, ?)");
      $stmt->execute([$listId, $name, $price === '' ? null : $price]);
      header("Location: list.php?id=$listId");
      exit;
    }
  }

if (isset($_GET['id'])) {
  $listId = (int)$_GET['id'];

  // Handle item update
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item_id'])) {
    $itemId = (int)$_POST['edit_item_id'];
    $newName = trim($_POST['edit_item_name'] ?? '');
    $newPrice = trim($_POST['edit_item_price'] ?? '');
    if ($newName !== '') {
      $stmt = $pdo->prepare("UPDATE items SET name = ?, price = ? WHERE id = ? AND list_id = ?");
      $stmt->execute([$newName, $newPrice === '' ? null : $newPrice, $itemId, $listId]);
    }
    header("Location: list.php?id=$listId");
    exit;
  }

  // handle delete-item request
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    $itemId = (int)$_POST['delete_item_id'];
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ? AND list_id = ?");
    $stmt->execute([$itemId, $listId]);
    header("Location: list.php?id=$listId");
    exit;
  }

  // fetch list & items
  $stmt = $pdo->prepare("SELECT * FROM lists WHERE id = ? AND user_id = ?");
  $stmt->execute([$listId, $_SESSION['user_id']]);
  $list = $stmt->fetch();
  if (!$list) { echo "List not found."; exit; }
  $stmt = $pdo->prepare("SELECT * FROM items WHERE list_id = ? ORDER BY created_at DESC");
  $stmt->execute([$listId]);
  $items = $stmt->fetchAll();

  
  $totalPrice = 0;
  foreach ($items as $item) {
    if ($item['price'] !== null) {
      $totalPrice += $item['price'];
    }
  }
}

?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>
    <?= isset($list)
         ? htmlspecialchars($list['title'])
         : 'Listasi' ?>
  </title>
  <link rel="stylesheet" href="style.css">
</head>

<body>

<?php if (!isset($list)): ?>
  <div class="name-prompt">
    <h1>Syötä nimi</h1>
    <?php if (!empty($error)): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" class="name-form">
      <button type="submit" class="btn-add"><span>＋</span></button>
      <input
        type="text"
        name="title"
        placeholder="Uusi lista"
        required
        class="input-item"
      >
    </form>
  </div>


<?php else: ?>
  <header class="header-bar">
    <a href="index.php" class="icon-btn back-btn">‹</a>
    <h2><?= htmlspecialchars($list['title']) ?></h2>
    <a href="logout.php" class="logout-btn">Kirjaudu Ulos</a>
  </header>
  <form method="post" class="add-item-form">
    <button type="submit" class="btn-add"><span>＋</span></button>
    <input
      type="text"
      name="item_name"
      placeholder="Tuote"
      required
      class="input-item"
    >
    <input
      type="number"
      name="price"
      placeholder="Hinta"
      class="input-price"
    >
  </form>

<?php
$editingId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
?>
  <?php if (count($items) > 0): ?>
    <ul class="items-overview">
      <?php foreach ($items as $item): ?>
        <li class="item-row">
          <?php if ($editingId === (int)$item['id']): ?>
            <form method="post" class="edit-item-form">
              <input
                type="hidden"
                name="edit_item_id"
                value="<?= htmlspecialchars($item['id']) ?>"
              >
              <input
                type="text"
                name="edit_item_name"
                value="<?= htmlspecialchars($item['name']) ?>"
                required
                class="input-item edit-input"
              >
              <input
                type="number"
                name="edit_item_price"
                value="<?= htmlspecialchars($item['price']) ?>"
                class="input-price edit-input"
              >
              <button type="submit" class="btn-edit">💾</button>
            </form>
            <?php else: ?>
            <form method="post" class="check-item-form">
              <input
                type="checkbox"
                name="item_id[]"
                value="<?= htmlspecialchars($item['id']) ?>"
                class="check-item"
              >
            </form>
            <span class="item-name">
              <a href="list.php?id=<?= $listId ?>&edit=<?= $item['id'] ?>" style="text-decoration:none;color:inherit;">
                <?= htmlspecialchars($item['name']) ?>
              </a>
            </span>
            <?php if ($item['price'] !== null): ?>
              <span class="item-price">
                <a href="list.php?id=<?= $listId ?>&edit=<?= $item['id'] ?>" style="text-decoration:none;color:inherit;">
                 <?= number_format($item['price'],2) ?>€
                </a>
              </span>
            <?php endif; ?>
            <form method="post" class="delete-item-form" style="display:inline;">
              <input type="hidden" name="delete_item_id" value="<?= htmlspecialchars($item['id']) ?>">
              <button type="submit" class="btn-delete">🗑️</button>
            </form>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="total-price">
      <strong>Yhteensä:</strong>
      <span><?= number_format($totalPrice, 2) ?>€</span>
    </div>
    
    <div class="split-cost">
      <label for ="split_cost">Jaetaan:</label>
      <input type="number" id="split_cost" name="split_cost" min="1" value="1">
      <button id="calculate_split" class="btn-split">Laske</button>
      <div id="split_result" class="split-result"></div>  

    </div>
  <?php endif; ?>
<?php endif; ?>

<script>
  document.querySelectorAll('.check-item').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      const li = this.closest('.item-row');
      if (this.checked) {
        li.classList.add('checked');
      } else {
        li.classList.remove('checked');
      }
    });
  });

  document.getElementById('calculate_split').addEventListener('click', function() {
    const splitCost = document.getElementById('split_cost').value;
    const totalPrice = <?= json_encode($totalPrice) ?>;
    const splitResult = document.getElementById('split_result');
    
    if (splitCost > 0) {
      const result = (totalPrice / splitCost).toFixed(2);
      splitResult.textContent = `Jokaisen osuus: ${result}€`;
    } else {
      splitResult.textContent = 'Anna kelvollinen luku.';
    }
  });
</script>

</body>
</html>
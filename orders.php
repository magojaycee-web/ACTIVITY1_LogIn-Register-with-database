<?php
require 'config.php';
require_login();

$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');

$pageTitle = 'My Orders';
require 'header.php';
?>
<h1>My Orders</h1>
<?php if (!$orders): ?>
    <p>Wala ka pang orders.</p>
<?php endif; ?>
<?php foreach ($orders as $o): ?>
    <div class="box">
        <p><strong>Order #<?= (int)$o['id'] ?></strong> — <span class="badge"><?= e($o['status']) ?></span>
        <br><small><?= e($o['created_at']) ?></small></p>
        <ul>
            <?php
            $itemStmt->execute([$o['id']]);
            foreach ($itemStmt->fetchAll() as $it): ?>
                <li><?= e($it['product_name']) ?> × <?= (int)$it['qty'] ?> — <?= money($it['qty'] * (float)$it['price']) ?></li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Total: <?= money((float)$o['total']) ?></strong></p>
    </div>
<?php endforeach; ?>
<?php require 'footer.php'; ?>

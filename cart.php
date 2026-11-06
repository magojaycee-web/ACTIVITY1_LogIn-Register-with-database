<?php
require 'config.php';
$_SESSION['cart'] ??= [];

function product_stock(PDO $pdo, int $id): ?int {
    $s = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
    $s->execute([$id]);
    $row = $s->fetch();
    return $row ? (int)$row['stock'] : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash('Invalid request.', 'error');
        redirect('cart.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $id = (int)($_POST['id'] ?? 0);
        $stock = product_stock($pdo, $id);
        if ($stock === null || $stock < 1) {
            flash('Hindi available ang product.', 'error');
        } else {
            $_SESSION['cart'][$id] = min(($_SESSION['cart'][$id] ?? 0) + 1, $stock);
            flash('Naidagdag sa cart.');
        }
        redirect('index.php');
    }

    if ($action === 'update') {
        foreach (($_POST['qty'] ?? []) as $id => $qty) {
            $id = (int)$id;
            $qty = (int)$qty;
            $stock = product_stock($pdo, $id);
            if ($qty < 1 || $stock === null) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = min($qty, $stock);
            }
        }
        flash('Na-update ang cart.');
        redirect('cart.php');
    }

    if ($action === 'remove') {
        unset($_SESSION['cart'][(int)($_POST['id'] ?? 0)]);
        redirect('cart.php');
    }
}

$items = [];
$total = 0.0;
if ($_SESSION['cart']) {
    $ids = array_keys($_SESSION['cart']);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) {
        $p['qty'] = $_SESSION['cart'][$p['id']];
        $p['subtotal'] = $p['qty'] * (float)$p['price'];
        $total += $p['subtotal'];
        $items[] = $p;
    }
}

$pageTitle = 'Cart';
require 'header.php';
?>
<h1>Your Cart</h1>
<?php if (!$items): ?>
    <p>Walang laman ang cart. <a href="index.php">Mamili na</a></p>
<?php else: ?>
<form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="update">
    <table>
        <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
        <?php foreach ($items as $it): ?>
        <tr>
            <td><?= e($it['name']) ?></td>
            <td><?= money((float)$it['price']) ?></td>
            <td><input type="number" name="qty[<?= (int)$it['id'] ?>]" value="<?= (int)$it['qty'] ?>" min="0" max="<?= (int)$it['stock'] ?>" class="qty"></td>
            <td><?= money($it['subtotal']) ?></td>
            <td>
                <button type="submit" form="rm<?= (int)$it['id'] ?>" class="small secondary">Remove</button>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="3"><strong>Total</strong></td><td colspan="2"><strong><?= money($total) ?></strong></td></tr>
    </table>
    <div class="row">
        <button type="submit" class="secondary">Update cart</button>
        <a class="btn" href="checkout.php">Checkout</a>
    </div>
</form>
<?php foreach ($items as $it): ?>
<form id="rm<?= (int)$it['id'] ?>" method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="remove">
    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
</form>
<?php endforeach; ?>
<?php endif; ?>
<?php require 'footer.php'; ?>

<?php
require 'config.php';
require_login();

if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

// Kunin ang presyo at stock mula sa database (hindi pinagkakatiwalaan ang client)
$ids = array_keys($_SESSION['cart']);
$in = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
$stmt->execute($ids);
$items = [];
$total = 0.0;
foreach ($stmt->fetchAll() as $p) {
    $p['qty'] = $_SESSION['cart'][$p['id']];
    $total += $p['qty'] * (float)$p['price'];
    $items[] = $p;
}

$errors = [];
$name = $phone = $address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['full_name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!csrf_check()) $errors[] = 'Invalid request.';
    if ($name === '' || strlen($name) > 100) $errors[] = 'Ilagay ang buong pangalan.';
    if (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) $errors[] = 'Maling phone number.';
    if ($address === '' || strlen($address) > 255) $errors[] = 'Ilagay ang address.';

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            $o = $pdo->prepare('INSERT INTO orders (user_id, full_name, phone, address, total) VALUES (?,?,?,?,?)');
            $o->execute([$_SESSION['user_id'], $name, $phone, $address, $total]);
            $orderId = (int)$pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, qty) VALUES (?,?,?,?,?)');
            $dec = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

            foreach ($items as $it) {
                $dec->execute([$it['qty'], $it['id'], $it['qty']]);
                if ($dec->rowCount() === 0) {
                    throw new RuntimeException('Kulang na ang stock ng ' . $it['name']);
                }
                $ins->execute([$orderId, $it['id'], $it['name'], $it['price'], $it['qty']]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            flash('Order #' . $orderId . ' ay na-place na! (Cash on Delivery)');
            redirect('orders.php');
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getMessage();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'May error sa pag-order. Subukan ulit.';
        }
    }
}

$pageTitle = 'Checkout';
require 'header.php';
?>
<h1>Checkout</h1>
<?php foreach ($errors as $err): ?><p class="error"><?= e($err) ?></p><?php endforeach; ?>

<table>
    <?php foreach ($items as $it): ?>
    <tr><td><?= e($it['name']) ?> × <?= (int)$it['qty'] ?></td><td><?= money($it['qty'] * (float)$it['price']) ?></td></tr>
    <?php endforeach; ?>
    <tr><td><strong>Total</strong></td><td><strong><?= money($total) ?></strong></td></tr>
</table>

<form method="post" class="stack">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Buong pangalan <input type="text" name="full_name" value="<?= e($name) ?>" required></label>
    <label>Phone number <input type="text" name="phone" value="<?= e($phone) ?>" required></label>
    <label>Address <input type="text" name="address" value="<?= e($address) ?>" required></label>
    <p><small>Bayad: Cash on Delivery.</small></p>
    <button type="submit">Place order</button>
</form>
<?php require 'footer.php'; ?>

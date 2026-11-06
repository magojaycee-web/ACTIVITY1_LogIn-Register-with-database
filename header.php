<?php
$base = $base ?? '';
$cartCount = array_sum($_SESSION['cart'] ?? []);
$flashMsg = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'MAGS COFFEE') ?></title>
<link rel="stylesheet" href="<?= $base ?>style.css">
</head>
<body>
<header class="nav">
    <a class="brand" href="<?= $base ?>index.php">MAGS COFFEE</a>
    <nav>
        <a href="<?= $base ?>index.php">Shop</a>
        <a href="<?= $base ?>cart.php">Cart (<?= (int)$cartCount ?>)</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="<?= $base ?>orders.php">My Orders</a>
            <?php if (is_admin()): ?><a href="<?= $base ?>admin/index.php">Admin</a><?php endif; ?>
            <a href="<?= $base ?>logout.php">Logout (<?= e($_SESSION['username']) ?>)</a>
        <?php else: ?>
            <a href="<?= $base ?>login.php">Login</a>
            <a href="<?= $base ?>register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="wrap">
<?php if ($flashMsg): ?>
    <p class="<?= e($flashMsg['type']) ?>"><?= e($flashMsg['msg']) ?></p>
<?php endif; ?>

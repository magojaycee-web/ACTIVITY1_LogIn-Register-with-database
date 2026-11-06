<?php
require 'config.php';
$pageTitle = 'MAGS COFFEE - Menu';

$cat = trim($_GET['cat'] ?? '');
$categories = $pdo->query('SELECT DISTINCT category FROM products ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);

if ($cat !== '') {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE category = ? ORDER BY name');
    $stmt->execute([$cat]);
} else {
    $stmt = $pdo->query('SELECT * FROM products ORDER BY category, name');
}
$products = $stmt->fetchAll();

require 'header.php';
?>
<h1>Our Menu</h1>
<div class="chips">
    <a href="index.php" class="<?= $cat === '' ? 'active' : '' ?>">All</a>
    <?php foreach ($categories as $c): ?>
        <a href="index.php?cat=<?= urlencode($c) ?>" class="<?= $cat === $c ? 'active' : '' ?>"><?= e($c) ?></a>
    <?php endforeach; ?>
</div>

<div class="grid">
<?php foreach ($products as $p): ?>
    <div class="product">
        <div class="img">
            <?php if ($p['image_url']): ?>
                <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>">
            <?php else: ?>
                <span>No image</span>
            <?php endif; ?>
        </div>
        <small><?= e($p['category']) ?></small>
        <h3><?= e($p['name']) ?></h3>
        <p class="desc"><?= e($p['description'] ?? '') ?></p>
        <p class="price"><?= money((float)$p['price']) ?></p>
        <?php if ($p['stock'] > 0): ?>
            <form method="post" action="cart.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit">Add to cart</button>
            </form>
        <?php else: ?>
            <button disabled>Out of stock</button>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php if (!$products): ?><p>Walang products.</p><?php endif; ?>
</div>
<?php require 'footer.php'; ?>

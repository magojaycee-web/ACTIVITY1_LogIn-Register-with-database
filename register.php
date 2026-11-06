<?php
require 'config.php';

$errors = [];
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!csrf_check()) $errors[] = 'Invalid request.';
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) $errors[] = 'Username: 3-30 characters (letters, numbers, underscore).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Maling email.';
    if (strlen($password) < 8) $errors[] = 'Password: hindi bababa sa 8 characters.';
    if ($password !== $confirm) $errors[] = 'Hindi magkapareho ang password.';

    if (!$errors) {
        $s = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $s->execute([$username, $email]);
        if ($s->fetch()) $errors[] = 'Gamit na ang username o email.';
    }

    if (!$errors) {
        $s = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?,?,?, 'customer')");
        $s->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
        flash('Registered na! Pwede ka nang mag-login.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
require 'header.php';
?>
<div class="card narrow">
    <h1>Register</h1>
    <?php foreach ($errors as $err): ?><p class="error"><?= e($err) ?></p><?php endforeach; ?>
    <form method="post" class="stack">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label>Username <input type="text" name="username" value="<?= e($username) ?>" required></label>
        <label>Email <input type="email" name="email" value="<?= e($email) ?>" required></label>
        <label>Password <input type="password" name="password" required></label>
        <label>Confirm password <input type="password" name="confirm" required></label>
        <button type="submit">Register</button>
    </form>
    <p>May account ka na? <a href="login.php">Login</a></p>
</div>
<?php require 'footer.php'; ?>

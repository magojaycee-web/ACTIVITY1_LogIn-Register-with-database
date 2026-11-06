<?php
require 'config.php';

if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_check()) {
        $error = 'Invalid request.';
    } else {
        $s = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? OR email = ?');
        $s->execute([$login, $login]);
        $user = $s->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            redirect('index.php');
        }
        $error = 'Maling username/email o password.';
    }
}

$pageTitle = 'Login';
require 'header.php';
?>
<div class="card narrow">
    <h1>Login</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <form method="post" class="stack">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label>Username o email <input type="text" name="login" value="<?= e($login) ?>" required></label>
        <label>Password <input type="password" name="password" required></label>
        <button type="submit">Login</button>
    </form>
    <p>Wala pang account? <a href="register.php">Register</a></p>
</div>
<?php require 'footer.php'; ?>

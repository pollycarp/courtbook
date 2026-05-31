<?php
session_start();
if (!empty($_SESSION['admin'])) { header('Location: index.php'); exit; }

$rootDir = dirname(__DIR__);
include $rootDir . '/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, full_name, password_hash FROM users WHERE email = :email AND is_admin = 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin']      = true;
        $_SESSION['admin_name'] = $user['full_name'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – CourtBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#1e3c2c] flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-[#1e3c2c] px-8 py-7 text-center text-white">
            <h1 class="text-2xl font-extrabold">CourtBook Admin</h1>
            <p class="text-green-300 text-xs mt-1">Staff access only</p>
        </div>
        <div class="px-8 py-7">
            <?php if ($error): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Email</label>
                    <input type="email" name="email" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                </div>
                <button type="submit"
                        class="w-full bg-[#2a7f4e] hover:bg-[#1e5c38] text-white font-semibold py-3 rounded-lg transition text-sm">
                    Log In as Admin
                </button>
            </form>
            <p class="text-center mt-5">
                <a href="../index.php" class="text-xs text-gray-400 hover:underline">← Back to CourtBook</a>
            </p>
        </div>
    </div>
</body>
</html>

<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

include 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, password_hash FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In – CourtBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#1e3c2c] to-[#2a4a35] p-4 sm:p-8 flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">

        <div class="bg-gradient-to-r from-[#1e3c2c] to-[#2a7f4e] px-8 py-8 text-center text-white">
            <h1 class="text-3xl font-extrabold">CourtBook</h1>
            <p class="mt-1 text-green-200 text-sm">Welcome back</p>
        </div>

        <div class="px-8 py-8">

            <?php if ($error): ?>
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Email Address</label>
                    <input type="email" name="email" required autofocus
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                </div>
                <button type="submit"
                        class="w-full bg-[#2a7f4e] hover:bg-[#1e5c38] text-white font-semibold py-3 rounded-lg transition text-sm mt-2">
                    Log In
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Don't have an account?
                <a href="register.php" class="text-[#2a7f4e] font-semibold hover:underline">Register</a>
            </p>
        </div>
    </div>
</body>
</html>

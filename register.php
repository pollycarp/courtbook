<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

include 'db_connect.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$fullName || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check email not already taken
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute(['email' => $email]);
        if ($check->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins  = $pdo->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (:name, :email, :hash)");
            $ins->execute(['name' => htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'), 'email' => $email, 'hash' => $hash]);
            $success = 'Account created! You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – CourtBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#1e3c2c] to-[#2a4a35] p-4 sm:p-8 flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">

        <div class="bg-gradient-to-r from-[#1e3c2c] to-[#2a7f4e] px-8 py-8 text-center text-white">
            <h1 class="text-3xl font-extrabold">CourtBook</h1>
            <p class="mt-1 text-green-200 text-sm">Create your account</p>
        </div>

        <div class="px-8 py-8">

            <?php if ($error): ?>
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
                <?= htmlspecialchars($success) ?>
                <a href="login.php" class="font-bold underline ml-1">Log in here</a>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Full Name</label>
                    <input type="text" name="full_name" required
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Email Address</label>
                    <input type="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Password <span class="font-normal text-gray-400">(min 8 chars)</span></label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Confirm Password</label>
                    <input type="password" name="confirm_password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                </div>
                <button type="submit"
                        class="w-full bg-[#2a7f4e] hover:bg-[#1e5c38] text-white font-semibold py-3 rounded-lg transition text-sm mt-2">
                    Create Account
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Already have an account?
                <a href="login.php" class="text-[#2a7f4e] font-semibold hover:underline">Log in</a>
            </p>
        </div>
    </div>
</body>
</html>

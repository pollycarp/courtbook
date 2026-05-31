<?php
require 'auth.php';
$rootDir = dirname(__DIR__);
include $rootDir . '/db_connect.php';

$users = $pdo->query(
    "SELECT u.id, u.full_name, u.email, u.is_admin, u.created_at,
            COUNT(b.id) AS booking_count
     FROM users u
     LEFT JOIN bookings b ON b.user_id = u.id
     GROUP BY u.id
     ORDER BY u.created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users – CourtBook Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'partials/nav.php'; ?>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        <h2 class="text-2xl font-extrabold text-gray-800 mb-6">Registered Users</h2>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#2a7f4e] text-white text-left text-xs uppercase tracking-widest">
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Bookings</th>
                            <th class="px-5 py-3">Registered</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 font-medium text-gray-800"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td class="px-5 py-3 text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                    <?= $u['is_admin'] ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $u['is_admin'] ? 'Admin' : 'User' ?>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600"><?= $u['booking_count'] ?></td>
                            <td class="px-5 py-3 text-gray-500 text-xs"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>

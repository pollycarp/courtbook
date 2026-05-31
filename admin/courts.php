<?php
require 'auth.php';
$rootDir = dirname(__DIR__);
include $rootDir . '/db_connect.php';

$message = '';
$error   = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name      = trim($_POST['name'] ?? '');
        $sportType = $_POST['sport_type'] ?? '';
        if (!$name || !in_array($sportType, ['football', 'tennis'])) {
            $error = 'Please provide a valid name and sport type.';
        } else {
            $pdo->prepare("INSERT INTO courts (name, sport_type, is_active) VALUES (:name, :sport, 1)")
                ->execute(['name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), 'sport' => $sportType]);
            $message = 'Court added successfully.';
        }
    } elseif ($action === 'toggle') {
        $id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $isActive  = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT);
        if ($id) {
            $pdo->prepare("UPDATE courts SET is_active = :active WHERE id = :id")
                ->execute(['active' => $isActive ? 0 : 1, 'id' => $id]);
            $message = 'Court status updated.';
        }
    } elseif ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $pdo->prepare("DELETE FROM courts WHERE id = :id")->execute(['id' => $id]);
            $message = 'Court deleted.';
        }
    }
}

$courts = $pdo->query(
    "SELECT c.*, COUNT(b.id) AS booking_count
     FROM courts c
     LEFT JOIN bookings b ON c.id = b.court_id
     GROUP BY c.id
     ORDER BY c.sport_type, c.name"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courts – CourtBook Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'partials/nav.php'; ?>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        <h2 class="text-2xl font-extrabold text-gray-800 mb-6">Court Management</h2>

        <?php if ($message): ?>
        <div class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Add court form -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h3 class="font-bold text-gray-700 mb-4">Add New Court</h3>
            <form method="POST" class="flex flex-wrap gap-3 items-end">
                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Court Name</label>
                    <input type="text" name="name" required placeholder="e.g. Pitch A"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2a7f4e]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Sport Type</label>
                    <select name="sport_type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2a7f4e]">
                        <option value="football">⚽ Football</option>
                        <option value="tennis">🎾 Tennis</option>
                    </select>
                </div>
                <button type="submit"
                        class="bg-[#2a7f4e] hover:bg-[#1e5c38] text-white font-semibold px-5 py-2 rounded-lg text-sm transition">
                    Add Court
                </button>
            </form>
        </div>

        <!-- Courts table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#2a7f4e] text-white text-left text-xs uppercase tracking-widest">
                            <th class="px-5 py-3">Court Name</th>
                            <th class="px-5 py-3">Sport</th>
                            <th class="px-5 py-3">Total Bookings</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($courts as $c): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 font-medium text-gray-800"><?= htmlspecialchars($c['name']) ?></td>
                            <td class="px-5 py-3 text-gray-500">
                                <?= $c['sport_type'] === 'football' ? '⚽ Football' : '🎾 Tennis' ?>
                            </td>
                            <td class="px-5 py-3 text-gray-600"><?= $c['booking_count'] ?></td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                    <?= $c['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="px-5 py-3 flex items-center gap-3">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action"    value="toggle">
                                    <input type="hidden" name="id"        value="<?= $c['id'] ?>">
                                    <input type="hidden" name="is_active" value="<?= $c['is_active'] ?>">
                                    <button type="submit"
                                            class="text-xs font-semibold text-blue-600 hover:underline transition">
                                        <?= $c['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                                <form method="POST" class="inline"
                                      onsubmit="return confirm('Delete this court? This cannot be undone.')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id"     value="<?= $c['id'] ?>">
                                    <button type="submit"
                                            class="text-xs font-semibold text-red-500 hover:underline transition">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>

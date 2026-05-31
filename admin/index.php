<?php
require 'auth.php';
$rootDir = dirname(__DIR__);
include $rootDir . '/db_connect.php';

// Stats
$totalBookings  = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$todayBookings  = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_date = CURDATE()")->fetchColumn();
$weekBookings   = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 DAY)")->fetchColumn();
$activeCourts   = $pdo->query("SELECT COUNT(*) FROM courts WHERE is_active = 1")->fetchColumn();
$totalUsers     = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();

// Most popular court
$popStmt = $pdo->query(
    "SELECT c.name, COUNT(*) AS total FROM bookings b
     JOIN courts c ON b.court_id = c.id
     GROUP BY b.court_id ORDER BY total DESC LIMIT 1"
);
$popular = $popStmt->fetch(PDO::FETCH_ASSOC);

// Recent 10 bookings
$recentStmt = $pdo->query(
    "SELECT b.reference, b.customer_name, b.booking_date, b.time_slot,
            c.name AS court_name, c.sport_type
     FROM bookings b
     JOIN courts c ON b.court_id = c.id
     ORDER BY b.id DESC LIMIT 10"
);
$recent = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – CourtBook Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <?php include 'partials/nav.php'; ?>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <h2 class="text-2xl font-extrabold text-gray-800 mb-6">Dashboard</h2>

        <!-- Stats cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <?php
            $cards = [
                ['label' => 'Total Bookings',   'value' => $totalBookings,  'color' => 'bg-[#2a7f4e]'],
                ['label' => "Today's Bookings", 'value' => $todayBookings,  'color' => 'bg-blue-600'],
                ['label' => 'This Week',         'value' => $weekBookings,   'color' => 'bg-purple-600'],
                ['label' => 'Active Courts',     'value' => $activeCourts,   'color' => 'bg-amber-500'],
                ['label' => 'Registered Users',  'value' => $totalUsers,     'color' => 'bg-rose-500'],
            ];
            foreach ($cards as $c): ?>
            <div class="<?= $c['color'] ?> text-white rounded-xl px-5 py-4 shadow">
                <p class="text-3xl font-extrabold"><?= $c['value'] ?></p>
                <p class="text-xs font-semibold mt-1 opacity-90"><?= $c['label'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($popular): ?>
        <div class="mb-8 bg-white rounded-xl shadow p-5 inline-flex items-center gap-4">
            <span class="text-3xl">🏆</span>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Most Popular Court</p>
                <p class="text-lg font-extrabold text-[#1e3c2c]"><?= htmlspecialchars($popular['name']) ?></p>
                <p class="text-sm text-gray-500"><?= $popular['total'] ?> bookings total</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent bookings table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-700">Recent Bookings</h3>
                <a href="bookings.php" class="text-sm text-[#2a7f4e] font-semibold hover:underline">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-widest">
                            <th class="px-5 py-3">Reference</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Court</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recent as $b): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 font-mono text-xs font-bold text-[#1e3c2c]">
                                <?= htmlspecialchars($b['reference'] ?? '—') ?>
                            </td>
                            <td class="px-5 py-3 text-gray-700"><?= htmlspecialchars($b['customer_name']) ?></td>
                            <td class="px-5 py-3 text-gray-600">
                                <?= $b['sport_type'] === 'football' ? '⚽' : '🎾' ?>
                                <?= htmlspecialchars($b['court_name']) ?>
                            </td>
                            <td class="px-5 py-3 text-gray-600"><?= htmlspecialchars($b['booking_date']) ?></td>
                            <td class="px-5 py-3 text-gray-600">
                                <?= substr($b['time_slot'], 0, 5) ?> – <?= date('H:i', strtotime($b['time_slot']) + 3600) ?>
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

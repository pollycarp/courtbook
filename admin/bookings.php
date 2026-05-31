<?php
require 'auth.php';
$rootDir = dirname(__DIR__);
include $rootDir . '/db_connect.php';

// Filters
$filterName  = trim($_GET['name']  ?? '');
$filterSport = $_GET['sport'] ?? '';
$filterDate  = $_GET['date']  ?? '';

$params = [];
$where  = ['1=1'];

if ($filterName !== '') {
    $where[]           = 'b.customer_name LIKE :name';
    $params['name']    = '%' . $filterName . '%';
}
if ($filterSport === 'football' || $filterSport === 'tennis') {
    $where[]           = 'c.sport_type = :sport';
    $params['sport']   = $filterSport;
}
if ($filterDate !== '') {
    $where[]           = 'b.booking_date = :date';
    $params['date']    = $filterDate;
}

$sql = "SELECT b.id, b.reference, b.customer_name, b.booking_date, b.time_slot,
               c.name AS court_name, c.sport_type
        FROM bookings b
        JOIN courts c ON b.court_id = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY b.booking_date DESC, b.time_slot ASC
        LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings – CourtBook Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'partials/nav.php'; ?>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <h2 class="text-2xl font-extrabold text-gray-800 mb-6">All Bookings</h2>

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Customer Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($filterName) ?>"
                       placeholder="Search…"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2a7f4e]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Sport</label>
                <select name="sport" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2a7f4e]">
                    <option value="">All</option>
                    <option value="football" <?= $filterSport === 'football' ? 'selected' : '' ?>>Football</option>
                    <option value="tennis"   <?= $filterSport === 'tennis'   ? 'selected' : '' ?>>Tennis</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Date</label>
                <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2a7f4e]">
            </div>
            <button type="submit"
                    class="bg-[#2a7f4e] hover:bg-[#1e5c38] text-white font-semibold px-5 py-2 rounded-lg text-sm transition">
                Filter
            </button>
            <a href="bookings.php" class="border border-gray-300 text-gray-600 hover:bg-gray-100 font-semibold px-4 py-2 rounded-lg text-sm transition">
                Reset
            </a>
        </form>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#2a7f4e] text-white text-left text-xs uppercase tracking-widest">
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Court</th>
                            <th class="px-4 py-3">Sport</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Time</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($bookings)): ?>
                        <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No bookings found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($bookings as $b): ?>
                        <tr class="hover:bg-gray-50 transition" id="row-<?= $b['id'] ?>">
                            <td class="px-4 py-3 font-mono text-xs font-bold text-[#1e3c2c]">
                                <?= htmlspecialchars($b['reference'] ?? '—') ?>
                            </td>
                            <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($b['customer_name']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($b['court_name']) ?></td>
                            <td class="px-4 py-3 text-gray-500">
                                <?= $b['sport_type'] === 'football' ? '⚽ Football' : '🎾 Tennis' ?>
                            </td>
                            <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($b['booking_date']) ?></td>
                            <td class="px-4 py-3 text-gray-600">
                                <?= substr($b['time_slot'], 0, 5) ?> – <?= date('H:i', strtotime($b['time_slot']) + 3600) ?>
                            </td>
                            <td class="px-4 py-3">
                                <button onclick="adminDelete(<?= $b['id'] ?>, '<?= htmlspecialchars($b['reference'] ?? '#'.$b['id'], ENT_QUOTES) ?>')"
                                        class="text-xs font-semibold text-red-500 hover:text-red-700 hover:underline transition">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2 text-right">Showing up to 200 results</p>
    </main>

    <script>
        async function adminDelete(id, ref) {
            const { isConfirmed } = await Swal.fire({
                title: 'Delete Booking?',
                text: `Permanently delete booking ${ref}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280'
            });
            if (!isConfirmed) return;

            const res  = await fetch('delete_booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ id })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('row-' + id)?.remove();
                Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, confirmButtonColor: '#2a7f4e' });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.error, confirmButtonColor: '#2a7f4e' });
            }
        }
    </script>
</body>
</html>

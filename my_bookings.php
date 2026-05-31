<?php
session_start();
include 'db_connect.php';

$loggedIn  = isset($_SESSION['user_id']);
$sessionId = $_SESSION['user_id'] ?? null;

// Optional name filter
$filterName = trim($_GET['name'] ?? '');
$params     = [];

$sql = "SELECT b.id, b.reference, c.name AS court_name, c.sport_type,
               b.customer_name, b.booking_date, b.time_slot
        FROM bookings b
        JOIN courts c ON b.court_id = c.id
        WHERE 1=1";

// If logged in, show only this user's bookings
if ($loggedIn) {
    $sql .= " AND b.user_id = :uid";
    $params['uid'] = $sessionId;
}

if ($filterName !== '') {
    $sql .= " AND b.customer_name LIKE :name";
    $params['name'] = '%' . $filterName . '%';
}

$sql .= " ORDER BY b.booking_date DESC, b.time_slot ASC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = new DateTime('today');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings – CourtBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#1e3c2c] to-[#2a4a35] p-4 sm:p-8">

    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden">

        <!-- Header -->
        <div class="bg-gradient-to-r from-[#1e3c2c] to-[#2a7f4e] px-8 py-8 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight">My Bookings</h1>
                    <p class="mt-1 text-green-200 text-sm">
                        <?= $loggedIn ? 'Your personal reservations' : 'Showing all public bookings — <a href="login.php" class="underline font-bold">log in</a> to see only yours' ?>
                    </p>
                </div>
                <a href="index.php"
                   class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 transition
                          text-white text-sm font-semibold px-4 py-2 rounded-lg self-start sm:self-auto">
                    ← Back to Home
                </a>
            </div>
        </div>

        <div class="p-6 sm:p-8">

            <!-- Search bar -->
            <form method="GET" class="flex gap-3 mb-6">
                <input type="text" name="name" value="<?= htmlspecialchars($filterName) ?>"
                       placeholder="Search by customer name…"
                       class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                <button type="submit"
                        class="bg-[#2a7f4e] hover:bg-[#1e5c38] text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition">
                    Search
                </button>
                <?php if ($filterName): ?>
                <a href="my_bookings.php"
                   class="border border-gray-300 hover:bg-gray-100 text-gray-600 font-semibold px-4 py-2.5 rounded-lg text-sm transition">
                    Clear
                </a>
                <?php endif; ?>
            </form>

            <?php if (count($bookings) === 0): ?>
                <div class="text-center py-16 text-gray-400">
                    <svg class="mx-auto mb-4 h-14 w-14 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-lg font-semibold">
                        <?= $filterName ? 'No bookings found for "' . htmlspecialchars($filterName) . '"' : 'No bookings yet' ?>
                    </p>
                    <a href="index.php"
                       class="inline-block mt-5 bg-[#2a7f4e] hover:bg-[#1e5c38] transition
                              text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                        Book a Court
                    </a>
                </div>

            <?php else: ?>
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#2a7f4e] text-white text-left">
                                <th class="px-4 py-3.5 font-semibold">Reference</th>
                                <th class="px-4 py-3.5 font-semibold">Court</th>
                                <th class="px-4 py-3.5 font-semibold">Sport</th>
                                <th class="px-4 py-3.5 font-semibold">Customer</th>
                                <th class="px-4 py-3.5 font-semibold">Date</th>
                                <th class="px-4 py-3.5 font-semibold">Time Slot</th>
                                <th class="px-4 py-3.5 font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($bookings as $i => $b):
                                $bDate    = new DateTime($b['booking_date'] . ' ' . $b['time_slot']);
                                $isPast   = $bDate <= new DateTime();
                                $rowClass = $isPast ? 'bg-gray-50 opacity-60' : ($i % 2 === 0 ? 'bg-white' : 'bg-[#fafff8]');
                            ?>
                            <tr class="<?= $rowClass ?> hover:bg-[#f0f7f2] transition"
                                id="row-<?= $b['id'] ?>">
                                <td class="px-4 py-3.5 font-mono font-bold text-[#1e3c2c] text-xs">
                                    <?= htmlspecialchars($b['reference'] ?? '—') ?>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-[#1e3c2c]">
                                    <?= htmlspecialchars($b['court_name']) ?>
                                </td>
                                <td class="px-4 py-3.5 text-gray-500">
                                    <?= $b['sport_type'] === 'football' ? '⚽ Football' : '🎾 Tennis' ?>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">
                                    <?= htmlspecialchars($b['customer_name']) ?>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">
                                    <?= htmlspecialchars($b['booking_date']) ?>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">
                                    <?= substr($b['time_slot'], 0, 5) ?> – <?= date('H:i', strtotime($b['time_slot']) + 3600) ?>
                                </td>
                                <td class="px-4 py-3.5">
                                    <?php if (!$isPast): ?>
                                    <button onclick="cancelBooking(<?= $b['id'] ?>, '<?= htmlspecialchars($b['customer_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($b['reference'] ?? '', ENT_QUOTES) ?>')"
                                            class="text-xs font-semibold text-red-500 hover:text-red-700 hover:underline transition">
                                        Cancel
                                    </button>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400">Past</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-400 mt-3 text-right">Showing up to 100 most recent bookings</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        async function cancelBooking(id, name, reference) {
            const label = reference || '#' + id;
            const { isConfirmed } = await Swal.fire({
                title: 'Cancel Booking?',
                html: `<p class="text-gray-600 text-sm">Are you sure you want to cancel booking <strong>${label}</strong>?</p>
                       <p class="text-gray-500 text-xs mt-2">This action cannot be undone.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel it',
                cancelButtonText: 'Keep it',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280'
            });

            if (!isConfirmed) return;

            const params = new URLSearchParams({ id, name });
            try {
                const res  = await fetch('cancel_booking.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params
                });
                const data = await res.json();

                if (data.success) {
                    document.getElementById('row-' + id)?.remove();
                    Swal.fire({ icon: 'success', title: 'Cancelled', text: data.message, confirmButtonColor: '#2a7f4e' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.error, confirmButtonColor: '#2a7f4e' });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not cancel. Please try again.', confirmButtonColor: '#2a7f4e' });
            }
        }
    </script>
</body>
</html>

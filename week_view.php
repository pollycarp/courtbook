<?php
session_start();
include 'db_connect.php';

$loggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

// Build 7-day window starting today
$days      = [];
$startDate = new DateTime('today');
for ($i = 0; $i < 7; $i++) {
    $days[] = (clone $startDate)->modify("+$i days")->format('Y-m-d');
}

$timeSlots = [
    '09:00:00','10:00:00','11:00:00','12:00:00','13:00:00','14:00:00',
    '15:00:00','16:00:00','17:00:00','18:00:00','19:00:00',
];

// Fetch ALL bookings for the 7-day window in one query
$stmt = $pdo->prepare(
    "SELECT booking_date, time_slot, COUNT(*) AS booked_count
     FROM bookings
     WHERE booking_date BETWEEN :start AND :end
     GROUP BY booking_date, time_slot"
);
$stmt->execute(['start' => $days[0], 'end' => $days[6]]);
$rawBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build a lookup map: $bookedMap['2026-06-01']['09:00:00'] = n
$bookedMap = [];
foreach ($rawBookings as $row) {
    $bookedMap[$row['booking_date']][$row['time_slot']] = (int) $row['booked_count'];
}

// Fetch total active courts per sport combination (for availability = total - booked)
$totalCourts = (int) $pdo->query("SELECT COUNT(*) FROM courts WHERE is_active = 1")->fetchColumn();

$now = new DateTime();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Week View – CourtBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/motion@10.18.0/dist/motion.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #week-header  { opacity: 0; }
        #week-grid    { opacity: 0; }
        .slot-cell { cursor: pointer; transition: transform .1s; }
        .slot-cell:hover { transform: scale(1.05); }
        .slot-past { opacity: .35; cursor: default; pointer-events: none; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#1e3c2c] to-[#2a4a35] p-4 sm:p-6">

    <div class="max-w-7xl mx-auto">

        <!-- Header card -->
        <div id="week-header" class="bg-white rounded-2xl shadow-2xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-[#1e3c2c] to-[#2a7f4e] px-8 py-7 text-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight">Week View</h1>
                        <p class="text-green-200 text-sm mt-1">
                            <?= date('d M Y', strtotime($days[0])) ?> – <?= date('d M Y', strtotime($days[6])) ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <?php if ($loggedIn): ?>
                        <span class="text-green-200">Hello, <strong><?= htmlspecialchars($userName) ?></strong></span>
                        <a href="logout.php" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg font-semibold text-xs transition">Log Out</a>
                        <?php else: ?>
                        <a href="login.php"    class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg font-semibold text-xs transition">Log In</a>
                        <a href="register.php" class="bg-white text-[#1e3c2c] hover:bg-green-100 px-3 py-1 rounded-lg font-semibold text-xs transition">Register</a>
                        <?php endif; ?>
                        <a href="index.php" class="bg-white/10 hover:bg-white/20 px-3 py-1 rounded-lg font-semibold text-xs transition">Single Slot →</a>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 flex flex-wrap gap-4 text-xs font-semibold">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-500 inline-block"></span> Available</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-400 inline-block"></span> Partially booked</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-400 inline-block"></span> Fully booked</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-300 inline-block"></span> Past / unavailable</span>
            </div>
        </div>

        <!-- Calendar grid -->
        <div id="week-grid" class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-[#1e3c2c] text-white">
                            <th class="px-3 py-3 text-left font-semibold w-24 sticky left-0 bg-[#1e3c2c] z-10">Time</th>
                            <?php foreach ($days as $day): ?>
                            <th class="px-2 py-3 text-center font-semibold min-w-[100px]
                                <?= $day === date('Y-m-d') ? 'bg-[#2a7f4e]' : '' ?>">
                                <div><?= date('D', strtotime($day)) ?></div>
                                <div class="font-normal opacity-80"><?= date('d M', strtotime($day)) ?></div>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($timeSlots as $slot):
                            $slotHour    = (int) substr($slot, 0, 2);
                            $slotDisplay = substr($slot, 0, 5) . ' – ' . date('H:i', strtotime($slot) + 3600);
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 py-2 font-semibold text-gray-600 sticky left-0 bg-white border-r border-gray-100">
                                <?= $slotDisplay ?>
                            </td>
                            <?php foreach ($days as $day):
                                $bookedCount = $bookedMap[$day][$slot] ?? 0;
                                $available   = max(0, $totalCourts - $bookedCount);

                                // Is this slot in the past?
                                $slotDT  = new DateTime($day . ' ' . $slot);
                                $isPast  = $slotDT <= $now;

                                if ($isPast) {
                                    $bg = 'bg-gray-100 text-gray-400';
                                    $cls = 'slot-past';
                                } elseif ($available === 0) {
                                    $bg  = 'bg-red-100 text-red-700';
                                    $cls = 'slot-cell';
                                } elseif ($bookedCount > 0) {
                                    $bg  = 'bg-amber-100 text-amber-800';
                                    $cls = 'slot-cell';
                                } else {
                                    $bg  = 'bg-green-100 text-green-800';
                                    $cls = 'slot-cell';
                                }
                            ?>
                            <td class="px-1 py-1 text-center">
                                <div class="<?= $bg ?> <?= $cls ?> rounded-lg mx-1 py-2 px-1
                                    <?= $day === date('Y-m-d') ? 'ring-1 ring-[#2a7f4e]' : '' ?>"
                                    <?= !$isPast && $available > 0 ? "onclick=\"goBook('$day','$slot')\"" : '' ?>>
                                    <?php if ($isPast): ?>
                                        <span class="text-gray-300">—</span>
                                    <?php elseif ($available === 0): ?>
                                        <span class="font-bold">Full</span>
                                    <?php else: ?>
                                        <span class="font-bold"><?= $available ?></span>
                                        <span class="block opacity-70" style="font-size:10px">free</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="my_bookings.php" class="text-green-200 font-semibold text-sm hover:underline">View My Bookings →</a>
        </div>
    </div>

    <script>
        function goBook(date, time) {
            window.location.href = `index.php?date=${date}&time=${time}`;
        }

        window.addEventListener('DOMContentLoaded', () => {
            const { animate } = Motion;
            animate('#week-header',
                { opacity: [0, 1], y: [-24, 0] },
                { duration: 0.6, easing: [0.22, 1, 0.36, 1] }
            );
            animate('#week-grid',
                { opacity: [0, 1], y: [30, 0] },
                { duration: 0.65, delay: 0.2, easing: [0.22, 1, 0.36, 1] }
            );
        });
    </script>
</body>
</html>

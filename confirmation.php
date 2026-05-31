<?php
include 'db_connect.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

$sql = "SELECT b.*, c.name AS court_name, c.sport_type
        FROM bookings b
        JOIN courts c ON b.court_id = c.id
        WHERE b.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) { header('Location: index.php'); exit; }

$endTime = date('H:i', strtotime($booking['time_slot']) + 3600);
$sport   = $booking['sport_type'] === 'football' ? '⚽ Football' : '🎾 Tennis';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed – CourtBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/motion@10.18.0/dist/motion.js"></script>
    <style>
        #receipt-card { opacity: 0; transform: translateY(32px) scale(0.97); }
        #success-banner { opacity: 0; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-card { box-shadow: none !important; border: 1px solid #ccc; }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#1e3c2c] to-[#2a4a35] p-4 sm:p-8 flex items-center justify-center">

    <div class="w-full max-w-lg">

        <!-- Success banner -->
        <div id="success-banner" class="text-center mb-6 no-print">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-3">
                <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-white">Booking Confirmed!</h1>
            <p class="text-green-200 text-sm mt-1">Your court has been reserved. Keep this reference safe.</p>
        </div>

        <!-- Receipt card -->
        <div id="receipt-card" class="print-card bg-white rounded-2xl shadow-2xl overflow-hidden">

            <!-- Header stripe -->
            <div class="bg-gradient-to-r from-[#1e3c2c] to-[#2a7f4e] px-6 py-5 flex items-center justify-between">
                <span class="text-white font-extrabold text-xl tracking-tight">CourtBook</span>
                <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full tracking-widest">
                    RECEIPT
                </span>
            </div>

            <!-- Reference -->
            <div class="bg-[#f0f7f2] border-b border-green-100 px-6 py-4 text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1">Booking Reference</p>
                <p class="text-3xl font-extrabold text-[#1e3c2c] tracking-widest">
                    <?= htmlspecialchars($booking['reference']) ?>
                </p>
            </div>

            <!-- Details grid -->
            <div class="px-6 py-5 space-y-4">

                <div class="flex justify-between items-start">
                    <span class="text-sm text-gray-500 font-medium">Court</span>
                    <span class="text-sm font-semibold text-gray-800 text-right">
                        <?= htmlspecialchars($booking['court_name']) ?>
                    </span>
                </div>

                <div class="flex justify-between items-start border-t border-gray-100 pt-3">
                    <span class="text-sm text-gray-500 font-medium">Sport</span>
                    <span class="text-sm font-semibold text-gray-800"><?= $sport ?></span>
                </div>

                <div class="flex justify-between items-start border-t border-gray-100 pt-3">
                    <span class="text-sm text-gray-500 font-medium">Date</span>
                    <span class="text-sm font-semibold text-gray-800">
                        <?= date('l, d F Y', strtotime($booking['booking_date'])) ?>
                    </span>
                </div>

                <div class="flex justify-between items-start border-t border-gray-100 pt-3">
                    <span class="text-sm text-gray-500 font-medium">Time Slot</span>
                    <span class="text-sm font-semibold text-gray-800">
                        <?= substr($booking['time_slot'], 0, 5) ?> – <?= $endTime ?>
                    </span>
                </div>

                <div class="flex justify-between items-start border-t border-gray-100 pt-3">
                    <span class="text-sm text-gray-500 font-medium">Name</span>
                    <span class="text-sm font-semibold text-gray-800">
                        <?= htmlspecialchars($booking['customer_name']) ?>
                    </span>
                </div>

                <?php if (!empty($booking['customer_email'])): ?>
                <div class="flex justify-between items-start border-t border-gray-100 pt-3">
                    <span class="text-sm text-gray-500 font-medium">Email</span>
                    <span class="text-sm font-semibold text-gray-800">
                        <?= htmlspecialchars($booking['customer_email']) ?>
                    </span>
                </div>
                <?php endif; ?>

            </div>

            <!-- Footer actions -->
            <div class="px-6 py-5 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row gap-3 no-print">
                <button onclick="window.print()"
                        class="flex-1 bg-[#2a7f4e] hover:bg-[#1e5c38] text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    Print / Save as PDF
                </button>
                <a href="index.php"
                   class="flex-1 text-center border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold py-2.5 rounded-lg transition text-sm">
                    Back to Home
                </a>
                <a href="my_bookings.php"
                   class="flex-1 text-center border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold py-2.5 rounded-lg transition text-sm">
                    My Bookings
                </a>
            </div>
        </div>

    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const { animate } = Motion;
            animate('#success-banner',
                { opacity: [0, 1], y: [-20, 0] },
                { duration: 0.5, easing: [0.22, 1, 0.36, 1] }
            );
            animate('#receipt-card',
                { opacity: [0, 1], y: [32, 0], scale: [0.97, 1] },
                { duration: 0.65, delay: 0.2, easing: [0.22, 1, 0.36, 1] }
            );
        });
    </script>
</body>
</html>

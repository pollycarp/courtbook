<nav class="bg-[#1e3c2c] text-white shadow">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14">
        <div class="flex items-center gap-6">
            <a href="index.php" class="font-extrabold text-base tracking-tight">CourtBook <span class="text-green-400 font-normal text-xs">Admin</span></a>
            <div class="hidden sm:flex items-center gap-4 text-sm">
                <a href="index.php"   class="hover:text-green-300 transition">Dashboard</a>
                <a href="bookings.php" class="hover:text-green-300 transition">Bookings</a>
                <a href="courts.php"  class="hover:text-green-300 transition">Courts</a>
                <a href="users.php"   class="hover:text-green-300 transition">Users</a>
            </div>
        </div>
        <div class="flex items-center gap-3 text-sm">
            <span class="text-green-300 hidden sm:inline"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
            <a href="logout.php" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg text-xs font-semibold transition">
                Log Out
            </a>
            <a href="../index.php" class="bg-white/10 hover:bg-white/20 px-3 py-1 rounded-lg text-xs font-semibold transition">
                ← Site
            </a>
        </div>
    </div>
</nav>

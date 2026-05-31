<?php
session_start();
include 'db_connect.php';
$loggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userId   = $_SESSION['user_id']   ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CourtBook – Sports Booking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                primary: { DEFAULT: '#2a7f4e', dark: '#1e5c38', light: '#f0f7f2' }
            }}}
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Motion One: vanilla-JS animation library (same author as Framer Motion) -->
    <script src="https://cdn.jsdelivr.net/npm/motion@10.18.0/dist/motion.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        /* ── Floating ball keyframes ── */
        @keyframes floatSpin {
            0%,100% { transform: translateY(0px)   rotate(0deg);   }
            33%      { transform: translateY(-22px)  rotate(135deg); }
            66%      { transform: translateY(-10px)  rotate(270deg); }
        }
        @keyframes floatSpinR {
            0%,100% { transform: translateY(0px)   rotate(0deg);    }
            33%      { transform: translateY(-18px)  rotate(-120deg); }
            66%      { transform: translateY(-8px)   rotate(-240deg); }
        }
        .ball-1 { animation: floatSpin  5s ease-in-out infinite; }
        .ball-2 { animation: floatSpinR 4.2s ease-in-out infinite 0.8s; }
        .ball-3 { animation: floatSpin  6.5s ease-in-out infinite 1.8s; }
        .ball-4 { animation: floatSpinR 5.5s ease-in-out infinite 0.4s; }

        /* ── Hero panel hover expand ── */
        .hero-panel {
            transition: flex 0.45s cubic-bezier(0.4,0,0.2,1);
            flex: 1;
        }
        .hero-panel:hover { flex: 1.18; }

        /* ── Court cards start invisible; Motion One reveals them ── */
        .court-card { opacity: 0; transform: translateY(20px); }

        /* ── Smooth scroll ── */
        html { scroll-behavior: smooth; }

        /* ── Nav glass effect ── */
        .glass-nav {
            background: rgba(17, 26, 20, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="min-h-screen bg-[#0f1710]">

<!-- ════════════════════════════════════════
     STICKY NAV
════════════════════════════════════════ -->
<nav class="glass-nav sticky top-0 z-50 border-b border-white/5 px-4 sm:px-8 py-3 flex items-center justify-between">
    <a href="index.php" class="text-white font-extrabold text-xl tracking-tight select-none">
        Court<span class="text-green-400">Book</span>
    </a>
    <div class="flex items-center gap-2 sm:gap-3 text-xs font-semibold">
        <a href="week_view.php"
           class="hidden sm:inline text-gray-400 hover:text-white transition px-2 py-1 rounded-lg hover:bg-white/10">
            📅 Week View
        </a>
        <a href="my_bookings.php"
           class="hidden sm:inline text-gray-400 hover:text-white transition px-2 py-1 rounded-lg hover:bg-white/10">
            My Bookings
        </a>
        <?php if ($loggedIn): ?>
            <span class="text-green-400 hidden sm:inline">Hi, <strong><?= htmlspecialchars($userName) ?></strong></span>
            <a href="logout.php"
               class="bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg transition">
                Log Out
            </a>
        <?php else: ?>
            <a href="login.php"
               class="text-gray-300 hover:text-white px-3 py-1.5 rounded-lg hover:bg-white/10 transition">
                Log In
            </a>
            <a href="register.php"
               class="bg-[#2a7f4e] hover:bg-[#1e5c38] text-white px-3 py-1.5 rounded-lg transition">
                Register
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- ════════════════════════════════════════
     HERO — split-screen sports images
════════════════════════════════════════ -->
<section class="relative w-full flex overflow-hidden" style="height: clamp(280px, 48vh, 480px);">

    <!-- ── Football Panel ── -->
    <div id="hero-football"
         class="hero-panel relative overflow-hidden group cursor-pointer"
         onclick="document.getElementById('booking-section').scrollIntoView({behavior:'smooth'})">

        <!-- Photo -->
        <img src="https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=960&q=85&fit=crop&crop=center"
             alt="Football pitch aerial view"
             class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

        <!-- Colour tint + depth -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-950/55 via-transparent to-black/50"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"></div>

        <!-- SVG football pitch lines overlay -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.12] pointer-events-none"
             viewBox="0 0 500 340" preserveAspectRatio="xMidYMid slice">
            <!-- Outer pitch -->
            <rect x="30" y="20" width="440" height="300" stroke="white" stroke-width="2.5" fill="none"/>
            <!-- Centre line -->
            <line x1="250" y1="20" x2="250" y2="320" stroke="white" stroke-width="1.5"/>
            <!-- Centre circle -->
            <circle cx="250" cy="170" r="55" stroke="white" stroke-width="1.5" fill="none"/>
            <circle cx="250" cy="170" r="3" fill="white"/>
            <!-- Left penalty box -->
            <rect x="30" y="95" width="100" height="150" stroke="white" stroke-width="1.5" fill="none"/>
            <rect x="30" y="125" width="50" height="90" stroke="white" stroke-width="1" fill="none"/>
            <!-- Right penalty box -->
            <rect x="370" y="95" width="100" height="150" stroke="white" stroke-width="1.5" fill="none"/>
            <rect x="420" y="125" width="50" height="90" stroke="white" stroke-width="1" fill="none"/>
            <!-- Penalty spots -->
            <circle cx="100" cy="170" r="3" fill="white"/>
            <circle cx="400" cy="170" r="3" fill="white"/>
            <!-- Corner arcs -->
            <path d="M30,20 Q42,20 42,32" stroke="white" stroke-width="1" fill="none"/>
            <path d="M470,20 Q458,20 458,32" stroke="white" stroke-width="1" fill="none"/>
            <path d="M30,320 Q42,320 42,308" stroke="white" stroke-width="1" fill="none"/>
            <path d="M470,320 Q458,320 458,308" stroke="white" stroke-width="1" fill="none"/>
        </svg>

        <!-- Floating footballs — SVG illustrations -->
        <svg class="ball-1 absolute top-8 right-10 pointer-events-none select-none"
             style="width:68px;height:68px;filter:drop-shadow(0 8px 16px rgba(0,0,0,0.55))"
             viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <radialGradient id="fbg1" cx="35%" cy="28%" r="74%">
              <stop offset="0%"   stop-color="#ffffff"/>
              <stop offset="55%"  stop-color="#f2f2f2"/>
              <stop offset="100%" stop-color="#c0c0c0"/>
            </radialGradient>
            <clipPath id="fbc1"><circle cx="50" cy="50" r="47"/></clipPath>
          </defs>
          <!-- Sphere -->
          <circle cx="50" cy="50" r="47" fill="url(#fbg1)"/>
          <!-- Classic pentagon patches -->
          <polygon points="50,30 62,39 57,52 43,52 38,39"          fill="#111" clip-path="url(#fbc1)"/>
          <polygon points="50,4 66,15 63,30 50,30 37,30 34,15"     fill="#111" clip-path="url(#fbc1)"/>
          <polygon points="82,20 95,37 87,52 72,52 62,39 66,15"    fill="#111" clip-path="url(#fbc1)"/>
          <polygon points="87,52 95,70 78,83 65,76 57,52 72,52"    fill="#111" clip-path="url(#fbc1)"/>
          <polygon points="5,37 18,20 34,15 38,39 28,52 13,52"     fill="#111" clip-path="url(#fbc1)"/>
          <polygon points="13,52 28,52 43,52 35,76 22,83 5,70"     fill="#111" clip-path="url(#fbc1)"/>
          <polygon points="65,76 78,83 66,96 50,96 34,96 22,83 35,76 43,52 57,52" fill="#111" clip-path="url(#fbc1)"/>
          <!-- Specular highlight -->
          <ellipse cx="35" cy="32" rx="14" ry="9" fill="white" opacity="0.45" transform="rotate(-25,35,32)"/>
          <!-- Edge -->
          <circle cx="50" cy="50" r="47" fill="none" stroke="#aaa" stroke-width="1.5"/>
        </svg>

        <svg class="ball-3 absolute top-20 right-28 pointer-events-none select-none"
             style="width:36px;height:36px;opacity:0.55;filter:drop-shadow(0 4px 8px rgba(0,0,0,0.45))"
             viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <radialGradient id="fbg2" cx="35%" cy="28%" r="74%">
              <stop offset="0%"   stop-color="#ffffff"/>
              <stop offset="100%" stop-color="#c0c0c0"/>
            </radialGradient>
            <clipPath id="fbc2"><circle cx="50" cy="50" r="47"/></clipPath>
          </defs>
          <circle cx="50" cy="50" r="47" fill="url(#fbg2)"/>
          <polygon points="50,30 62,39 57,52 43,52 38,39"          fill="#111" clip-path="url(#fbc2)"/>
          <polygon points="50,4 66,15 63,30 50,30 37,30 34,15"     fill="#111" clip-path="url(#fbc2)"/>
          <polygon points="82,20 95,37 87,52 72,52 62,39 66,15"    fill="#111" clip-path="url(#fbc2)"/>
          <polygon points="87,52 95,70 78,83 65,76 57,52 72,52"    fill="#111" clip-path="url(#fbc2)"/>
          <polygon points="5,37 18,20 34,15 38,39 28,52 13,52"     fill="#111" clip-path="url(#fbc2)"/>
          <polygon points="13,52 28,52 43,52 35,76 22,83 5,70"     fill="#111" clip-path="url(#fbc2)"/>
          <polygon points="65,76 78,83 66,96 50,96 34,96 22,83 35,76 43,52 57,52" fill="#111" clip-path="url(#fbc2)"/>
          <ellipse cx="35" cy="32" rx="14" ry="9" fill="white" opacity="0.4" transform="rotate(-25,35,32)"/>
          <circle cx="50" cy="50" r="47" fill="none" stroke="#aaa" stroke-width="1.5"/>
        </svg>

        <!-- Text label -->
        <div class="absolute bottom-0 left-0 p-5 sm:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-400 mb-1">Football</p>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight">Book a Pitch</h2>
            <p class="text-sm text-gray-300 mt-1 max-h-0 overflow-hidden group-hover:max-h-10
                       transition-all duration-300">
                5-a-side &amp; 11-a-side courts available
            </p>
        </div>
    </div>

    <!-- ── Centre badge ── -->
    <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 z-20
                flex items-center justify-center pointer-events-none" style="width:0">
        <div id="hero-badge"
             class="bg-black/40 backdrop-blur-md border border-white/20 rounded-2xl
                    px-4 py-3 text-center shadow-2xl whitespace-nowrap">
            <p class="text-white font-extrabold text-lg tracking-tight">
                Court<span class="text-green-400">Book</span>
            </p>
            <div class="h-px bg-white/20 my-1.5"></div>
            <p class="text-white/50 text-xs leading-snug">Pick a sport<br>↓ scroll to book</p>
        </div>
    </div>

    <!-- ── Tennis Panel ── -->
    <div id="hero-tennis"
         class="hero-panel relative overflow-hidden group cursor-pointer"
         onclick="document.getElementById('booking-section').scrollIntoView({behavior:'smooth'})">

        <!-- Photo -->
        <img src="https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=960&q=85&fit=crop&crop=center"
             alt="Tennis court aerial view"
             class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

        <!-- Colour tint + depth -->
        <div class="absolute inset-0 bg-gradient-to-bl from-blue-950/50 via-transparent to-black/50"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"></div>

        <!-- SVG tennis court lines overlay -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.15] pointer-events-none"
             viewBox="0 0 500 340" preserveAspectRatio="xMidYMid slice">
            <!-- Outer court -->
            <rect x="60" y="30" width="380" height="280" stroke="white" stroke-width="2.5" fill="none"/>
            <!-- Singles sidelines -->
            <line x1="100" y1="30" x2="100" y2="310" stroke="white" stroke-width="1.5"/>
            <line x1="400" y1="30" x2="400" y2="310" stroke="white" stroke-width="1.5"/>
            <!-- Service line (both ends) -->
            <line x1="100" y1="110" x2="400" y2="110" stroke="white" stroke-width="1.2"/>
            <line x1="100" y1="230" x2="400" y2="230" stroke="white" stroke-width="1.2"/>
            <!-- Centre service line -->
            <line x1="250" y1="110" x2="250" y2="230" stroke="white" stroke-width="1.2"/>
            <!-- Net -->
            <line x1="60"  y1="170" x2="440" y2="170" stroke="white" stroke-width="3"/>
            <!-- Centre mark -->
            <line x1="250" y1="165" x2="250" y2="175" stroke="white" stroke-width="2"/>
            <!-- Baseline centre marks -->
            <line x1="250" y1="30"  x2="250" y2="42"  stroke="white" stroke-width="1.5"/>
            <line x1="250" y1="298" x2="250" y2="310" stroke="white" stroke-width="1.5"/>
        </svg>

        <!-- Tennis racket + ball — SVG illustrations -->
        <svg class="ball-2 absolute top-6 left-10 pointer-events-none select-none"
             style="width:56px;height:84px;filter:drop-shadow(0 8px 18px rgba(0,0,0,0.6))"
             viewBox="0 0 60 90" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <clipPath id="rcc1"><ellipse cx="30" cy="30" rx="24" ry="28"/></clipPath>
          </defs>
          <!-- String face (light fill) -->
          <ellipse cx="30" cy="30" rx="24" ry="28" fill="rgba(255,255,255,0.07)"/>
          <!-- Vertical strings -->
          <line x1="14" y1="3"  x2="14" y2="57" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="19" y1="2"  x2="19" y2="58" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="24" y1="2"  x2="24" y2="58" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="30" y1="2"  x2="30" y2="58" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="36" y1="2"  x2="36" y2="58" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="41" y1="2"  x2="41" y2="58" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="46" y1="3"  x2="46" y2="57" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <!-- Horizontal strings -->
          <line x1="6"  y1="8"  x2="54" y2="8"  stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="6"  y1="14" x2="54" y2="14" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="6"  y1="20" x2="54" y2="20" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="6"  y1="26" x2="54" y2="26" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="6"  y1="32" x2="54" y2="32" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="6"  y1="38" x2="54" y2="38" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="6"  y1="44" x2="54" y2="44" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <line x1="6"  y1="50" x2="54" y2="50" stroke="#fff" stroke-width="0.9" opacity="0.7" clip-path="url(#rcc1)"/>
          <!-- Frame (over strings) -->
          <ellipse cx="30" cy="30" rx="24" ry="28" fill="none" stroke="#e8c030" stroke-width="5"/>
          <!-- Inner frame ring -->
          <ellipse cx="30" cy="30" rx="20" ry="24" fill="none" stroke="#f0d050" stroke-width="1" opacity="0.4"/>
          <!-- Throat -->
          <path d="M 20,57 L 22,70 L 38,70 L 40,57"
                fill="none" stroke="#e8c030" stroke-width="4.5" stroke-linejoin="round"/>
          <!-- Handle body -->
          <rect x="22" y="70" width="16" height="19" rx="4" fill="#c8a020"/>
          <!-- Handle highlight -->
          <rect x="22" y="70" width="16" height="5" rx="4" fill="#dbb030" opacity="0.6"/>
          <!-- Grip wrapping bands -->
          <rect x="22" y="76" width="16" height="2" rx="1" fill="#7a6010" opacity="0.6"/>
          <rect x="22" y="81" width="16" height="2" rx="1" fill="#7a6010" opacity="0.6"/>
          <rect x="22" y="86" width="16" height="2" rx="1" fill="#7a6010" opacity="0.6"/>
          <!-- Butt cap -->
          <rect x="20" y="88" width="20" height="2" rx="1" fill="#b09020"/>
        </svg>

        <svg class="ball-4 absolute top-24 left-28 pointer-events-none select-none"
             style="width:42px;height:42px;opacity:0.7;filter:drop-shadow(0 4px 10px rgba(0,0,0,0.45))"
             viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <radialGradient id="tbg1" cx="34%" cy="28%" r="72%">
              <stop offset="0%"   stop-color="#e2fc50"/>
              <stop offset="60%"  stop-color="#b8d800"/>
              <stop offset="100%" stop-color="#7aaa00"/>
            </radialGradient>
            <clipPath id="tbc1"><circle cx="50" cy="50" r="46"/></clipPath>
          </defs>
          <!-- Ball -->
          <circle cx="50" cy="50" r="46" fill="url(#tbg1)"/>
          <!-- White seam — left curve -->
          <path d="M 13,26 Q 44,50 13,74"
                stroke="white" stroke-width="5.5" fill="none"
                stroke-linecap="round" clip-path="url(#tbc1)"/>
          <!-- White seam — right curve -->
          <path d="M 87,26 Q 56,50 87,74"
                stroke="white" stroke-width="5.5" fill="none"
                stroke-linecap="round" clip-path="url(#tbc1)"/>
          <!-- Specular highlight -->
          <ellipse cx="34" cy="32" rx="14" ry="9" fill="white" opacity="0.38" transform="rotate(-22,34,32)"/>
          <!-- Edge -->
          <circle cx="50" cy="50" r="46" fill="none" stroke="#5a9200" stroke-width="1.5"/>
        </svg>

        <!-- Text label -->
        <div class="absolute bottom-0 left-0 p-5 sm:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-yellow-400 mb-1">Tennis</p>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight">Book a Court</h2>
            <p class="text-sm text-gray-300 mt-1 max-h-0 overflow-hidden group-hover:max-h-10
                       transition-all duration-300">
                Hard &amp; clay surface courts
            </p>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-4 inset-x-0 flex justify-center z-30 pointer-events-none">
        <div class="animate-bounce text-white/30">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════
     BOOKING SECTION
════════════════════════════════════════ -->
<div id="booking-section" class="px-4 sm:px-8 py-8">
    <div id="booking-card"
         class="max-w-3xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden"
         x-data="bookingApp()" x-init="init()">

        <!-- Card header -->
        <div class="bg-gradient-to-r from-[#1e3c2c] to-[#2a7f4e] px-6 sm:px-8 py-6 text-center text-white">
            <h2 class="text-2xl font-extrabold tracking-tight">Check Availability</h2>
            <p class="text-green-200 text-sm mt-1">Select your date, time, and sport below</p>
        </div>

        <div class="p-6 sm:p-8">

            <!-- Booking Form -->
            <div class="bg-gray-50 rounded-xl p-5 sm:p-6 mb-8 border border-gray-100">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1.5">Select Date</label>
                        <input type="date" x-model="date" :min="minDate"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1.5">Time Slot</label>
                        <select x-model="time"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                       focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                            <option value="09:00:00">09:00 – 10:00</option>
                            <option value="10:00:00">10:00 – 11:00</option>
                            <option value="11:00:00">11:00 – 12:00</option>
                            <option value="12:00:00">12:00 – 13:00</option>
                            <option value="13:00:00">13:00 – 14:00</option>
                            <option value="14:00:00">14:00 – 15:00</option>
                            <option value="15:00:00">15:00 – 16:00</option>
                            <option value="16:00:00">16:00 – 17:00</option>
                            <option value="17:00:00">17:00 – 18:00</option>
                            <option value="18:00:00">18:00 – 19:00</option>
                            <option value="19:00:00">19:00 – 20:00</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1.5">Sport</label>
                        <select x-model="sport"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                       focus:outline-none focus:ring-2 focus:ring-[#2a7f4e] focus:border-transparent transition">
                            <option value="all">All Sports</option>
                            <option value="football">⚽ Football</option>
                            <option value="tennis">🎾 Tennis</option>
                        </select>
                    </div>
                </div>

                <button @click="checkAvailability()" :disabled="loading"
                        class="w-full bg-[#2a7f4e] hover:bg-[#1e5c38] disabled:opacity-60 disabled:cursor-not-allowed
                               text-white font-semibold py-3 rounded-lg transition duration-200
                               flex items-center justify-center gap-2">
                    <template x-if="loading">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Checking...' : 'Check Availability'"></span>
                </button>
            </div>

            <!-- Results -->
            <div x-show="searched" x-transition>

                <!-- No courts -->
                <template x-if="results.length === 0">
                    <div class="text-center py-10 text-gray-400">
                        <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-base font-semibold">No courts available</p>
                        <p class="text-sm mt-1">Try a different date or time slot.</p>
                    </div>
                </template>

                <!-- Available courts -->
                <template x-if="results.length > 0">
                    <div>
                        <h2 class="text-lg font-bold text-gray-700 mb-4">
                            Available Courts
                            <span class="ml-2 text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full"
                                  x-text="results.length + ' found'"></span>
                        </h2>
                        <div class="space-y-3" id="court-list">
                            <template x-for="court in results" :key="court.id">
                                <div class="court-card bg-[#f0f7f2] border-l-4 border-[#2a7f4e] px-5 py-4 rounded-xl
                                            flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-[#1e3c2c] text-base" x-text="court.name"></h3>
                                        <p class="text-gray-500 text-sm mt-0.5"
                                           x-text="court.sport_type === 'football' ? '⚽ Football Court' : '🎾 Tennis Court'"></p>
                                    </div>
                                    <button @click="bookCourt(court)"
                                            class="shrink-0 bg-[#2a7f4e] hover:bg-[#1e5c38] text-white
                                                   font-semibold py-2 px-6 rounded-lg transition text-sm">
                                        Book Now
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-100 px-8 py-5
                    flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
            <a href="my_bookings.php" class="text-[#2a7f4e] font-semibold hover:underline">
                View My Bookings →
            </a>
            <span class="hidden sm:inline text-gray-300">|</span>
            <a href="week_view.php" class="text-[#2a7f4e] font-semibold hover:underline">
                📅 Week View
            </a>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════ -->
<script>
/* ── Motion One entrance animations ── */
window.addEventListener('DOMContentLoaded', () => {
    const { animate, stagger, inView } = Motion;

    // Hero panels slide in from sides simultaneously
    animate('#hero-football',
        { opacity: [0, 1], x: ['-6%', '0%'] },
        { duration: 0.9, easing: [0.22, 1, 0.36, 1] }
    );
    animate('#hero-tennis',
        { opacity: [0, 1], x: ['6%', '0%'] },
        { duration: 0.9, easing: [0.22, 1, 0.36, 1] }
    );
    animate('#hero-badge',
        { opacity: [0, 1], scale: [0.75, 1] },
        { duration: 0.6, delay: 0.45, easing: [0.34, 1.56, 0.64, 1] }
    );

    // Booking card fades up when it scrolls into view
    inView('#booking-card', () => {
        animate('#booking-card',
            { opacity: [0, 1], y: [40, 0] },
            { duration: 0.65, easing: [0.22, 1, 0.36, 1] }
        );
    }, { amount: 0.15 });

    // Expose stagger animator so Alpine can call it after results render
    window.animateCourtCards = () => {
        animate('.court-card',
            { opacity: [0, 1], y: [20, 0], scale: [0.97, 1] },
            { duration: 0.38, delay: stagger(0.08), easing: [0.22, 1, 0.36, 1] }
        );
    };
});

/* ── Alpine.js app ── */
function bookingApp() {
    return {
        date:    '',
        time:    '09:00:00',
        sport:   'all',
        results: [],
        loading: false,
        searched: false,

        get minDate() {
            return new Date().toISOString().split('T')[0];
        },

        init() {
            const p   = new URLSearchParams(window.location.search);
            this.date = p.get('date') || new Date().toISOString().split('T')[0];
            this.time = p.get('time') || '09:00:00';
            if (p.get('date')) this.checkAvailability();
        },

        async checkAvailability() {
            if (!this.date) {
                Swal.fire({
                    icon: 'warning', title: 'No date selected',
                    text: 'Please choose a date before checking.',
                    confirmButtonColor: '#2a7f4e'
                });
                return;
            }
            this.loading  = true;
            this.searched = false;
            try {
                const res  = await fetch(`check_availability.php?date=${this.date}&time=${this.time}&sport=${this.sport}`);
                const data = await res.json();
                if (data.error) throw new Error(data.error);
                this.results  = data;
                this.searched = true;
                // Trigger stagger animation after Alpine renders the cards
                this.$nextTick(() => {
                    if (window.animateCourtCards) window.animateCourtCards();
                });
            } catch (e) {
                Swal.fire({
                    icon: 'error', title: 'Error',
                    text: e.message || 'Failed to check availability. Please try again.',
                    confirmButtonColor: '#2a7f4e'
                });
            } finally {
                this.loading = false;
            }
        },

        async bookCourt(court) {
            const { value: formValues } = await Swal.fire({
                title: `Book ${court.name}`,
                html: `
                    <p class="text-gray-500 text-sm mb-4">
                        ${court.sport_type === 'football' ? '⚽ Football' : '🎾 Tennis'}
                        &nbsp;•&nbsp; ${this.date}
                        &nbsp;•&nbsp; ${this.time.slice(0,5)}
                    </p>
                    <div class="text-left space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input id="swal-name" class="swal2-input !mx-0 !w-full" placeholder="Your full name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">
                                Email <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <input id="swal-email" type="email" class="swal2-input !mx-0 !w-full" placeholder="your@email.com">
                        </div>
                    </div>
                `,
                showCancelButton:  true,
                confirmButtonText: 'Confirm Booking',
                cancelButtonText:  'Cancel',
                confirmButtonColor:'#2a7f4e',
                cancelButtonColor: '#6b7280',
                focusConfirm: false,
                preConfirm: () => {
                    const name  = document.getElementById('swal-name').value.trim();
                    const email = document.getElementById('swal-email').value.trim();
                    if (!name) { Swal.showValidationMessage('Please enter your full name'); return false; }
                    return { name, email };
                }
            });

            if (!formValues) return;

            const params = new URLSearchParams({
                court_id: court.id,
                date:     this.date,
                time:     this.time,
                name:     formValues.name,
                email:    formValues.email,
                user_id:  '<?= (int)$userId ?>'
            });

            try {
                const res  = await fetch('book.php', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body:    params
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = `confirmation.php?id=${data.booking_id}`;
                } else {
                    Swal.fire({ icon:'error', title:'Booking Failed', text: data.error||'Please try again.', confirmButtonColor:'#2a7f4e' });
                }
            } catch (e) {
                Swal.fire({ icon:'error', title:'Error', text:'Booking failed. Please try again.', confirmButtonColor:'#2a7f4e' });
            }
        }
    };
}
</script>
</body>
</html>

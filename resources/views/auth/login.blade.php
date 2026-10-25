<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - Portal Akreditasi</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-black bg-white">
    <!-- Mobile Navbar -->
    <header class="lg:hidden sticky top-0 z-20 w-full bg-white/95 backdrop-blur-md border-b border-[#EAEAEA] px-5 sm:px-8 py-3.5 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center p-2 shadow-xs shrink-0">
                <img src="{{ asset('images/cap-icon.png') }}" alt="Logo" class="w-6 h-auto">
            </div>
            <div>
                <span class="block text-base font-bold tracking-tight text-primary leading-tight">Portal Akreditasi</span>
                <span class="block text-[11px] text-gray-form uppercase tracking-wider font-medium">Sistem Penjaminan Mutu</span>
            </div>
        </div>
    </header>

    <div class="min-h-[calc(100vh-65px)] lg:min-h-screen flex flex-col lg:flex-row bg-white">
        
        <!-- LEFT SECTION: Abstract Wavy Graphic with 64px Rounded Corners -->
        <div class="relative hidden lg:flex lg:w-1/2 p-12 lg:p-16 flex-col justify-between lg:rounded-tr-[64px] lg:rounded-br-[64px] overflow-hidden bg-cover bg-center bg-no-repeat shadow-xl z-10" style="background-image: url('{{ asset('images/background.jpeg') }}');">
            
            <!-- Top: Attached Graduation Cap Logo -->
            <div class="relative z-10">
                <img src="{{ asset('images/cap-icon.png') }}" alt="Logo Portal" class="w-20 h-auto drop-shadow-md">
            </div>

            <!-- Middle / Lower: "Let's Get Started" Heading -->
            <div class="relative z-10 my-auto pt-24 pb-12 max-w-lg">
                <h1 class="text-[86px] xl:text-[96px] font-extrabold text-white tracking-tight leading-[1.12]">
                    Let's Get<br>Started
                </h1>
            </div>

            <!-- Bottom Spacer -->
            <div class="relative z-10"></div>
        </div>

        <!-- RIGHT SECTION: Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between items-center px-6 sm:px-10 lg:px-14 xl:px-20 pt-8 sm:pt-10 lg:pt-16 pb-8 lg:pb-16 bg-white min-h-[calc(100vh-65px)] lg:min-h-screen">
            
            <!-- Top: Title & Subtitle (Tetap di atas, sejajar icon cap di kiri) -->
            <div class="w-full max-w-[420px]">
                <h2 class="text-3xl sm:text-6xl font-extrabold text-primary tracking-tight">
                    Login
                </h2>
                <p class="text-xl text-[#5A6E85] mt-2.5 leading-relaxed">
                    Selamat datang di Portal. Silakan lakukan autentikasi untuk mengakses akun Anda.
                </p>
            </div>

            <!-- Middle: Form Container (Ditengah / centered) -->
            <div class="w-full max-w-[420px] my-auto py-6">
                
                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-primary mb-1.5">
                            Email
                        </label>
                        <x-input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="example@gmail.com" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-primary mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <x-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="**********" class="pr-11" />
                            <button 
                                type="button" 
                                id="togglePasswordBtn"
                                aria-label="Toggle password visibility"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center hover:opacity-100 transition duration-150 cursor-pointer"
                            >
                                <!-- Eye Off (Closed/Slash) Icon -->
                                <img id="eyeClosedIcon" src="{{ asset('images/eye-off.png') }}" alt="Password Tersembunyi" class="w-5 h-5 object-contain opacity-60 hover:opacity-100 transition">
                                <!-- Eye Open Icon -->
                                <img id="eyeOpenIcon" src="{{ asset('images/eye-open.png') }}" alt="Password Terlihat" class="w-5 h-5 object-contain opacity-60 hover:opacity-100 transition hidden">
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <x-checkbox name="remember" id="remember" />
                            <span class="text-xs font-semibold text-primary">
                                Ingat Saya
                            </span>
                        </label>
                        <button 
                            type="button" 
                            id="openForgotModalBtn" 
                            class="text-xs font-semibold text-additional hover:underline cursor-pointer"
                        >
                            Lupa Password?
                        </button>
                    </div>

                    <!-- Submit Button (Yellow Button) -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full py-3.5 px-4 rounded-lg bg-secondary hover:brightness-95 active:scale-[0.99] text-primary font-bold text-sm tracking-wide transition-all duration-150 shadow-sm cursor-pointer text-center"
                        >
                            Login
                        </button>
                    </div>
                </form>

                <!-- Login Dengan Akun Lain & Google Login -->
                <div class="mt-8 text-center">
                    <p class="text-xs font-medium text-primary">
                        Login dengan akun lain
                    </p>
                    <div class="mt-3.5 flex justify-center">
                        <a 
                            href="{{ route('auth.google') }}" 
                            class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-gray-100 transition cursor-pointer"
                            title="Login dengan Google"
                        >
                            <svg class="w-6 h-6" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.65v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.14z"/>
                                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.15C3.29 21.4 7.37 24 12 24z"/>
                                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.26C.46 8.16 0 9.94 0 12s.46 3.84 1.26 5.42l4.02-3.15z"/>
                                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.37 0 3.29 2.6 1.26 6.58l4.02 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- Modal Lupa Password (Mengalami Masalah saat Login?) -->
    <div 
        id="forgotPasswordModal" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/45 backdrop-blur-[2px] transition-all duration-200 opacity-0 pointer-events-none"
        aria-hidden="true"
    >
        <div 
            id="forgotModalCard"
            class="relative w-full max-w-[480px] bg-[#EAEAEA] rounded-[32px] p-8 sm:p-10 text-center shadow-2xl transform scale-95 transition-all duration-200"
        >
            <!-- Shield Icon Attached by User -->
            <div class="flex justify-center mb-5">
                <img src="{{ asset('images/shield-icon.png') }}" alt="Security Shield" class="w-12 h-12 object-contain">
            </div>

            <!-- Modal Heading -->
            <h3 class="text-lg sm:text-xl font-bold text-primary tracking-tight mb-3">
                Mengalami Masalah saat Login?
            </h3>

            <!-- Modal Body Text -->
            <p class="text-xs sm:text-[13px] text-[#012650]/80 leading-relaxed max-w-sm mx-auto mb-7">
                Jangan khawatir! Jika Anda lupa kata sandi, tim Admin kami siap membantu. Silakan hubungi Dukungan IT atau kunjungi kantor administrasi untuk mengatur ulang kredensial Anda.
            </p>

            <!-- Action Button -->
            <div class="flex justify-center">
                <button 
                    type="button" 
                    id="closeForgotModalBtn"
                    class="py-3 px-12 rounded-lg bg-primary hover:bg-additional active:scale-[0.99] text-white font-semibold text-sm tracking-wide shadow-md transition-all duration-150 cursor-pointer"
                >
                    Mengerti
                </button>
            </div>
        </div>
    </div>
    <!-- Interactive Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Password Visibility Toggle
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const eyeClosedIcon = document.getElementById('eyeClosedIcon');
            const eyeOpenIcon = document.getElementById('eyeOpenIcon');

            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    
                    if (isPassword) {
                        eyeClosedIcon.classList.add('hidden');
                        eyeOpenIcon.classList.remove('hidden');
                    } else {
                        eyeClosedIcon.classList.remove('hidden');
                        eyeOpenIcon.classList.add('hidden');
                    }
                });
            }

            // Forgot Password Modal Logic
            const openModalBtn = document.getElementById('openForgotModalBtn');
            const closeModalBtn = document.getElementById('closeForgotModalBtn');
            const forgotModal = document.getElementById('forgotPasswordModal');
            const forgotModalCard = document.getElementById('forgotModalCard');

            function openModal() {
                if (!forgotModal) return;
                forgotModal.classList.remove('opacity-0', 'pointer-events-none');
                forgotModal.classList.add('opacity-100');
                if (forgotModalCard) {
                    forgotModalCard.classList.remove('scale-95');
                    forgotModalCard.classList.add('scale-100');
                }
            }

            function closeModal() {
                if (!forgotModal) return;
                forgotModal.classList.add('opacity-0', 'pointer-events-none');
                forgotModal.classList.remove('opacity-100');
                if (forgotModalCard) {
                    forgotModalCard.classList.add('scale-95');
                    forgotModalCard.classList.remove('scale-100');
                }
            }

            if (openModalBtn) {
                openModalBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    openModal();
                });
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeModal);
            }

            if (forgotModal) {
                forgotModal.addEventListener('click', function (e) {
                    if (e.target === forgotModal) {
                        closeModal();
                    }
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });
        });
    </script>
    <!-- Reusable Toast Notification Component -->
    <x-toast />
</body>
</html>

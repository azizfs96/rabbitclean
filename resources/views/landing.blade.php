<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>رابيت كلين - خدمة الغسيل بضغطة زر</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            position: relative;
        }
        
        /* Subtle background pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(0, 170, 161, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 212, 202, 0.03) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        /* Navbar styling */
        #navbar {
            position: fixed !important;
            top: 16px !important;
            left: 16px;
            right: 16px;
            z-index: 9999 !important;
            margin-left: auto;
            margin-right: auto;
            max-width: 1280px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Navbar scroll state */
        #navbar.scrolled {
            top: 8px !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Mobile adjustments */
        @media (max-width: 768px) {
            #navbar {
                top: 8px !important;
                left: 8px !important;
                right: 8px !important;
                border-radius: 1.5rem !important;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#f0fdfc] via-white to-[#f0f9ff] min-h-screen">
    <!-- Navigation -->
    <nav id="navbar" class="bg-white/90 backdrop-blur-lg shadow-md rounded-3xl transition-all duration-300">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="#home" class="flex items-center gap-3 group">
                    <div class="relative">
                        <div class="absolute inset-0 bg-[#00aaa1]/20 rounded-xl blur-lg group-hover:blur-xl transition-all"></div>
                        <img src="{{ asset('images/logo.png') }}" 
                             alt="رابيت كلين" 
                             class="relative h-12 w-auto object-contain transform group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <span class="text-2xl font-bold text-gray-900 group-hover:text-[#00aaa1] transition-colors duration-300">رابيت كلين</span>
                </a>
                
                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="#home" class="group relative px-4 py-2 text-gray-900 font-medium hover:text-[#00aaa1] transition-all duration-300">
                        <span class="relative z-10">الرئيسية</span>
                        <div class="absolute inset-0 bg-[#00aaa1]/10 rounded-lg scale-0 group-hover:scale-100 transition-transform duration-300"></div>
                    </a>
                    <a href="#features" class="group relative px-4 py-2 text-gray-600 hover:text-[#00aaa1] transition-all duration-300">
                        <span class="relative z-10">المميزات</span>
                        <div class="absolute inset-0 bg-[#00aaa1]/10 rounded-lg scale-0 group-hover:scale-100 transition-transform duration-300"></div>
                    </a>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex items-center gap-3">
                    <!-- Contact Button (Desktop) -->
                    <a href="tel:+966500000000" class="hidden lg:flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-[#00aaa1] transition-colors duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="font-medium">اتصل بنا</span>
                    </a>
                    
                    <!-- Login Button -->
                    <button class="hidden md:flex items-center gap-2 bg-gradient-to-r from-[#00aaa1] to-[#00d4ca] text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>تسجيل الدخول</span>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors duration-300">
                        <svg id="menu-icon" class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 text-gray-900 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden border-t border-gray-200 rounded-b-3xl overflow-hidden">
                <div class="py-4 space-y-2 px-2">
                    <a href="#home" class="block px-4 py-3 text-gray-900 font-medium hover:bg-[#00aaa1]/10 hover:text-[#00aaa1] rounded-lg transition-all duration-300">
                        الرئيسية
                    </a>
                    <a href="#features" class="block px-4 py-3 text-gray-600 hover:bg-[#00aaa1]/10 hover:text-[#00aaa1] rounded-lg transition-all duration-300">
                        المميزات
                    </a>
                    <a href="tel:+966500000000" class="flex items-center gap-2 px-4 py-3 text-gray-600 hover:bg-[#00aaa1]/10 hover:text-[#00aaa1] rounded-lg transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>اتصل بنا</span>
                    </a>
                    <button class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-[#00aaa1] to-[#00d4ca] text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl transition-all duration-300 mt-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>تسجيل الدخول</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- JavaScript for Mobile Menu and Scroll Effect -->
    <script>
        // Mobile Menu Toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });

        // Close mobile menu when clicking on a link
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            });
        });

        // Navbar scroll effect
        const navbar = document.getElementById('navbar');

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            if (currentScroll > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

    <!-- Hero Section -->
    <section id="home" class="relative pt-24 pb-12 md:pt-36 md:pb-20 bg-gradient-to-br from-[#e0f9f6] via-[#f0fdfc] to-white overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-20 left-10 w-72 h-72 bg-[#00aaa1] rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-[#00d4ca] rounded-full blur-3xl animate-pulse delay-1000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full h-full">
                <div class="absolute top-40 right-1/4 w-64 h-64 bg-[#00aaa1]/30 rounded-full blur-2xl"></div>
                <div class="absolute bottom-40 left-1/4 w-80 h-80 bg-[#00d4ca]/30 rounded-full blur-2xl"></div>
            </div>
        </div>
        
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12">
                <!-- Content Side -->
                <div class="flex-1 text-center lg:text-right space-y-6 lg:space-y-8">
                    <!-- Special Badge -->
                    <div class="inline-flex items-center gap-2 bg-gradient-to-r from-[#00aaa1] to-[#00d4ca] text-white px-6 py-3 rounded-full shadow-xl animate-bounce ring-4 ring-[#00aaa1]/10">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-bold text-sm">خصم 50% على أول طلبية!</span>
                    </div>

                    <!-- Main Heading with Animation -->
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-gray-900 leading-tight animate-fade-in-up">
                        الغسيل ما عاد<br>
                        <span class="text-[#00aaa1] relative inline-block">
                            يحتاج مشوار...
                            <svg class="absolute -bottom-2 left-0 w-full" height="12" viewBox="0 0 200 12" fill="none">
                                <path d="M2 10C45 2 155 2 198 10" stroke="#00aaa1" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </span><br>
                        صار بضغطة زر
                    </h1>
                    
                    <!-- Subheading -->
                    <p class="text-xl md:text-2xl text-gray-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed animate-fade-in-up animation-delay-200">
                        مع رابيت كلين، احصل على خدمة غسيل وكوي احترافية توصلك للباب في أقل من 24 ساعة
                    </p>
                </div>
                
                <!-- Image Side -->
                <div class="flex-1 relative animate-fade-in animation-delay-300">
                    <div class="relative mx-auto w-full max-w-md">
                        <!-- Floating Badge -->
                        <div class="absolute -top-6 -right-6 bg-gradient-to-br from-[#00aaa1] to-[#00d4ca] text-white px-6 py-3 rounded-full shadow-2xl z-20">
                            <p class="text-2xl font-bold">4h</p>
                            <p class="text-xs">توصيل سريع</p>
                        </div>

                         <!-- Image with Hover Effect -->
                         <div class="relative group">
                             <img src="{{ asset('images/hero.png') }}" 
                                  alt="Rabbit Clean App" 
                                  class="w-full h-auto transform group-hover:scale-105 transition-transform duration-500">
                             
                             <!-- Overlay on Hover -->
                             <div class="absolute inset-0 bg-gradient-to-t from-[#00aaa1]/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-end justify-center pb-8">
                                 <button class="bg-white text-[#00aaa1] px-6 py-3 rounded-full font-bold shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                                     شاهد العرض التوضيحي
                                 </button>
                             </div>
                         </div>
                        
                         <!-- Decorative Elements with Animation -->
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#00aaa1]/30 rounded-full blur-3xl opacity-50 animate-pulse"></div>
                        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-[#00d4ca]/30 rounded-full blur-3xl opacity-50 animate-pulse animation-delay-1000"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Custom CSS for Animations -->
    <style>
        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.8s ease-out forwards;
        }

        .animate-fade-in {
            animation: fade-in 1s ease-out forwards;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animation-delay-200 {
            animation-delay: 0.2s;
        }

        .animation-delay-400 {
            animation-delay: 0.4s;
        }

        .animation-delay-600 {
            animation-delay: 0.6s;
        }

        .animation-delay-800 {
            animation-delay: 0.8s;
        }

        .animation-delay-300 {
            animation-delay: 0.3s;
        }

        .animation-delay-1000 {
            animation-delay: 1s;
        }
    </style>

    <!-- Features Section -->
    <section id="features" class="py-12 md:py-20 bg-gradient-to-b from-white to-[#f0fdfc] relative overflow-hidden">
        <!-- Background Decorations -->
        <div class="absolute top-20 left-0 w-64 h-64 bg-[#00aaa1]/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-0 w-80 h-80 bg-[#00d4ca]/5 rounded-full blur-3xl"></div>
        
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Section Title -->
            <div class="text-center mb-10 md:mb-16">
                <!-- Main Title -->
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-6 leading-tight">
                    ليه 
                    <span class="relative inline-block">
                        <span class="relative z-10 text-[#00aaa1]">رابيت كلين</span>
                        <svg class="absolute -bottom-2 left-0 w-full" height="12" viewBox="0 0 200 12" fill="none">
                            <path d="M2 10C45 2 155 2 198 10" stroke="#00aaa1" stroke-width="3" stroke-linecap="round" opacity="0.5"/>
                        </svg>
                    </span>
                    الأفضل ؟
                </h2>
                
                <!-- Subtitle -->
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    نقدم لك خدمة استثنائية تجمع بين السرعة والجودة والسعر المناسب
                </p>
            </div>
            
            <!-- Features Grid -->
            <div class="grid md:grid-cols-3 gap-8 lg:gap-10 max-w-7xl mx-auto">
                <!-- Feature 1 -->
                <div class="group relative">
                    <div class="relative bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 border-2 border-transparent hover:border-[#00aaa1]/20">
                        <!-- Number Badge -->
                        <div class="absolute -top-4 -right-4 w-12 h-12 bg-gradient-to-br from-[#00aaa1] to-[#00d4ca] rounded-2xl flex items-center justify-center shadow-xl transform rotate-12 group-hover:rotate-0 transition-transform duration-500">
                            <span class="text-white font-bold text-xl">1</span>
                        </div>
                        
                        <!-- Icon Background -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[#00d4ca]/5 rounded-full blur-2xl transform translate-x-8 -translate-y-8 group-hover:scale-150 transition-transform duration-700"></div>
                        
                        <!-- Content -->
                        <div class="relative z-10">
                            <!-- Title -->
                            <h3 class="text-2xl md:text-3xl font-bold mb-5 text-gray-900 group-hover:text-[#00aaa1] transition-colors duration-300 leading-tight">
                                لأنك بتدفع أقل<br>وتوفر 30% 💰
                            </h3>
                            
                            <!-- Image -->
                            <div class="relative mb-6 overflow-hidden">
                                <img src="{{ asset('images/picture2.png') }}" 
                                     alt="لأنك بتدفع أقل" 
                                     class="w-full h-52 object-cover transform group-hover:scale-105 transition-transform duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#00aaa1]/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            </div>
                            
                            <!-- Description -->
                            <p class="text-gray-600 leading-relaxed text-base">
                                مع باقات الاشتراك عندنا، وفّر أكثر واستمتع بخدمة مميزة بأسعار لا تُقاوم
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="group relative md:mt-8">
                    <div class="relative bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 border-2 border-transparent hover:border-[#00aaa1]/20">
                        <!-- Number Badge -->
                        <div class="absolute -top-4 -right-4 w-12 h-12 bg-gradient-to-br from-[#00aaa1] to-[#00d4ca] rounded-2xl flex items-center justify-center shadow-xl transform rotate-12 group-hover:rotate-0 transition-transform duration-500">
                            <span class="text-white font-bold text-xl">2</span>
                        </div>
                        
                        <!-- Icon Background -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[#00aaa1]/5 rounded-full blur-2xl transform translate-x-8 -translate-y-8 group-hover:scale-150 transition-transform duration-700"></div>
                        
                        <!-- Content -->
                        <div class="relative z-10">
                            <!-- Title -->
                            <h3 class="text-2xl md:text-3xl font-bold mb-5 text-gray-900 group-hover:text-[#00aaa1] transition-colors duration-300 leading-tight">
                                لأننا الأسرع
                            </h3>
                            
                            <!-- Image -->
                            <div class="relative mb-6 overflow-hidden">
                                <img src="{{ asset('images/picture1.png') }}" 
                                     alt="لأننا الأسرع" 
                                     class="w-full h-52 object-cover transform group-hover:scale-105 transition-transform duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#00aaa1]/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            </div>
                            
                            <!-- Description -->
                            <p class="text-gray-600 leading-relaxed text-base">
                                نوصل ملابسك نظيفة ومكوية خلال 4 ساعات للغسيل المستعجل أو 12 ساعة للغسيل العادي .
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="group relative">
                    <div class="relative bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 border-2 border-transparent hover:border-[#00aaa1]/20">
                        <!-- Number Badge -->
                        <div class="absolute -top-4 -right-4 w-12 h-12 bg-gradient-to-br from-[#00aaa1] to-[#00d4ca] rounded-2xl flex items-center justify-center shadow-xl transform rotate-12 group-hover:rotate-0 transition-transform duration-500">
                            <span class="text-white font-bold text-xl">3</span>
                        </div>
                        
                        <!-- Icon Background -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[#00aaa1]/5 rounded-full blur-2xl transform translate-x-8 -translate-y-8 group-hover:scale-150 transition-transform duration-700"></div>
                        
                        <!-- Content -->
                        <div class="relative z-10">
                            <!-- Title -->
                            <h3 class="text-2xl md:text-3xl font-bold mb-5 text-gray-900 group-hover:text-[#00aaa1] transition-colors duration-300 leading-tight">
                                الجودة عندنا ما توقف<br>عند الغسيل
                            </h3>
                            
                            <!-- Image -->
                            <div class="relative mb-6 overflow-hidden">
                                <img src="{{ asset('images/picture3.png') }}" 
                                     alt="الجودة عندنا ما توقف عند الغسيل" 
                                     class="w-full h-52 object-cover transform group-hover:scale-105 transition-transform duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#00aaa1]/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            </div>
                            
                            <!-- Description -->
                            <p class="text-gray-600 leading-relaxed text-base">
                                نهتم بكل التفاصيل من الغسيل للكي والتعبئة، لتصلك ملابسك بأفضل حالة ممكنة
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-[#00aaa1] to-[#008a82]">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                جاهز لتجربة الفرق؟
            </h2>
            <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                حمّل التطبيق الآن واحصل على أول طلبية بخصم 50%
            </p>
            <button class="bg-white text-[#00aaa1] px-10 py-4 rounded-2xl font-bold text-lg hover:shadow-2xl transition">
                حمّل التطبيق الآن
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="رابيت كلين" 
                         class="h-12 w-auto object-contain opacity-90 hover:opacity-100 transition-opacity duration-300">
                    <span class="text-xl font-bold">رابيت كلين</span>
                </div>
                <p class="text-gray-400 mb-6">
                    الغسيل ما عاد يحتاج مشوار... صار بضغطة زر
                </p>
                <p class="text-gray-500 text-sm">
                    جميع الحقوق محفوظة © 2026 رابيت كلين
                </p>
            </div>
        </div>
    </footer>
</body>
</html>

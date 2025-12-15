@php
    $page = trim($__env->yieldContent('page', 'home'));
    $bodyClass = trim($__env->yieldContent('body_class'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>@yield('title', config('app.name'))</title>
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}" media="(prefers-color-scheme: light)">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}" media="(prefers-color-scheme: dark)">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="{{ asset('client/front-end/style.css') }}">
        <base href="{{ asset('client/front-end/') }}">
        <style>
            .text-red-600,
            .text-danger {
                color: #dc2626 !important;
            }
            .dark .text-red-600,
            .dark .text-danger {
                color: #f87171 !important;
            }
            .text-green-600,
            .text-success {
                color: #16a34a !important;
            }
            .dark .text-green-600,
            .dark .text-success {
                color: #4ade80 !important;
            }
            .mk,
            a.mk:link,
            a.mk:visited {
                color: #0077b6 !important;
            }
            .gh,
            .sh,
            .lk.gh,
            .vd.gh {
                background-color: #0077b6 !important;
            }
            .gh:hover,
            .sh:hover {
                background-color: #005f92 !important;
            }
            .wh {
                fill: #0077b6 !important;
            }
        </style>
        @stack('head')
    </head>
    <body
        class="{{ $bodyClass }}"
        data-page="{{ $page }}"
        x-data="landingTheme()"
        x-init="init()"
        :class="{ 'b eh': darkMode === true }"
    >
        @include('layouts.header')

        <div class="pt-28 lg:pt-36">
            @yield('content')
        </div>

        <footer>
            <div class="bb ze ki xn 2xl:ud-px-0">
                <div class="ji gp">
                    <div class="tc uf ap gg fp">
                        <div class="animate_top zd/2 to/4">
                            <a href="{{ url('/') }}">
                                <img src="{{ asset('images/logo/logo-dark.png') }}" alt="{{ config('app.name') }} Logo" class="om" style="height:50px;" loading="lazy" width="150" height="50"/>
                                <img src="{{ asset('images/logo/logo-white.png') }}" alt="{{ config('app.name') }} Logo" class="xc nm" style="height:50px;" loading="lazy" width="150" height="50"/>
                            </a>
                            <p class="lc fb">Adilisha is a non-profit organization that empowers girls in STEM education across Tanzania.</p>
                            <ul class="tc wf cg">
                                @php
                                    $social = \App\Models\Setting::getByGroup('social');
                                @endphp
                                
                                @if(!empty($social['facebook_url']))
                                <li>
                                    <a href="{{ $social['facebook_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                        <svg class="vh ul cl il" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_48_1499)">
                                                <path d="M14 13.5H16.5L17.5 9.5H14V7.5C14 6.47 14 5.5 16 5.5H17.5V2.14C17.174 2.097 15.943 2 14.643 2C11.928 2 10 3.657 10 6.7V9.5H7V13.5H10V22H14V13.5Z" fill="" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_48_1499">
                                                    <rect width="24" height="24" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </a>
                                </li>
                                @endif
                                
                                @if(!empty($social['x_url']))
                                <li>
                                    <a href="{{ $social['x_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="X (Twitter)">
                                        <svg class="vh ul cl il" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_48_1502)">
                                                <path d="M22.162 5.65593C21.3985 5.99362 20.589 6.2154 19.76 6.31393C20.6337 5.79136 21.2877 4.96894 21.6 3.99993C20.78 4.48793 19.881 4.82993 18.944 5.01493C18.3146 4.34151 17.4803 3.89489 16.5709 3.74451C15.6615 3.59413 14.7279 3.74842 13.9153 4.18338C13.1026 4.61834 12.4564 5.30961 12.0771 6.14972C11.6978 6.98983 11.6067 7.93171 11.818 8.82893C10.1551 8.74558 8.52832 8.31345 7.04328 7.56059C5.55823 6.80773 4.24812 5.75098 3.19799 4.45893C2.82628 5.09738 2.63095 5.82315 2.63199 6.56193C2.63199 8.01193 3.36999 9.29293 4.49199 10.0429C3.828 10.022 3.17862 9.84271 2.59799 9.51993V9.57193C2.59819 10.5376 2.93236 11.4735 3.54384 12.221C4.15532 12.9684 5.00647 13.4814 5.95299 13.6729C5.33661 13.84 4.6903 13.8646 4.06299 13.7449C4.32986 14.5762 4.85 15.3031 5.55058 15.824C6.25117 16.345 7.09712 16.6337 7.96999 16.6499C7.10247 17.3313 6.10917 17.8349 5.04687 18.1321C3.98458 18.4293 2.87412 18.5142 1.77899 18.3819C3.69069 19.6114 5.91609 20.2641 8.18899 20.2619C15.882 20.2619 20.089 13.8889 20.089 8.36193C20.089 8.18193 20.084 7.99993 20.076 7.82193C20.8949 7.2301 21.6016 6.49695 22.163 5.65693L22.162 5.65593Z" fill="" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_48_1502">
                                                    <rect width="24" height="24" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </a>
                                </li>
                                @endif
                                
                                @if(!empty($social['linkedin_url']))
                                <li>
                                    <a href="{{ $social['linkedin_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                        <svg class="vh ul cl il" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_48_1505)">
                                                <path d="M6.94 5.00002C6.93974 5.53046 6.72877 6.03906 6.35351 6.41394C5.97825 6.78883 5.46944 6.99929 4.939 6.99902C4.40857 6.99876 3.89997 6.78779 3.52508 6.41253C3.1502 6.03727 2.93974 5.52846 2.94 4.99802C2.94027 4.46759 3.15124 3.95899 3.5265 3.5841C3.90176 3.20922 4.41057 2.99876 4.941 2.99902C5.47144 2.99929 5.98004 3.21026 6.35492 3.58552C6.72981 3.96078 6.94027 4.46959 6.94 5.00002ZM7 8.48002H3V21H7V8.48002ZM13.32 8.48002H9.34V21H13.28V14.43C13.28 10.77 18.05 10.43 18.05 14.43V21H22V13.07C22 6.90002 14.94 7.13002 13.28 10.16L13.32 8.48002Z" fill="" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_48_1505">
                                                    <rect width="24" height="24" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </a>
                                </li>
                                @endif
                                
                                @if(!empty($social['instagram_url']))
                                <li>
                                    <a href="{{ $social['instagram_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                        <svg class="vh ul cl il" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" fill=""/>
                                        </svg>
                                    </a>
                                </li>
                                @endif
                                
                                @if(!empty($social['youtube_url']))
                                <li>
                                    <a href="{{ $social['youtube_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                                        <svg class="vh ul cl il" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill=""/>
                                        </svg>
                                    </a>
                                </li>
                                @endif
                                
                                @if(!empty($social['whatsapp_url']))
                                <li>
                                    <a href="{{ $social['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                                        <svg class="vh ul cl il" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" fill=""/>
                                        </svg>
                                    </a>
                                </li>
                                @endif
                                
                                @if(!empty($social['tiktok_url']))
                                <li>
                                    <a href="{{ $social['tiktok_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                                        <svg class="vh ul cl il" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z" fill=""/>
                                        </svg>
                                    </a>
                                </li>
                                @endif
                                
                                @if(!empty($social['telegram_url']))
                                <li>
                                    <a href="{{ $social['telegram_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
                                        <svg class="vh ul cl il" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168 1.001-.5 1.33-.82 1.363-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.246-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.24-1.865-.44-.752-.244-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" fill=""/>
                                        </svg>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                        <div class="vd ro tc sf rn un gg vn">
                            <div class="animate_top">
                                <h4 class="kk wm tj ec">Quick Links</h4>
                                <ul>
                                    <li><a href="{{ url('/') }}" class="sc xl vb">Home</a></li>
                                    <li><a href="#!" class="sc xl vb">Product</a></li>
                                    <li><a href="#!" class="sc xl vb">Careers</a></li>
                                    <li><a href="#!" class="sc xl vb">Pricing</a></li>
                                </ul>
                            </div>
                            <div class="animate_top">
                                <h4 class="kk wm tj ec">Services</h4>
                                <ul>
                                    <li><a href="#!" class="sc xl vb">Web Development</a></li>
                                    <li><a href="#!" class="sc xl vb">Graphics Design</a></li>
                                    <li><a href="#!" class="sc xl vb">Digital Marketing</a></li>
                                    <li><a href="#!" class="sc xl vb">Ui/Ux Design</a></li>
                                </ul>
                            </div>
                            <div class="animate_top">
                                <h4 class="kk wm tj ec">Support</h4>
                                <ul>
                                    <li><a href="#!" class="sc xl vb">Company</a></li>
                                    <li><a href="#!" class="sc xl vb">Press media</a></li>
                                    <li><a href="#!" class="sc xl vb">Our Blog</a></li>
                                    <li><a href="#!" class="sc xl vb">Contact Us</a></li>
                                </ul>
                            </div>
                            <div class="animate_top">
                                <h4 class="kk wm tj ec">Newsletter</h4>
                                <p class="ac qe">Subscribe to receive future updates</p>
                                <form action="#!" method="post">
                                    <div class="i">
                                        <input type="email" placeholder="Email address" class="vd sm _g ch pm vk xm rg gm dm dn gi mi" />
                                        <button class="h q fi">
                                            <svg class="th vm ul" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_48_1487)">
                                                    <path d="M3.1175 1.17318L18.5025 9.63484C18.5678 9.67081 18.6223 9.72365 18.6602 9.78786C18.6982 9.85206 18.7182 9.92527 18.7182 9.99984C18.7182 10.0744 18.6982 10.1476 18.6602 10.2118C18.6223 10.276 18.5678 10.3289 18.5025 10.3648L3.1175 18.8265C3.05406 18.8614 2.98262 18.8792 2.91023 18.8781C2.83783 18.8769 2.76698 18.857 2.70465 18.8201C2.64232 18.7833 2.59066 18.7308 2.55478 18.6679C2.51889 18.6051 2.50001 18.5339 2.5 18.4615V1.53818C2.50001 1.46577 2.51889 1.39462 2.55478 1.33174C2.59066 1.26885 2.64232 1.2164 2.70465 1.17956C2.76698 1.14272 2.83783 1.12275 2.91023 1.12163C2.98262 1.12051 3.05406 1.13828 3.1175 1.17318ZM4.16667 10.8332V16.3473L15.7083 9.99984L4.16667 3.65234V9.16651H8.33333V10.8332H4.16667Z" fill="" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_48_1487">
                                                        <rect width="20" height="20" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bh ch pm tc uf sf yo wf xf ap cg fp bj">
                    <div class="animate_top">
                        <ul class="tc wf gg">
                            <li><a href="#!" class="xl">English</a></li>
                            <li><a href="#!" class="xl">Privacy Policy</a></li>
                            <li><a href="#!" class="xl">Support</a></li>
                            @php
                                $appVersion = \App\Models\Setting::get('app_version', '1.0.0');
                            @endphp
                            <li class="list-inline-item"><a href="#!" class="xl">V{{ $appVersion }}</a></li>
                        </ul>
                    </div>
                    <div class="animate_top">
                        @php
                            $siteName = \App\Models\Setting::get('site_name', config('app.name'));
                        @endphp
                        <p>
                            &copy; {{ now()->year }} {{ $siteName }}.
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <button
            class="xc wf xf ie ld vg sr gh tr g sa ta _a"
            @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            @scroll.window="scrollTop = window.pageYOffset > 50"
            :class="{ 'uc' : scrollTop }"
        >
            <svg class="uh se qd" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z" />
            </svg>
        </button>

        <script>
            function landingTheme() {
                return {
                    page: document.body.getAttribute('data-page') || 'home',
                    darkMode: JSON.parse(window.localStorage.getItem('darkMode') ?? 'false'),
                    stickyMenu: false,
                    navigationOpen: false,
                    scrollTop: false,
                    init() {
                        // Initialize darkMode from localStorage if not already set
                        const stored = window.localStorage.getItem('darkMode');
                        if (stored !== null) {
                            this.darkMode = JSON.parse(stored);
                        }
                        // Watch for darkMode changes
                        this.$watch('darkMode', value => {
                            window.localStorage.setItem('darkMode', JSON.stringify(value));
                        });

                        // Ensure scroll handler works even if other libraries interfere
                        // Use requestAnimationFrame for smooth updates and to avoid conflicts with ScrollReveal
                        let ticking = false;
                        const updateStickyMenu = () => {
                            if (!ticking) {
                                window.requestAnimationFrame(() => {
                                    const scrollY = window.pageYOffset || window.scrollY || document.documentElement.scrollTop;
                                    this.stickyMenu = (scrollY > 20) ? true : false;
                                    ticking = false;
                                });
                                ticking = true;
                            }
                        };

                        // Set initial state
                        const scrollY = window.pageYOffset || window.scrollY || document.documentElement.scrollTop;
                        this.stickyMenu = (scrollY > 20) ? true : false;

                        // Add scroll listener with high priority (capture phase)
                        // This ensures it runs before other scroll handlers that might stop propagation
                        window.addEventListener('scroll', updateStickyMenu, { passive: true, capture: true });
                    },
                };
            }
        </script>
        <script defer src="{{ asset('client/front-end/bundle.js') }}"></script>
        @stack('scripts')
    </body>
</html>


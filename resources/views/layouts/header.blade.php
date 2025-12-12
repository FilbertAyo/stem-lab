<header
    class="g s r vd ya cj"
    :class="{ 'hh sm _k dj bl ll' : stickyMenu }"
    @scroll.window="stickyMenu = (window.pageYOffset > 20) ? true : false"
>
    <div class="bb ze ki xn 2xl:ud-px-0 oo wf yf i">
        <div class="vd to/4 tc wf yf">
            <a href="{{ url('/') }}">
                <img class="om" src="{{ asset('images/logo/logo-dark.png') }}" alt="{{ config('app.name') }} Logo" style="height:50px;" loading="eager" fetchpriority="high" width="150" height="50"/>
                <img class="xc nm" src="{{ asset('images/logo/logo-white.png') }}" alt="{{ config('app.name') }} Logo" style="height:50px;" loading="eager" fetchpriority="high" width="150" height="50"/>
            </a>
            <button class="po rc" @click="navigationOpen = !navigationOpen">
                <span class="rc i pf re pd">
                    <span class="du-block h q vd yc">
                        <span class="rc i r s eh um tg te rd eb ml jl dl" :class="{ 'ue el': !navigationOpen }"></span>
                        <span class="rc i r s eh um tg te rd eb ml jl fl" :class="{ 'ue qr': !navigationOpen }"></span>
                        <span class="rc i r s eh um tg te rd eb ml jl gl" :class="{ 'ue hl': !navigationOpen }"></span>
                    </span>
                    <span class="du-block h q vd yc lf">
                        <span class="rc eh um tg ml jl el h na r ve yc" :class="{ 'sd dl': !navigationOpen }"></span>
                        <span class="rc eh um tg ml jl qr h s pa vd rd" :class="{ 'sd rr': !navigationOpen }"></span>
                    </span>
                </span>
            </button>
        </div>
        <div
            class="vd wo/4 sd qo f ho oo wf yf"
            :class="{ 'd hh rm sr td ud qg ug jc yh': navigationOpen }"
        >
            <nav>
                <ul class="tc _o sf yo cg ep">
                    <li>
                        <a href="{{ url('/') }}" class="xl" :class="{ 'mk': page === 'home' }">Home</a>
                    </li>
                    <li>
                        <a href="{{ route('competitions.public') }}" class="xl">
                        Competitions
                    </a>
                    </li>
                    <li>
                        <a href="{{ url('/#about') }}" class="xl">About</a>
                    </li>
                    <li>
                        <a href="{{ url('/#subjects') }}" class="xl">Subjects</a>
                    </li>
                    <li>
                        <a href="{{ url('/#support') }}" class="xl">Support</a>
                    </li>
                </ul>
            </nav>
            <div class="tc wf ig pb no gap-2">
                <div class="pc h io pa ra" :class="navigationOpen ? '!-ud-visible' : 'd'">
                    <label class="rc ab i">
                        <input
                            type="checkbox"
                            :value="darkMode"
                            @change="darkMode = !darkMode"
                            class="pf vd yc uk h r za ab"
                        />
                        <!-- Sun Icon (shown in dark mode to switch to light) -->
                        <svg
                            :class="{ 'wn' : page === 'home', 'xh' : page === 'home' && stickyMenu }"
                            class="xc sk nm"
                            width="25"
                            height="25"
                            viewBox="0 0 25 25"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M12.0908 18.6363C10.3549 18.6363 8.69 17.9467 7.46249 16.7192C6.23497 15.4916 5.54537 13.8268 5.54537 12.0908C5.54537 10.3549 6.23497 8.69 7.46249 7.46249C8.69 6.23497 10.3549 5.54537 12.0908 5.54537C13.8268 5.54537 15.4916 6.23497 16.7192 7.46249C17.9467 8.69 18.6363 10.3549 18.6363 12.0908C18.6363 13.8268 17.9467 15.4916 16.7192 16.7192C15.4916 17.9467 13.8268 18.6363 12.0908 18.6363ZM12.0908 16.4545C13.2481 16.4545 14.358 15.9947 15.1764 15.1764C15.9947 14.358 16.4545 13.2481 16.4545 12.0908C16.4545 10.9335 15.9947 9.8236 15.1764 9.00526C14.358 8.18692 13.2481 7.72718 12.0908 7.72718C10.9335 7.72718 9.8236 8.18692 9.00526 9.00526C8.18692 9.8236 7.72718 10.9335 7.72718 12.0908C7.72718 13.2481 8.18692 14.358 9.00526 15.1764C9.8236 15.9947 10.9335 16.4545 12.0908 16.4545ZM10.9999 0.0908203H13.1817V3.36355H10.9999V0.0908203ZM10.9999 20.8181H13.1817V24.0908H10.9999V20.8181ZM2.83446 4.377L4.377 2.83446L6.69082 5.14828L5.14828 6.69082L2.83446 4.37809V4.377ZM17.4908 19.0334L19.0334 17.4908L21.3472 19.8046L19.8046 21.3472L17.4908 19.0334ZM19.8046 2.83337L21.3472 4.377L19.0334 6.69082L17.4908 5.14828L19.8046 2.83446V2.83337ZM5.14828 17.4908L6.69082 19.0334L4.377 21.3472L2.83446 19.8046L5.14828 17.4908ZM24.0908 10.9999V13.1817H20.8181V10.9999H24.0908ZM3.36355 10.9999V13.1817H0.0908203V10.9999H3.36355Z"
                                fill=""
                            />
                        </svg>
                        <!-- Moon Icon (shown in light mode to switch to dark) -->
                        <svg
                            :class="{ 'wn' : page === 'home', 'xh' : page === 'home' && stickyMenu }"
                            class="th wl om"
                            width="25"
                            height="25"
                            viewBox="0 0 25 25"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M21.6364 12.8182C21.6364 13.1818 21.5455 13.5455 21.3636 13.8182C21.1818 14.1818 20.9091 14.4545 20.5455 14.6364C20.1818 14.8182 19.8182 14.9091 19.4545 14.9091C19.0909 14.9091 18.7273 14.8182 18.3636 14.6364C18 14.4545 17.7273 14.1818 17.5455 13.8182C17.3636 13.5455 17.2727 13.1818 17.2727 12.8182C17.2727 12.4545 17.3636 12.0909 17.5455 11.8182C17.7273 11.4545 18 11.1818 18.3636 11C18.7273 10.8182 19.0909 10.7273 19.4545 10.7273C19.8182 10.7273 20.1818 10.8182 20.5455 11C20.9091 11.1818 21.1818 11.4545 21.3636 11.8182C21.5455 12.0909 21.6364 12.4545 21.6364 12.8182ZM16.3636 12.8182C16.3636 15.3636 14.5455 17.4545 12 18.1818C9.45455 18.9091 6.81818 17.7273 5.18182 15.6364C3.54545 13.5455 3 10.9091 3.72727 8.36364C4.45455 5.81818 6.54545 4 9.09091 3.27273C10.0909 3 11.1818 2.90909 12.1818 3.09091C12.7273 3.18182 12.9091 3.81818 12.4545 4.18182C11.7273 4.72727 11.1818 5.54545 11 6.36364C10.7273 7.45455 10.8182 8.63636 11.2727 9.63636C11.7273 10.6364 12.4545 11.3636 13.4545 11.8182C14.4545 12.2727 15.6364 12.3636 16.7273 12.0909C17.1818 12 17.8182 12.1818 17.9091 12.7273C18 12.8182 18 12.8182 17.9091 13.3636C17.7273 14.1818 17.1818 14.8182 16.3636 15.2727C16.3636 14.4545 16.2727 13.6364 16.3636 12.8182Z"
                                fill=""
                            />
                        </svg>
                   </label>
                </div>
                <button
                    type="button"
                    @click="window.location.reload()"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-[#19140035] dark:border-[#3E3E3A] text-[#1b1b18] dark:text-[#EDEDEC] hover:border-black dark:hover:border-white transition"
                    aria-label="Reload page"
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M21 12a9 9 0 1 1-2.64-6.36V3M21 3h-4"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
                <a
                    href="{{ route('login') }}"
                    :class="{ 'nk yl' : page === 'home', 'ok' : page === 'home' && stickyMenu }"
                    class="ek pk xl"
                >
                    Sign In
                </a>
                <a
                    href="{{ route('register') }}"
                    :class="{ 'hh/[0.15]' : page === 'home', 'sh' : page === 'home' && stickyMenu }"
                    class="lk gh dk rg tc wf xf _l gi hi"
                >
                    Sign Up
                </a>
            </div>
        </div>
    </div>
</header>


<div class="nk-header nk-header-fixed is-light">
    <div class="container-fluid">
        <div class="nk-header-wrap">
            <div class="nk-menu-trigger d-xl-none ms-n1">
                <a href="#" class="nk-nav-toggle nk-quick-nav-icon" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
            </div>
            <div class="nk-header-brand d-xl-none">
                <a href="{{ route('home') }}" class="logo-link">
                    <img class="logo-light logo-img" style="filter: brightness(0) invert(1);" src="/venox_logo_white.png" srcset="/venox_logo_white.png 2x" alt="logo">
                    <img class="logo-dark logo-img" src="/venox_logo_white.png" srcset="/venox_logo_white.png 2x" alt="logo-dark">
                </a>
            </div><!-- .nk-header-brand -->
            @php($globalUsdRate = App\Models\Currency::usdRate())
            <div class="nk-header-news d-none d-md-block">
                <div class="nk-news-list">
                    @role('admin')
                    <form method="POST" action="{{ route('global_usd_rate.update') }}" class="d-flex align-items-center" style="gap: 7px;">
                        @csrf
                        <span class="badge bg-light text-dark border" style="font-size: 12px; white-space: nowrap;">Asosiy valyuta: UZS</span>
                        <label for="global-usd-rate" class="mb-0" style="font-size: 12px; color: #526484; white-space: nowrap; font-weight: 600;">1 USD =</label>
                        <input id="global-usd-rate" type="number" name="usd_rate" value="{{ $globalUsdRate }}" min="1" max="1000000" step="0.01" required class="form-control form-control-sm" style="width: 112px; text-align: right; font-weight: 700;" aria-label="Umumiy USD kursi">
                        <span style="font-size: 12px; color: #526484; font-weight: 600;">UZS</span>
                        <button type="submit" class="btn btn-sm btn-primary" title="Butun tizim uchun kursni saqlash">Saqlash</button>
                    </form>
                    @else
                    <div class="d-flex align-items-center" style="gap: 7px; font-size: 12px; color: #526484;">
                        <span class="badge bg-light text-dark border">Asosiy valyuta: UZS</span>
                        <strong>1 USD = {{ number_format($globalUsdRate, 2, '.', ' ') }} UZS</strong>
                    </div>
                    @endrole
                </div>
            </div>

            <div class="nk-header-tools">
                <ul class="nk-quick-nav">
                    <li class="dropdown language-dropdown d-none d-sm-block me-n1">
                        <a href="#" class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
                            <div class="quick-icon border border-light">
                                <img class="icon" src="/backend/images/flags/{{ LaravelLocalization::getCurrentLocaleNative() }}.png" alt="">
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-s1">
                            <ul class="language-list">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                <li>
                                    <a href="{{ LaravelLocalization::getLocalizedURL($localeCode) }}" class="language-item">
                                        <img src="/backend/images/flags/{{ $properties['native'] }}.png" alt="" class="language-flag">
                                        <span class="language-name">{{ $properties['name'] }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                                    
                    <li class="dropdown notification-dropdown d-none ">
                        <a href="#" class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
                            <div class="icon-status icon-status-info"><em class="icon ni ni-bell"></em></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end dropdown-menu-s1">
                            <div class="dropdown-head">
                                <span class="sub-title nk-dropdown-title">{{ trans('backend.main.notification') }}</span><!--
                                <a href="#">Mark All as Read</a>-->
                            </div>
                            <div class="dropdown-body">
                                <div class="nk-notification">
                                    
                                </div><!-- .nk-notification -->
                            </div><!-- .nk-dropdown-body -->
                            <!--<div class="dropdown-foot center">
                                <a href="#">View All</a>
                            </div>-->
                        </div>
                    </li>
                    <li class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                            <div class="user-toggle">
                                <div class="user-avatar sm">
                                    <em class="icon ni ni-user-alt"></em>
                                </div>
                                <div class="user-info d-none d-md-block">
                                    <div class="user-status">{{ trans('backend.main.login') }}: {{ Auth()->user()->username }}</div>
                                    <div class="user-name dropdown-indicator">{{ Auth()->user()->name }}</div>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end dropdown-menu-s1">
                            <div class="dropdown-inner user-card-wrap bg-lighter d-none d-md-block">
                                <div class="user-card">
                                    <div class="user-avatar">
                                        <span>{{ Str::substr(Auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                    <div class="user-info">
                                        <span class="lead-text">{{ Auth()->user()->name }}</span>
                                        <span class="sub-text">{{ trans('backend.main.login') }}: {{ Auth()->user()->username }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    <li>
                                        <a href="{{ route('myprofile_form') }}">
                                            <em class="icon ni ni-user-alt"></em><span>{{ trans('backend.main.my_profile') }}</span>
                                        </a>
                                    </li>
                                    
                                    <li>
                                        <a href="{{ route('mypassword_form') }}">
                                            <em class="icon ni ni-setting-alt"></em><span>{{ trans('backend.main.change_password') }}</span>
                                        </a>
                                    </li>
                                    
                                    <li>
                                        @if(Auth()->user()->dark_mode == 1)
                                         <a href="{{ route('theme_user',['id' => Auth()->user()->code ]) }}">
                                            <em class="icon ni ni-sun"></em><span> {{ trans('backend.main.dark_theme') }}</span>
                                        </a>
                                        @else
                                        <a href="{{ route('theme_user',['id' => Auth()->user()->code ]) }}">
                                            <em class="icon ni ni-moon"></em><span> {{ trans('backend.main.white_theme') }}</span>
                                        </a>
                                        @endif
                                       
                                    </li>
                                    
                                </ul>
                            </div>
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"><em class="icon ni ni-signout"></em><span>{{ trans('backend.main.signout') }}</span></a>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li><!-- .dropdown -->
                </ul><!-- .nk-quick-nav -->
            </div><!-- .nk-header-tools -->
        </div><!-- .nk-header-wrap -->
    </div><!-- .container-fliud -->
</div>

<nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="container-fluid" style="min-height:0">

        @php
            $websetting = App\Models\WebSetting::first();
        @endphp
        <!-- Brand -->
        <a class="navbar-brand " href="{{ route('root') }}">
            <img src="{{ $websetting->websiteLogoPath ?? asset('web/logo.png') }}" class="navbar-brand-img"
                alt="Admin Logo">
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerGoldStar"
            aria-controls="navbarTogglerGoldStar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="navbarTogglerGoldStar">
            {{-- Main Admin --}}
            <ul class="navbar-nav">
                <div class="position-absolute top-0 {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} d-md-none navbarCloseBtn" data-toggle="collapse"
                    data-target="#navbarTogglerGoldStar">
                    <i class="fas {{ app()->getLocale() == 'ar' ? 'fa-angle-right' : 'fa-angle-left' }}"></i>
                </div>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('root') ? 'active' : '' }}" href="{{ route('root') }}">
                        <i class="fa fa-desktop text-teal"></i>
                        <span class="nav-link-text">{{ __('Dashboard') }}</span>
                    </a>
                </li>

                @can('order.index')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('order.*') ? 'active' : '' }}"
                            href="{{ route('order.index') }}">
                            <i class="fa fa-shopping-cart text-orange"></i>
                            <span class="nav-link-text">{{ __('Orders') }}</span>
                        </a>
                    </li>
                @endcan

                @canany(['product.index', 'coupon.index', 'variant.index', 'service.index'])
                    <li class="nav-item">
                        <a class="nav-link  {{ request()->routeIs('service.*', 'variant.*', 'product.*', 'coupon.*') ? 'active' : '' }}"
                            href="#product_manage" data-toggle="collapse" aria-expanded="false" role="button"
                            aria-controls="navbar-examples">
                            <i class="fas fa-th-large text-primary"></i>
                            <span class="nav-link-text">{{ __('Product_Manage') }}</span>
                        </a>

                        <div class="collapse {{ request()->routeIs('service.*', 'variant.*', 'product.*', 'coupon.*') ? 'show' : '' }}"
                            id="product_manage">
                            <ul class="nav nav-sm flex-column">
                                @can('service.index')
                                    <a class="nav-link sub-menu {{ request()->routeIs('service.*') ? 'active' : '' }}"
                                        href="{{ route('service.index') }}" href="{{ route('service.index') }}">
                                        {{-- <i class="fas fa-cogs"></i> --}}
                                        <i class="fas fa-tools"></i>
                                        <span class="nav-link-text">{{ __('Services') }}</span>
                                    </a>
                                @endcan

                                @can('variant.index')
                                    <a class="nav-link sub-menu {{ request()->routeIs('variant.*') ? 'active' : '' }}"
                                        href="{{ route('variant.index') }}">
                                        <i class="fas fa-list"></i>
                                        <span class="nav-link-text">{{ __('Variants') }}</span>
                                    </a>
                                @endcan
                                @can('product.index')
                                    <a class="nav-link sub-menu {{ request()->routeIs('product.*') ? 'active' : '' }}"
                                        href="{{ route('product.index') }}">
                                        <i class="fas fa-tshirt"></i>
                                        <span class="nav-link-text">{{ __('Products') }}</span>
                                    </a>
                                @endcan

                                @can('coupon.index')
                                    <a class="nav-link sub-menu {{ request()->routeIs('coupon.*') ? 'active' : '' }}"
                                        href="{{ route('coupon.index') }}">
                                        <i class="fa fa-percentage"></i>
                                        <span class="nav-link-text">{{ __('Coupon') }}</span>
                                    </a>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <li class="nav-item">
                    <a class="nav-link  {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="#posManage"
                        data-toggle="collapse" aria-expanded="false" role="button" aria-controls="navbar-examples">
                        <i class="fas fa-store-alt text-success"></i>
                        <span class="nav-link-text">{{ __('Pos_Manage') }}</span>
                    </a>

                    <div class="collapse {{ request()->routeIs('pos.*') ? 'show' : '' }}" id="posManage">
                        <ul class="nav nav-sm flex-column">
                            <a class="nav-link sub-menu {{ request()->routeIs('pos.index') ? 'active' : '' }}"
                                href="{{ route('pos.index') }}" href="{{ route('pos.index') }}">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="nav-link-text">{{ __('POS') }}</span>
                            </a>

                            <a class="nav-link sub-menu {{ request()->routeIs('pos.sales', 'pos.order.show') ? 'active' : '' }}"
                                href="{{ route('pos.sales') }}">
                                <i class="fas fa-list"></i>
                                <span class="nav-link-text">{{ __('Pos_Sales') }}</span>
                            </a>
                        </ul>
                    </div>
                </li>

                @can('notification.index')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('notification.*') ? 'active' : '' }}"
                            href="{{ route('notification.index') }}">
                            <i class="fas fa-bell text-primary"></i>
                            <span class="nav-link-text">{{ __('Notifications') }}</span>
                        </a>
                    </li>
                @endcan

                @can('revenue.index')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('revenue.*') ? 'active' : '' }}"
                            href="{{ route('revenue.index', ['from' => now()->subMonth(1)->format('Y-m-d'), 'to' => now()->addDay(1)->format('Y-m-d')]) }}">
                            <i class="fa fa-file text-red"></i>
                            <span class="nav-link-text">{{ __('Reports') }}</span>
                        </a>
                    </li>
                @endcan

                @can('banner.promotional')
                    <li class="nav-item">
                        <a class="nav-link  {{ request()->routeIs('banner.promotional') ? 'active' : '' }}"
                            href="{{ route('banner.promotional') }}">
                            <i class="fas fa-image text-dark"></i>
                            <span class="nav-link-text">{{ __('App_Banners') }}</span>
                        </a>
                    </li>
                @endcan

                @can('customer.index')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.*') ? 'active' : '' }}"
                            href="{{ route('customer.index') }}">
                            <i class="fa fa-users text-red"></i>
                            <span class="nav-link-text">{{ __('Customer') }}</span>
                        </a>
                    </li>
                @endcan

                {{-- Subscription Management --}}
                <li class="nav-item">
                    <a class="nav-link  {{ request()->routeIs('subscription.*', 'customer-subscription.*') ? 'active' : '' }}"
                        href="#subscriptionManage" data-toggle="collapse" aria-expanded="false" role="button"
                        aria-controls="navbar-examples">
                        <i class="fas fa-credit-card text-success"></i>
                        <span class="nav-link-text">{{ __('Subscriptions') }}</span>
                    </a>

                    <div class="collapse {{ request()->routeIs('subscription.*', 'customer-subscription.*') ? 'show' : '' }}"
                        id="subscriptionManage">
                        <ul class="nav nav-sm flex-column">
                            <a class="nav-link sub-menu {{ request()->routeIs('subscription.*') ? 'active' : '' }}"
                                href="{{ route('subscription.index') }}">
                                <i class="fas fa-list"></i>
                                <span class="nav-link-text">{{ __('Plans') }}</span>
                            </a>

                            <a class="nav-link sub-menu {{ request()->routeIs('customer-subscription.*') ? 'active' : '' }}"
                                href="{{ route('customer-subscription.index') }}">
                                <i class="fas fa-users"></i>
                                <span class="nav-link-text">{{ __('Customer_Subscriptions') }}</span>
                            </a>
                        </ul>
                    </div>
                </li>

                @can('contact')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}"
                            href="{{ route('contact') }}">
                            <i class="fa fa-comment text-purple"></i>
                            <span class="nav-link-text">{{ __('Contacts') }}</span>
                        </a>
                    </li>
                @endcan

                @role('root|admin|visitor')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                            href="{{ route('admin.index') }}">
                            <i class="fas fa-user-secret"></i>
                            <span class="nav-link-text">{{ __('Admins') }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link  {{ request()->routeIs('setting.*', 'mobileApp', 'socialLink.*', 'webSetting.*', 'otpSetting.*', 'notification.manage', 'fcm.*', 'mail-config.*') ? 'active' : '' }}"
                            href="#setting" data-toggle="collapse" aria-expanded="false" role="button"
                            aria-controls="navbar-examples">
                            <i class="fa fa-cog text-red"></i>
                            <span class="nav-link-text">{{ __('Settings') }}</span>
                        </a>

                        <div class="collapse {{ request()->routeIs('setting.*', 'mobileApp', 'socialLink.*', 'profile.*', 'webSetting.*', 'otpSetting.*', 'schedule.*', 'invoiceManage.*', 'areas.*', 'sms-gateway.*', 'notification.manage', 'fcm.*', 'mail-config.*') ? 'show' : '' }}"
                            id="setting">
                            <ul class="nav nav-sm flex-column">
                                @foreach (config('enums.settings') as $index => $item)
                                    <a class="nav-link sub-menu {{ url()->full() == config('app.url') . '/settings/' . $index || url()->full() == config('app.url') . '/settings/' . $index . '/edit' ? 'active' : '' }}"
                                        href="{{ route('setting.show', $index) }}">

                                        @if ($index == 'privacy-policy')

                                            <i class="fas fa-vote-yea"></i>
                                        @endif
                                        @if ($index == 'trams-of-service')
                                            <i class="fas fa-toilet-paper"></i>
                                        @endif
                                        @if ($index == 'contact-us')
                                            <i class="fas fa-envelope-open-text"></i>
                                        @endif
                                        @if ($index == 'about-us')
                                            <i class="fas fa-info-circle"></i>
                                        @endif
                                        <span class="nav-link-text">{{ __( $item ) }}</span>

                                    </a>
                                @endforeach
                                <a class="nav-link sub-menu {{ request()->routeIs('mobileApp') ? 'active' : '' }}"
                                    href="{{ route('mobileApp') }}">
                                    <i class="fa fa-link"></i>
                                    <span class="nav-link-text">{{ __('Mobile_App_Link') }}</span>
                                </a>
                                <a class="nav-link sub-menu {{ request()->routeIs('socialLink.*') ? 'active' : '' }}"
                                    href="{{ route('socialLink.index') }}">
                                    <i class="fa fa-icons"></i>
                                    <span class="nav-link-text">{{ __('Social_Links') }}</span>
                                </a>
                                <a class="nav-link sub-menu {{ url()->full() == config('app.url') . '/pickup/scheduls' ? 'active' : '' }}"
                                    href="{{ route('schedule.index', 'pickup') }}">
                                    <i class="fas fa-clock"></i>
                                    <span class="nav-link-text">{{ __('Pickup_Schedules') }}</span>
                                </a>
                                <a class="nav-link sub-menu {{ url()->full() == config('app.url') . '/delivery/scheduls' ? 'active' : '' }}"
                                    href="{{ route('schedule.index', 'delivery') }}">
                                    <i class="fas fa-clock"></i>
                                    <span class="nav-link-text">{{ __('Delivery_Schedules') }}</span>
                                </a>

                                <a class="nav-link sub-menu {{ request()->routeIs('webSetting.*') ? 'active' : '' }}"
                                    href="{{ route('webSetting.index') }}">
                                    <i class="fas fa-globe"></i>
                                    <span class="nav-link-text">{{ __('Web_Setting') }}</span>
                                </a>

                                <a class="nav-link sub-menu {{ request()->routeIs('otpSetting.*') ? 'active' : '' }}"
                                    href="{{ route('otpSetting.index') }}">
                                    <i class="fas fa-shield-alt"></i>
                                    <span class="nav-link-text">{{ __('OTP_Settings') }}</span>
                                </a>

                                {{-- @can('payment-gateway.index') --}}
                                    <a href="{{ route('payment-gateway.index') }}"
                                        class="nav-link sub-menu  {{ request()->routeIs('payment-gateway.index') ? 'active' : '' }}">
                                        <i class="fas fa-globe"></i>
                                        <span class="nav-link-text">{{ __('Payment_gateway') }}</span>
                                        </a>
                                {{-- @endcan --}}
                                <a class="nav-link sub-menu {{ request()->routeIs('sms-gateway.*') ? 'active' : '' }}"
                                    href="{{ route('sms-gateway.index') }}">
                                    <i class="fas fa-sms"></i>
                                    <span class="nav-link-text">{{ __('Msegat_SMS_Gateway') }}</span>
                                </a>
                                <a class="nav-link sub-menu {{ request()->routeIs('invoiceManage.*') ? 'active' : '' }}"
                                    href="{{ route('invoiceManage.index') }}">
                                    <i class="fas fa-print"></i>
                                    <span class="nav-link-text">{{ __('Invoice_Manage') }}</span>
                                </a>

                                <a class="nav-link sub-menu {{ request()->routeIs('notification.manage') ? 'active' : '' }}"
                                    href="{{ route('notification.manage') }}">
                                    <i class="fas fa-bell"></i>
                                    <span class="nav-link-text">{{ __('Notify_Manage') }}</span>
                                </a>

                                <a class="nav-link sub-menu {{ request()->routeIs('fcm.*') ? 'active' : '' }}"
                                    href="{{ route('fcm.index') }}">
                                    <i class="fas fa-cloud"></i>
                                    <span class="nav-link-text">{{ __('FCM_Config') }}</span>
                                </a>

                                <a class="nav-link sub-menu {{ request()->routeIs('mail-config.*') ? 'active' : '' }}"
                                    href="{{ route('mail-config.index') }}">
                                    <i class="fas fa-envelope"></i>
                                    <span class="nav-link-text">{{ __('Mail_Config') }}</span>
                                </a>

                                <a class="nav-link sub-menu {{ request()->routeIs('areas.*') ? 'active' : '' }}"
                                    href="{{ route('areas.index') }}">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="nav-link-text">{{ __('Areas') }}</span>
                                </a>
                            </ul>
                        </div>
                    </li>
                @endrole

                @can('profile.index')
                    <li class="nav-item">
                        <a class="nav-link sub-menu {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                            href="{{ route('profile.index') }}">
                            <i class="fas fa-user"></i>
                            <span class="nav-link-text">{{ __('Profile') }}</span>
                        </a>
                    </li>
                @endcan

                <li class="nav-item">
                    <a class="nav-link sub-menu {{ request()->routeIs('language.*') ? 'active' : '' }}"
                        href="{{ route('language.index') }}">
                        <i class="fas fa-language text-primary"></i>
                        <span class="nav-link-text">{{ __('language') }}</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                        onclick="event.preventDefault();
                                        document.getElementById('logout').submit()"
                        href="#">
                        <i class="fas fa-sign-out-alt text-warning"></i>
                        <span class="nav-link-text">{{ __('Logout') }}</span>
                    </a>
                    <form id="logout" action="{{ route('logout') }}" method="POST"> @csrf </form>
                </li>

            </ul>
        </div>
        <div class="footer_bottom" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            <div class="local">
                <i class="fa fa-language lanIcon"></i>
                <select id="language" name="ln" class="form-control">
                    <option value="en" {{ session()->get('local') == 'en' ? 'selected' : '' }}>English
                    </option>
                    <option value="ar" {{ session()->get('local') == 'ar' ? 'selected' : '' }}>Arabic</option>
                </select>
            </div>
            <div class="profile d-flex {{ app()->getLocale() == 'ar' ? 'justify-content-end' : 'justify-content-start' }}">
                <div>
                    <img src="{{ auth()->user()->profile_photo_path }}" alt="" width="50"
                        height="50">
                </div>
                <div>
                    <h3 class="name m-0">{{ auth()->user()->name }}</h3>
                    <p class="email m-0">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </div>

</nav>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="{{ $appearance['effective'] }}"
    data-appearance="{{ $appearance['choice'] }}">
<!--suppress HtmlRequiredTitleElement -->
<head>
    @include('default.layouts.partials.head', [
        'themeColor' => $unruly['accent'],
        'stylesheet' => 'resources/scss/app.scss',
    ])
    @stack('head')
</head>
<body class="theme-unruly" style="--unruly-accent:{{ $unruly['accent'] }}">
    <a href="#main-content" class="skip-link">{{ __('theme::messages.navigation.skip') }}</a>
    <header class="unruly-header">
        <div class="container unruly-header-inner">
            <a class="unruly-brand" href="{{ url('/') }}">
                @if ($unruly['logo'])
                    <img src="{{ $unruly['logo'] }}" alt="{{ $siteName }}" width="180" height="54">
                @elseif($publicSite['logo'])
                    @include('default.layouts.partials.brand')
                @else
                    {{ $siteName }}
                @endif
            </a>
            <button class="unruly-menu-toggle" type="button" data-unruly-menu-toggle aria-controls="unruly-menu"
                aria-expanded="false" hidden>{{ __('theme::messages.navigation.menu') }} ☰</button>
            <nav class="unruly-menu" id="unruly-menu" aria-label="{{ __('theme::messages.navigation.main') }}">
                @if ($capabilities->hasCustomHeader)
                    @include('default.layouts.partials.navigation-links', ['navigationItems' => $headerLinks])
                @else
                    <a href="{{ url('/') }}">{{ __('theme::messages.navigation.home') }}</a>
                    @if ($capabilities->hasNews)
                        <a href="{{ route('site.news.index') }}">{{ __('common.labels.news_plural') }}</a>
                    @endif
                    @foreach ($publicSite['extensionHeaderLinks'] as $link)
                        <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                    @endforeach
                @endif
                @include('default.layouts.partials.appearance-menu')
                @auth
                    <a href="{{ route('account.notifications.index') }}"
                        aria-label="{{ $publicSite['unreadNoticeCount'] ? trans_choice('site.community.unread_notices', $publicSite['unreadNoticeCount']) : __('site.community.my_notices') }}"
                        title="{{ __('site.community.my_notices') }}">
                        <span class="notification-icon">
                            <i class="bi bi-bell" aria-hidden="true"></i>
                            @if ($publicSite['unreadNoticeCount'] > 0)
                                <span class="notification-count badge rounded-pill text-bg-danger" aria-hidden="true">
                                    {{ $publicSite['unreadNoticeCount'] > 99 ? '99+' : $publicSite['unreadNoticeCount'] }}
                                </span>
                            @endif
                        </span>
                    </a>
                @endauth
                <details class="navigation-dropdown unruly-account">
                    <summary>
                        @auth
                            @include('partials.users.avatar', ['avatarUser' => auth()->user()])
                        @endauth
                        {{ auth()->user()?->username ?? __('theme::messages.navigation.login') }}
                    </summary>
                    <div class="navigation-submenu">
                        @auth
                            <a href="{{ route('account.index') }}">{{ __('theme::messages.navigation.account') }}</a>
                            <a
                                href="{{ route('account.notifications.index') }}">{{ __('theme::messages.navigation.notifications') }}</a>
                            @if ($capabilities->canAccessAdmin)
                                <a href="{{ route('admin.dashboard.index') }}"
                                    target="_blank">{{ __('theme::messages.navigation.admin') }}</a>
                            @endif
                            <form data-ajax="true" data-redirect-url="response" data-success-message="false" method="post"
                                action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit">{{ __('theme::messages.navigation.logout') }}</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}">{{ __('theme::messages.navigation.login') }}</a>
                            @if ($capabilities->canRegister)
                                <a href="{{ route('register') }}">{{ __('theme::messages.navigation.register') }}</a>
                            @endif
                        @endauth
                    </div>
                </details>
                @if ($capabilities->canChooseLocale)
                    <details class="navigation-dropdown unruly-language">
                        <summary>{{ strtoupper(app()->getLocale()) }}</summary>
                        <div class="navigation-submenu">
                            @foreach (config('kraft.locales') as $code => $languageName)
                                <a href="{{ route('site.language.update', ['locale' => $code]) }}"
                                    lang="{{ $code }}">{{ $languageName }}</a>
                            @endforeach
                        </div>
                    </details>
                @endif
            </nav>
        </div>
    </header>
    @unless (request()->routeIs('site.home.index'))
        <div class="unruly-page-banner">
            <div class="container">
                <a href="{{ url('/') }}">{{ $siteName }}</a><span>{{ __('theme::messages.footer.community') }}</span>
            </div>
        </div>
    @endunless
    <main id="main-content" class="unruly-main">
        @if (request()->routeIs('site.home.index'))
            <div class="container">
                @include('partials.feedback.alerts')
            </div>
            <x-extensions.slot name="site.page.before" />
            @yield('content')
            <x-extensions.slot name="site.page.after" />
        @else
            <div class="container unruly-plugin">
                @include('partials.feedback.alerts')
                <x-extensions.slot name="site.page.before" />
                @yield('content')
                <x-extensions.slot name="site.page.after" />
            </div>
        @endif
    </main>
    <footer class="unruly-footer">
        <div class="container unruly-footer-inner">
            <div>
                <a href="{{ url('/') }}" class="unruly-brand">{{ $siteName }}</a>
                <p>{{ __('theme::messages.footer.description') }}</p>
            </div>
            <nav aria-label="{{ __('theme::messages.footer.navigation') }}">
                @if ($capabilities->hasCustomFooter)
                    @include('default.layouts.partials.navigation-links', ['navigationItems' => $footerLinks])
                @else
                    @if ($capabilities->hasRules)
                        <a href="{{ url('/p/rules') }}">{{ __('theme::messages.footer.rules') }}</a>
                    @endif
                    @if ($capabilities->hasPrivacy)
                        <a href="{{ url('/p/privacy') }}">{{ __('theme::messages.footer.privacy') }}</a>
                    @endif
                    @foreach ($publicSite['extensionFooterLinks'] as $link)
                        <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                    @endforeach
                @endif
            </nav>
            @include('default.layouts.partials.social-links')
            <small>{{ $publicSite['copyright'] }}<br>
                @include('default.layouts.partials.footer-credit', ['footerCreditClass' => 'text-decoration-underline'])
            </small>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>

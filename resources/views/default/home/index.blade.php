@use('App\Kraft\SiteDates')
@use('Illuminate\Support\Str')
@extends('default.layouts.layout')
@section('content')
    <x-extensions.slot name="site.home.before" :context="['news' => $news]" />
    @include('default.home.partials.slider')
    @include('default.home.partials.welcome')
    <section class="unruly-hero" aria-labelledby="unruly-title">
        @if ($unruly['hero_image'])
            <img class="unruly-hero-image" src="{{ $unruly['hero_image'] }}" alt="" fetchpriority="high">
        @else
            <div class="unruly-hero-scenes" aria-hidden="true">
                @foreach ($showcaseImages as $image)
                    <img src="{{ $image }}" alt="" width="360" height="400"
                        @if ($loop->first) fetchpriority="high" @endif>
                @endforeach
            </div>
        @endif
        <div class="container unruly-hero-copy">
            <h1 id="unruly-title">{{ $unruly['hero_title'] ?: ($publicSite['homeTitle'] ?: $publicSite['name']) }}</h1>
            <p>
                {{ $unruly['hero_subtitle'] ?: $publicSite['homeDescription'] ?: $publicSite['description'] ?: __('theme::messages.home.hero.subtitle') }}
            </p>
        </div>
    </section>
    <div class="container unruly-ribbon-wrap">
        @if ($unruly['show_sword'])
            <button type="button" class="unruly-sword" data-unruly-sword
                aria-label="{{ __('theme::messages.home.hero.change_sword') }}" aria-pressed="false"></button>
        @endif
        <div class="unruly-ribbon">
            @if ($unruly['show_connection'] && $address)
                <button type="button" data-copy="{{ $address }}"
                    data-copied="{{ __('theme::messages.home.ribbon.copied') }}">{{ $unruly['ribbon_text'] }}
                    <strong>{{ $address }}</strong></button>
            @else
                <p>{{ $unruly['ribbon_text'] }}</p>
            @endif
        </div>
    </div>
    <div class="container unruly-home-body">
        @if ($unruly['show_showcases'])
            <section class="unruly-showcases" aria-label="{{ __('theme::messages.home.showcases.title') }}">
                @foreach ($showcaseImages as $prefix => $image)
                    <a class="unruly-showcase"
                        href="{{ $unruly[$prefix . 'url'] ?: ($capabilities->hasNews ? '#community-news' : '#main-content') }}">
                        <img src="{{ $unruly[$prefix . 'image'] ?: $image }}"
                            alt="" width="360" height="400" loading="lazy">
                        <div>
                            <h2>{{ $unruly[$prefix . 'title'] }}</h2>
                            <p>{{ $unruly[$prefix . 'text'] }}</p>
                        </div>
                    </a>
                @endforeach
            </section>
        @endif
        @if ($capabilities->hasNews || $hasSidebar)
            <div @class([
                'unruly-columns',
                'd-block' => !$capabilities->hasNews || !$hasSidebar,
            ])>
                @if ($capabilities->hasNews)
                    <section class="unruly-news" id="community-news" aria-labelledby="news-heading">
                        <h2 class="unruly-section-title" id="news-heading">
                            <span>{{ __('theme::messages.home.news.title') }}</span>
                        </h2>
                        <div id="home-news-list" data-ajax-pagination tabindex="-1">
                            @foreach ($news as $entry)
                                <article class="unruly-news-item">
                                    <h3><a href="{{ $entry->url() }}">{{ $entry->title }}</a></h3>
                                    <time
                                        datetime="{{ app(SiteDates::class)->iso($entry->published_at) }}">{{ app(SiteDates::class)->format($entry->published_at, 'd F Y') }}</time>
                                    <p>{{ $entry->summary ?: Str::limit(strip_tags($entry->body), 320) }}</p>
                                    <a class="button button-small"
                                        href="{{ $entry->url() }}">{{ __('theme::messages.home.news.read') }}</a>
                                </article>
                            @endforeach
                            {{ $news->links() }}
                        </div>
                    </section>
                @endif
                @if ($hasSidebar)
                    <aside class="unruly-sidebar" aria-label="{{ __('theme::messages.footer.community') }}">
                        @if (auth()->check() || $capabilities->canRegister)
                            <section class="unruly-widget">
                                <h2>{{ __('theme::messages.home.community.title') }}</h2>
                                <div>
                                    <p>{{ __('theme::messages.home.community.description') }}</p>
                                    <a class="button button-small"
                                        href="{{ auth()->check() ? route('account.index') : route('register') }}">{{ auth()->check() ? __('theme::messages.navigation.account') : __('theme::messages.navigation.register') }}</a>
                                </div>
                            </section>
                        @endif
                        @if ($unruly['show_connection'])
                            @include('default.home.partials.community-connection')
                        @endif
                        @if ($voteUrl)
                            <section class="unruly-widget">
                                <h2>{{ __('theme::messages.home.vote.title') }}</h2>
                                <div>
                                    <p>{{ __('theme::messages.home.vote.description') }}</p>
                                    <a class="button button-small"
                                        href="{{ $voteUrl }}">{{ __('theme::messages.home.vote.action') }}</a>
                                </div>
                            </section>
                        @endif
                        @if ($unruly['show_launcher'] && $unruly['launcher_url'])
                            <section class="unruly-widget">
                                <h2>{{ $unruly['launcher_title'] }}</h2>
                                <div>
                                    <p>{{ $unruly['launcher_text'] }}</p>
                                    <a class="button button-small" href="{{ $unruly['launcher_url'] }}"
                                        rel="noopener noreferrer">{{ __('theme::messages.home.launcher.action') }}</a>
                                </div>
                            </section>
                        @endif
                        @if ($unruly['discord_url'])
                            <a class="unruly-social" href="{{ $unruly['discord_url'] }}" rel="noopener noreferrer"><span
                                    aria-hidden="true">◎</span>{{ __('theme::messages.home.discord.action') }}</a>
                        @endif
                    </aside>
                @endif
            </div>
        @endif
    </div>
    <x-widgets.zone name="home" :context="['news' => $news]" />
    <x-extensions.slot name="site.home.after" :context="['news' => $news]" />
@endsection

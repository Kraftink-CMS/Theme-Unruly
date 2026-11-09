<?php

namespace KraftInk\Themes\Unruly\Providers;

use App\Kraft\Extensions\ThemeContext;
use App\Kraft\Site\PublicViewContext;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use KraftInk\Themes\Unruly\Services\Configuration;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        view()->composer(['default.layouts.layout', 'default.home.index'], function (View $view): void {
            $theme = app(ThemeContext::class)->current();
            if (($theme['id'] ?? null) !== 'unruly') {
                return;
            }
            $configuration = app(Configuration::class)->all();
            $view->with('unruly', $configuration);
            if ($view->name() !== 'default.home.index') {
                return;
            }
            $context = app(PublicViewContext::class)->data(request());
            $address = $context['publicSite']['communityAddress'];
            $capabilities = $context['capabilities'];
            $hasConnectionDetails = $address || $context['communityConnection']['players_online'] !== null;
            $voteRoute = 'plugins.vote.public.votes.index';
            $voteUrl = $capabilities->routeAvailable($voteRoute) ? route($voteRoute) : null;
            $showcaseImages = [];
            foreach (Configuration::SHOWCASES as $index => $type) {
                $showcaseImages['showcase_'.$index.'_'] = route('site.extensions.assets.show', [
                    'id' => $theme['id'],
                    'asset' => 'images/showcase_'.$type.'.png',
                ]);
            }
            $view->with([
                'address' => $address,
                'showcaseImages' => $showcaseImages,
                'voteUrl' => $voteUrl,
                'hasSidebar' => auth()->check() ||
                    $capabilities->canRegister ||
                    ($configuration['show_connection'] && $hasConnectionDetails) ||
                    $voteUrl ||
                    ($configuration['show_launcher'] && $configuration['launcher_url']) ||
                    $configuration['discord_url'],
            ]);
        });
    }
}

<?php

namespace KraftInk\Themes\Unruly\Services;

use App\Kraft\LocalePolicy;
use App\Kraft\Settings;
use App\Kraft\SocialLinks;
use App\Kraft\TextTranslations;

class Configuration
{
    public const array SHOWCASES = [1 => 'pvp', 2 => 'survival', 3 => 'adventure'];

    public function __construct(private readonly Settings $settings)
    {
    }

    public static function textFields(): array
    {
        $fields = [
            'hero_title' => ['max' => 120],
            'hero_subtitle' => ['max' => 240],
            'ribbon_text' => ['max' => 160, 'required' => true],
            'launcher_title' => ['max' => 120],
            'launcher_text' => ['max' => 500, 'type' => 'textarea'],
        ];
        foreach (array_keys(self::SHOWCASES) as $index) {
            $fields['showcase_'.$index.'_title'] = ['max' => 80, 'required' => true];
            $fields['showcase_'.$index.'_text'] = ['max' => 180, 'required' => true, 'type' => 'textarea'];
        }

        return $fields;
    }

    public function translations(): array
    {
        return $this->settings->get('theme.unruly.translations', []);
    }

    public function all(bool $localized = true): array
    {
        $locale = $localized ? app()->getLocale() : app(LocalePolicy::class)->defaultLocale();
        $defaults = $this->defaults($locale);
        $saved = $this->settings->get('theme.unruly.config', []);
        $saved = is_array($saved) ? array_intersect_key($saved, $defaults) : [];
        $configuration = array_replace($defaults, $saved);
        if (!array_key_exists('discord_url', $saved)) {
            if ($localized) {
                $configuration['discord_url'] = collect(app(SocialLinks::class)->visible())->firstWhere('type', 'discord')['url'] ?? '';
            } else {
                unset($configuration['discord_url']);
            }
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/D', $configuration['accent'])) {
            $configuration['accent'] = $defaults['accent'];
        }
        if ($localized && app(LocalePolicy::class)->allowsChoice()) {
            $translations = $this->translations();
            $primary = $this->defaults(app(LocalePolicy::class)->defaultLocale());
            foreach (self::textFields() as $field => $options) {
                if (
                    !array_key_exists($field, $saved) &&
                    !collect($translations)->contains(fn ($entry) => filled($entry[$field] ?? null))
                ) {
                    continue;
                }
                $configuration[$field] = app(TextTranslations::class)->resolve(
                    (string) ($saved[$field] ?? $primary[$field]),
                    $translations,
                    $field,
                );
            }
        }

        return $configuration;
    }

    private function defaults(string $locale): array
    {
        $defaults = [
            'accent' => '#00852f',
            'hero_title' => '',
            'hero_subtitle' => '',
            'hero_image' => '',
            'logo' => '',
            'ribbon_text' => __('unruly::messages.home.ribbon.label', [], $locale),
            'show_sword' => true,
            'show_showcases' => true,
            'show_connection' => true,
            'show_launcher' => false,
            'launcher_title' => __('unruly::messages.home.launcher.title', [], $locale),
            'launcher_text' => __('unruly::messages.home.launcher.description', [], $locale),
            'launcher_url' => '',
            'discord_url' => '',
        ];
        foreach (self::SHOWCASES as $index => $type) {
            $prefix = 'showcase_'.$index.'_';
            $defaults[$prefix.'title'] = __(
                'unruly::messages.home.showcases.'.$type.'.title',
                [],
                $locale,
            );
            $defaults[$prefix.'text'] = __(
                'unruly::messages.home.showcases.'.$type.'.description',
                [],
                $locale,
            );
            $defaults[$prefix.'image'] = '';
            $defaults[$prefix.'url'] = '';
        }

        return $defaults;
    }

    public function save(array $configuration): void
    {
        $translations = $configuration['translations'] ?? null;
        unset($configuration['translations']);
        $this->settings->set('theme.unruly.config', $configuration);
        if (is_array($translations) && app(LocalePolicy::class)->allowsChoice()) {
            $this->settings->set(
                'theme.unruly.translations',
                app(TextTranslations::class)->normalize($translations, $this->translations(), self::textFields()),
            );
        }
    }
}

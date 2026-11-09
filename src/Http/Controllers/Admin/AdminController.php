<?php

namespace KraftInk\Themes\Unruly\Http\Controllers\Admin;

use App\Kraft\Audit;
use App\Kraft\NavigationTargets;
use App\Kraft\TextTranslations;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use KraftInk\Themes\Unruly\Services\Configuration;

class AdminController
{
    public function index(Configuration $configuration): View
    {
        $showcaseFields = [];
        foreach (array_keys(Configuration::SHOWCASES) as $index) {
            foreach (['title', 'text', 'image', 'url'] as $field) {
                $showcaseFields[$index]['showcase_'.$index.'_'.$field] = $field;
            }
        }

        return view('unruly::admin.configuration.index', [
            'configuration' => $configuration->all(false),
            'translations' => $configuration->translations(),
            'showcaseFields' => $showcaseFields,
            'textFields' => $this->labeledTextFields(),
        ]);
    }

    public function save(Request $request, Configuration $configuration): RedirectResponse
    {
        $url = function (string $attribute, mixed $value, Closure $fail): void {
            if (
                !is_string($value) ||
                !NavigationTargets::validUrl($value) ||
                strpbrk($value, '"<>') !== false ||
                (!str_starts_with($value, '/') && parse_url($value, PHP_URL_SCHEME) !== 'https')
            ) {
                $fail(__('unruly::messages.admin.validation.url'));
            }
        };
        $urlRules = ['nullable', 'string', 'max:2000', $url];
        $rules = [
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/D'],
            'hero_image' => $urlRules,
            'logo' => $urlRules,
            'show_sword' => 'boolean',
            'show_showcases' => 'boolean',
            'show_connection' => 'boolean',
            'show_launcher' => 'boolean',
            'launcher_url' => $urlRules,
            'override_discord' => 'boolean',
            'discord_url' => ['exclude_if:override_discord,0', ...$urlRules],
        ];
        foreach (array_keys(Configuration::SHOWCASES) as $index) {
            $rules['showcase_'.$index.'_image'] = $urlRules;
            $rules['showcase_'.$index.'_url'] = $urlRules;
        }
        $textFields = Configuration::textFields();
        foreach ($textFields as $field => $options) {
            $rules[$field] = [($options['required'] ?? false) ? 'required' : 'nullable', 'string', 'max:'.$options['max']];
        }
        $data = $request->validate($rules + app(TextTranslations::class)->rules($textFields));
        if ($request->boolean('override_discord')) {
            $data['discord_url'] ??= '';
        }
        unset($data['override_discord']);
        foreach (['show_sword', 'show_showcases', 'show_connection', 'show_launcher'] as $field) {
            $data[$field] = $request->boolean($field);
        }
        DB::transaction(function () use ($configuration, $data): void {
            $configuration->save($data);
            Audit::record('theme.configured', ['id' => 'unruly']);
        });

        return back()->with('status', __('unruly::messages.admin.saved'));
    }

    private function labeledTextFields(): array
    {
        $fields = Configuration::textFields();
        foreach (array_keys($fields) as $field) {
            $fields[$field]['label'] = preg_match('/^showcase_(\d+)_(title|text)$/', $field, $match)
                ? __('unruly::messages.admin.showcase_number', ['number' => $match[1]]).
                    ' · '.
                    __('unruly::messages.admin.fields.'.$match[2])
                : __('unruly::messages.admin.fields.'.$field);
        }

        return $fields;
    }
}

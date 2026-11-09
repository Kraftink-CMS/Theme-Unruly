@use('App\Kraft\LocalePolicy')
@extends('admin.layouts.layout')
@section('title', __('unruly::messages.admin.title'))
@section('description', __('unruly::messages.admin.description'))
@section('content')
    <form data-ajax="true" method="post" action="{{ route('themes.unruly.admin.configuration.update') }}" class="vstack gap-4">
        @csrf
        <section class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('unruly::messages.admin.sections.identity') }}</h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach (['accent', 'hero_title', 'hero_subtitle', 'hero_image', 'logo', 'ribbon_text'] as $field)
                        <div class="col-md-6">
                            @if (in_array($field, ['hero_image', 'logo'], true))
                                <x-admin::media-picker :name="$field" :value="$configuration[$field]" value-mode="url"
                                    context="theme-image" profile="image" :label="__('unruly::messages.admin.fields.' . $field)" />
                            @else
                                <label class="form-label"
                                    for="unruly-{{ $field }}">{{ __('unruly::messages.admin.fields.' . $field) }}</label>
                                <input @class(['form-control', 'form-control-color' => $field === 'accent']) id="unruly-{{ $field }}"
                                    type="{{ $field === 'accent' ? 'color' : 'text' }}" name="{{ $field }}"
                                    value="{{ old($field, $configuration[$field]) }}"
                                    @if (isset($textFields[$field])) maxlength="{{ $textFields[$field]['max'] }}" @endif
                                    @required($textFields[$field]['required'] ?? false)>
                            @endif
                        </div>
                    @endforeach
                    <div class="col-md-6">
                        @php($overrideDiscord = old('override_discord', array_key_exists('discord_url', $configuration)))
                        <input type="hidden" name="override_discord" value="0">
                        <div class="form-check mb-2">
                            <input class="form-check-input" id="unruly-override-discord" type="checkbox"
                                name="override_discord" value="1" data-fields-target="unruly-discord-settings"
                                @checked($overrideDiscord)>
                            <label class="form-check-label" for="unruly-override-discord">
                                {{ __('unruly::messages.admin.fields.override_discord') }}
                            </label>
                        </div>
                        <fieldset id="unruly-discord-settings" @if (!$overrideDiscord) hidden disabled @endif>
                            <label class="form-label" for="unruly-discord-url">
                                {{ __('unruly::messages.admin.fields.discord_url') }}
                            </label>
                            <input class="form-control" id="unruly-discord-url" name="discord_url" type="text"
                                inputmode="url" maxlength="2000" value="{{ old('discord_url', $configuration['discord_url'] ?? '') }}">
                        </fieldset>
                    </div>
                </div>
            </div>
        </section>
        <section class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('unruly::messages.admin.sections.showcases') }}</h2>
            </div>
            <div class="card-body">
                @foreach ($showcaseFields as $index => $fields)
                    <fieldset class="border-bottom pb-4 mb-4">
                        <legend class="float-none h5 mb-3">
                            {{ __('unruly::messages.admin.showcase_number', ['number' => $index]) }}</legend>
                        <div class="row g-3">
                            @foreach ($fields as $key => $field)
                                <div class="col-md-6">
                                    @if ($field === 'image')
                                        <x-admin::media-picker :name="$key" :value="$configuration[$key]" value-mode="url"
                                            context="theme-image" profile="image" :label="__('unruly::messages.admin.fields.image')" />
                                    @else
                                        <label class="form-label"
                                            for="unruly-{{ $key }}">{{ __('unruly::messages.admin.fields.' . $field) }}</label>
                                        <input class="form-control" id="unruly-{{ $key }}"
                                            name="{{ $key }}" value="{{ old($key, $configuration[$key]) }}"
                                            @if (isset($textFields[$key])) maxlength="{{ $textFields[$key]['max'] }}" @endif
                                            @required($textFields[$key]['required'] ?? false)>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
                <p class="form-text mb-0">{{ __('unruly::messages.admin.image_hint') }}</p>
            </div>
        </section>
        @if (app(LocalePolicy::class)->allowsChoice())
            <section class="card">
                <div class="card-body">
                    <x-admin::translation-fields id="unruly-text-translations" :fields="$textFields" :values="$translations" />
                </div>
            </section>
        @endif
        <section class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('unruly::messages.admin.sections.sidebar') }}</h2>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    @foreach (['launcher_title', 'launcher_text', 'launcher_url'] as $field)
                        <div class="col-md-6">
                            <label class="form-label"
                                for="unruly-{{ $field }}">{{ __('unruly::messages.admin.fields.' . $field) }}</label>
                            <input class="form-control" id="unruly-{{ $field }}" name="{{ $field }}"
                                value="{{ old($field, $configuration[$field]) }}"
                                @if (isset($textFields[$field])) maxlength="{{ $textFields[$field]['max'] }}" @endif>
                        </div>
                    @endforeach
                </div>
                @foreach (['show_sword', 'show_showcases', 'show_connection', 'show_launcher'] as $field)
                    <input type="hidden" name="{{ $field }}" value="0">
                    <div class="form-check mb-3">
                        <input class="form-check-input" id="unruly-{{ $field }}" type="checkbox"
                            name="{{ $field }}" value="1" @checked(old($field, $configuration[$field]))>
                        <label class="form-check-label"
                            for="unruly-{{ $field }}">{{ __('unruly::messages.admin.fields.' . $field) }}</label>
                    </div>
                @endforeach
            </div>
            <div class="card-footer d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">{{ __('unruly::messages.admin.actions.save') }}</button>
            </div>
        </section>
    </form>
@endsection

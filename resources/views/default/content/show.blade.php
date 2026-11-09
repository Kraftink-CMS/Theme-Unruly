@extends('default.layouts.layout')
@section('title', $entry->title)
@section('content')
    <div class="unruly-panel unruly-article">
        @include('default.content.partials.body')
    </div>
@endsection

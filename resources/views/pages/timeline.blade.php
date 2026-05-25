@php use App\Models\Timeline; @endphp
@php
/** @var Timeline[] $timelines */
@endphp

@extends('app')

@section('body')
    <div class="timeline-menu">
        <div class="container flex justify-center">
            @php $x = 0 @endphp
            @foreach($timelines as $timeline)
                <div class="year year-select mx-6 @if($x === 0) active @endif" data-year="{{$timeline->year}}">
                    <span>{{$timeline->year}}</span>
                </div>
                @php $x++ @endphp
            @endforeach
        </div>
    </div>

    <div class="timeline-container" id="timeline-1">
        <div class="timeline-header hidden">
            <h2 class="timeline-header__title">Mustafa Kemal Atatürk</h2>
            <h3 class="timeline-header__subtitle">FATHER OF THE TURKS</h3>
        </div>

        <div class="timeline">
            @foreach($timelines as $timeline)
                <div class="timeline-item" data-year="{{$timeline->year}}" id="timeline-year-{{$timeline->year}}" data-text="{{$timeline->title}}">
                    <div class="timeline__content"><img class="timeline__img" src="/storage/{{$timeline->image}}" alt="" loading="lazy" decoding="async"/>
                        <h2 class="timeline__content-title">{{$timeline->year}}</h2>
                        <p class="timeline__content-desc">{{$timeline->text}}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@endsection


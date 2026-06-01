@php
    use App\Models\AnnualReport;
    /** @var AnnualReport[] $reports */
@endphp

@extends('app')

@section('body')
    <main class="container">
        <div class="line mt-8"></div>
        <h1 class="text-6xl mt-12 mb-16">Annual Reports</h1>
        @if (count($reports) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-16 gap-y-24 pb-24">

            @foreach($reports as $report)
                <div class="article-item">
                    @if($report->file)
                        <a href="/storage/{{$report->file}}" style="display:block; height:410px; border-radius:4px; background:no-repeat center/cover; background-image:url('/storage/{{$report->image}}'); background-color:#1a1a2e;"></a>
                    @else
                        <div style="display:block; height:410px; border-radius:4px; background:#1a1a2e; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.35); font-size:14px;">Coming soon</div>
                    @endif
                    <div class="line my-4"></div>
                    @if($report->file)
                        <a class="text-xl text-white text-center block" href="/storage/{{$report->file}}">{{$report->title}}</a>
                    @else
                        <span class="text-xl text-white text-center block">{{$report->title}}</span>
                    @endif
                </div>
            @endforeach
        </div>
        @else
            @include('sections.coming-soon', ['message' => 'Our annual reports will be posted here soon. Please check back shortly.'])
        @endif
    </main>
@endsection

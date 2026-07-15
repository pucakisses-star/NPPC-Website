@extends('app')

@section('title', 'Frequently Asked Questions | NPPC')

@section('body')
    <div class="line mt-8"></div>
    @include('sections.faq', ['type'=>'faq'])
@endsection

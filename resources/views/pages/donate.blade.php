@extends('app')

@section('head')
<style>
    .donate-page { max-width: 1100px; margin: 0 auto; padding: 0 24px; }
    .donate-hero { display: flex; gap: 48px; align-items: flex-start; padding: 48px 0 40px; }
    .donate-image { flex: 0 0 45%; border-radius: 8px; overflow: hidden; }
    .donate-image img { width: 100%; height: auto; display: block; }
    .donate-form-side { flex: 1; }
    .donate-title { font-size: 2.5rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 20px; }
    .donate-desc { font-size: 15px; color: rgba(255,255,255,0.65); line-height: 1.7; margin-bottom: 32px; }
    .donate-label { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.7); margin-bottom: 12px; }
    .donate-intervals { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 20px; }
    .donate-interval { text-align: center; padding: 10px; border: 1px solid rgba(255,255,255,0.3); color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.15s; background: transparent; }
    .donate-interval:hover { border-color: #5660fe; }
    .donate-interval.active { background: #5660fe; border-color: #5660fe; }
    .donate-amounts { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 20px; }
    .donate-amount { text-align: center; padding: 10px; border: 1px solid rgba(255,255,255,0.3); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.15s; background: transparent; }
    .donate-amount:hover { border-color: #5660fe; }
    .donate-amount.active { background: #5660fe; border-color: #5660fe; }
    .donate-submit { width: 100%; background: #5660fe; color: #fff; border: none; padding: 14px; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; transition: background 0.2s; }
    .donate-submit:hover { background: #4850e6; }
    .donate-custom-input { background: transparent; border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 10px 14px; font-size: 18px; width: 100%; margin-bottom: 20px; outline: none; }
    .donate-custom-input:focus { border-color: #5660fe; }
    .donate-fine-print { font-size: 11px; color: rgba(255,255,255,0.3); text-align: center; margin-top: 16px; line-height: 1.5; }
    @@media (max-width: 768px) {
        .donate-page { padding: 0 16px; }
        .donate-hero { flex-direction: column; gap: 24px; padding: 24px 0 24px; }
        .donate-image { flex: auto; width: 100%; }
        .donate-title { font-size: 1.8rem; }
    }
    @@media (max-width: 420px) {
        .donate-amounts { grid-template-columns: repeat(2, 1fr); }
        .donate-intervals .donate-interval { font-size: 13px; padding: 12px 4px; }
        .donate-amount { font-size: 14px; padding: 14px 4px; min-height: 44px; }
    }
</style>
@endsection

@section('body')
<div class="donate-page">
    <div class="donate-hero">
        <div class="donate-image">
            <img src="/images/stop-jailing-truth-tellers.webp" alt="Support political prisoners">
        </div>
        <div class="donate-form-side">
            <h1 class="donate-title">Donate to help free political prisoners.</h1>
            <p class="donate-desc">The National Political Prisoner Coalition works to support our nation's political prisoners, fight against wrongful convictions, and create fair, compassionate, and equitable systems of justice for everyone. With your support, we can do even more — donate today.</p>

            <livewire:donation />

            <div class="donate-fine-print">
                &copy; {{ date('Y') }} National Political Prisoner Coalition &middot; Terms of Use &middot; Error Privacy &middot; Contact Us
            </div>
        </div>
    </div>

    @include('sections.faq', ['type'=>'donation'])
</div>
@endsection

@section('footer')
    <div id="app-gallery"></div>
@endsection

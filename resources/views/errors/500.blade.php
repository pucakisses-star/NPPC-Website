@extends('app')

@section('head')
<style>
    .error-page { min-height: 70vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 48px 24px; }
    .error-code { font-size: 8rem; font-weight: 900; color: rgba(255,255,255,0.06); line-height: 1; letter-spacing: 0.04em; margin-bottom: -10px; }
    .error-icon { font-size: 64px; line-height: 1; margin-bottom: 16px; opacity: 0.6; }
    .error-title { font-size: 2rem; font-weight: 800; color: #fff; margin: 24px 0 16px; }
    .error-desc { font-size: 16px; color: rgba(255,255,255,0.5); line-height: 1.7; max-width: 540px; margin: 0 auto 40px; }
    .error-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .error-btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 4px; transition: all 0.2s; }
    .error-btn-primary { background: #5660fe; color: #fff; }
    .error-btn-primary:hover { background: #4850e6; }
    .error-btn-outline { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.25); }
    .error-btn-outline:hover { border-color: #5660fe; color: #5660fe; }
</style>
@endsection

@section('body')
<div class="error-page">
    <div>
        <div class="error-icon">&#x1F6A7;</div>
        <div class="error-code">UNDER CONSTRUCTION</div>
        <h1 class="error-title">This page is being worked on</h1>
        <p class="error-desc">Sorry — something went wrong while loading this page, and we're patching it up. Try one of the links below in the meantime.</p>

        <div class="error-actions">
            <a href="/" class="error-btn error-btn-primary">Go Home</a>
            <a href="/database" class="error-btn error-btn-outline">Prisoner Database</a>
            <a href="/news" class="error-btn error-btn-outline">News</a>
            <a href="/contact" class="error-btn error-btn-outline">Contact Us</a>
        </div>
    </div>
</div>
@endsection

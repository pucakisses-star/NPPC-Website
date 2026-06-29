@extends('app')

@section('head')
<style>
    .error-page { min-height: 70vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 48px 24px; }
    .error-code { font-size: 10rem; font-weight: 900; color: rgba(var(--fg-rgb),0.06); line-height: 1; margin-bottom: -20px; position: relative; }
    .error-code::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 4px; background: var(--accent); border-radius: 2px; }
    .error-title { font-size: 2rem; font-weight: 800; color: var(--fg); margin: 32px 0 16px; }
    .error-desc { font-size: 16px; color: rgba(var(--fg-rgb),0.5); line-height: 1.7; max-width: 480px; margin: 0 auto 40px; }
    .error-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .error-btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 4px; transition: all 0.2s; }
    .error-btn-primary { background: var(--accent); color: var(--on-accent); }
    .error-btn-primary:hover { background: var(--accent-hover); }
    .error-btn-outline { background: transparent; color: var(--fg); border: 1px solid rgba(var(--fg-rgb),0.25); }
    .error-btn-outline:hover { border-color: var(--accent); color: var(--accent); }
    .error-search { margin-top: 40px; max-width: 400px; margin-left: auto; margin-right: auto; }
    .error-search form { display: flex; border: 1px solid rgba(var(--fg-rgb),0.2); border-radius: 6px; overflow: hidden; }
    .error-search input { flex: 1; background: transparent; border: none; color: var(--fg); padding: 12px 16px; font-size: 14px; outline: none; }
    .error-search input::placeholder { color: rgba(var(--fg-rgb),0.3); }
    .error-search button { background: var(--accent); border: none; color: var(--on-accent); padding: 12px 20px; cursor: pointer; transition: background 0.2s; }
    .error-search button:hover { background: var(--accent-hover); }
</style>
@endsection

@section('body')
<div class="error-page">
    <div>
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-desc">The page you're looking for doesn't exist or has been moved. It may have been removed, renamed, or is temporarily unavailable.</p>

        <div class="error-actions">
            <a href="/" class="error-btn error-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
                Go Home
            </a>
            <a href="/database" class="error-btn error-btn-outline">Prisoner Database</a>
            <a href="/news" class="error-btn error-btn-outline">News</a>
            <a href="/contact" class="error-btn error-btn-outline">Contact Us</a>
        </div>

        <div class="error-search">
            <form action="/search" method="GET">
                <input type="text" name="q" placeholder="Search the site...">
                <button type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

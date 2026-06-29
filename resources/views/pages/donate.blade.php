@extends('app')

@section('head')
<style>
    .donate-page { max-width: 1100px; margin: 0 auto; padding: 0 24px; }
    .donate-hero { display: flex; gap: 48px; align-items: flex-start; padding: 48px 0 40px; }
    .donate-image { flex: 0 0 45%; border-radius: 8px; overflow: hidden; }
    .donate-image img { width: 100%; height: auto; display: block; }
    .donate-form-side { flex: 1; }
    .donate-title { font-size: 2.5rem; font-weight: 900; color: var(--fg); line-height: 1.1; margin-bottom: 20px; }
    .donate-desc { font-size: 15px; color: rgba(var(--fg-rgb),0.65); line-height: 1.7; margin-bottom: 32px; }
    .donate-label { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(var(--fg-rgb),0.7); margin-bottom: 12px; }
    .donate-intervals { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 20px; }
    .donate-interval { text-align: center; padding: 10px; border: 1px solid rgba(var(--fg-rgb),0.3); color: var(--fg); font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.15s; background: transparent; }
    .donate-interval:hover { border-color: var(--accent); }
    .donate-interval.active { background: var(--accent); border-color: var(--accent); color: var(--on-accent); }
    .donate-amounts { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 20px; }
    .donate-amount { text-align: center; padding: 10px; border: 1px solid rgba(var(--fg-rgb),0.3); color: var(--fg); font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.15s; background: transparent; }
    .donate-amount:hover { border-color: var(--accent); }
    .donate-amount.active { background: var(--accent); border-color: var(--accent); color: var(--on-accent); }
    .donate-submit { width: 100%; background: var(--accent); color: var(--on-accent); border: none; padding: 14px; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; transition: background 0.2s; }
    .donate-submit:hover { background: var(--accent-hover); }
    .donate-custom-input { background: transparent; border: 1px solid rgba(var(--fg-rgb),0.3); color: var(--fg); padding: 10px 14px; font-size: 18px; width: 100%; margin-bottom: 20px; outline: none; }
    .donate-custom-input:focus { border-color: var(--accent); }
    .donate-fine-print { font-size: 11px; color: rgba(var(--fg-rgb),0.3); text-align: center; margin-top: 16px; line-height: 1.5; }
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

    /* Crypto donation section */
    .crypto-donate { border-top: 1px solid rgba(var(--fg-rgb),0.12); margin-top: 8px; padding: 40px 0 8px; }
    .crypto-donate h2 { font-size: 1.5rem; font-weight: 800; color: var(--fg); margin: 0 0 8px; }
    .crypto-donate-intro { font-size: 14px; color: rgba(var(--fg-rgb),0.6); line-height: 1.6; margin: 0 0 24px; max-width: 720px; }
    .crypto-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .crypto-card { border: 1px solid rgba(var(--fg-rgb),0.18); border-radius: 8px; padding: 20px; text-align: center; background: rgba(var(--fg-rgb),0.02); }
    .crypto-card-head { font-size: 16px; font-weight: 800; color: var(--fg); }
    .crypto-card-head span { color: var(--accent-2); }
    .crypto-net { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(var(--fg-rgb),0.4); margin: 2px 0 14px; }
    .crypto-qr { width: 160px; height: 160px; border-radius: 6px; background: #fff; padding: 8px; margin: 0 auto 14px; display: block; box-sizing: border-box; }
    .crypto-addr { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; color: rgba(var(--fg-rgb),0.85); word-break: break-all; line-height: 1.5; margin-bottom: 12px; }
    .crypto-copy { width: 100%; background: transparent; border: 1px solid var(--accent); color: var(--accent-2); padding: 9px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; transition: background 0.15s, color 0.15s; }
    .crypto-copy:hover { background: var(--accent); color: var(--on-accent); }
    .crypto-copy.copied { background: #2e7d32; border-color: #2e7d32; color: var(--on-accent); }
    @@media (max-width: 768px) {
        .crypto-grid { grid-template-columns: 1fr; }
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

            <div class="donate-fine-print" style="font-size:12px; color:rgba(var(--fg-rgb),0.55); line-height:1.55; margin-top:20px;">
                <p style="margin:0 0 10px;">The National Political Prisoner Coalition is a 501(c)(3) tax-exempt nonprofit. <strong>Your donation is tax-deductible</strong> in the United States to the fullest extent permitted by law. You will receive an email donation receipt at the time of your gift.</p>
                <p style="margin:0;">&copy; {{ date('Y') }} National Political Prisoner Coalition &middot; <a href="/terms" style="color:inherit; text-decoration:underline;">Terms of Use</a> &middot; <a href="/privacy" style="color:inherit; text-decoration:underline;">Privacy</a> &middot; <a href="/contact" style="color:inherit; text-decoration:underline;">Contact Us</a></p>
            </div>
        </div>
    </div>

    @php
        // [display name, symbol, network label, settings key]. A coin renders
        // only once its donate_<key>_address SiteSetting is filled.
        $cryptoDefs = [
            ['Bitcoin', 'BTC', 'Bitcoin network', 'btc'],
            ['Ethereum', 'ETH', 'Ethereum (ERC-20)', 'eth'],
            ['Solana', 'SOL', 'Solana network', 'sol'],
            ['XRP', 'XRP', 'XRP Ledger', 'xrp'],
            ['Bitcoin Cash', 'BCH', 'Bitcoin Cash network', 'bch'],
            ['Litecoin', 'LTC', 'Litecoin network', 'ltc'],
            ['Dogecoin', 'DOGE', 'Dogecoin network', 'doge'],
            ['Cardano', 'ADA', 'Cardano network', 'ada'],
            ['Avalanche', 'AVAX', 'Avalanche C-Chain', 'avax'],
            ['Polkadot', 'DOT', 'Polkadot network', 'dot'],
            ['USD Coin', 'USDC', 'Ethereum (ERC-20)', 'usdc'],
            ['Tether', 'USDT', 'Ethereum (ERC-20)', 'usdt'],
            ['Dai', 'DAI', 'Ethereum (ERC-20)', 'dai'],
            ['Monero', 'XMR', 'Monero network', 'xmr'],
        ];
        $cryptoWallets = [];
        foreach ($cryptoDefs as [$cName, $cSymbol, $cNetwork, $cKey]) {
            $cAddress = \App\Models\SiteSetting::get('donate_'.$cKey.'_address');
            if (filled($cAddress)) {
                $cryptoWallets[] = ['name' => $cName, 'symbol' => $cSymbol, 'network' => $cNetwork, 'address' => $cAddress];
            }
        }
    @endphp

    @if (! empty($cryptoWallets))
    <div class="crypto-donate">
        <h2>Donate with cryptocurrency</h2>
        <p class="crypto-donate-intro">Prefer to give in crypto? Send any amount directly to the wallets below. Crypto gifts aren't receipted automatically — email <a href="mailto:donations@nppc.org" style="color:var(--accent-2);">donations@nppc.org</a> with your transaction so we can send a tax receipt.</p>
        <div class="crypto-grid">
            @foreach ($cryptoWallets as $w)
            <div class="crypto-card">
                <div class="crypto-card-head">{{ $w['name'] }} <span>{{ $w['symbol'] }}</span></div>
                <div class="crypto-net">{{ $w['network'] }}</div>
                <img class="crypto-qr" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&amp;margin=0&amp;data={{ urlencode($w['address']) }}" alt="{{ $w['name'] }} donation address QR code" width="160" height="160" loading="lazy">
                <div class="crypto-addr">{{ $w['address'] }}</div>
                <button type="button" class="crypto-copy" data-address="{{ $w['address'] }}">Copy address</button>
            </div>
            @endforeach
        </div>
    </div>
    <script>
        document.querySelectorAll('.crypto-copy').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var addr = btn.getAttribute('data-address');
                var done = function () {
                    btn.textContent = 'Copied!';
                    btn.classList.add('copied');
                    setTimeout(function () { btn.textContent = 'Copy address'; btn.classList.remove('copied'); }, 1800);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(addr).then(done).catch(done);
                } else {
                    var t = document.createElement('textarea');
                    t.value = addr; document.body.appendChild(t); t.select();
                    try { document.execCommand('copy'); } catch (e) {}
                    document.body.removeChild(t); done();
                }
            });
        });
    </script>
    @endif

    @include('sections.faq', ['type'=>'donation'])
</div>
@endsection

@section('footer')
    <div id="app-gallery"></div>
@endsection

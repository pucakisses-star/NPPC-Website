@extends('app')

@section('head')
<style>
    .pet-page { max-width: 1100px; margin: 0 auto; padding: 0 24px; }
    .pet-banner { background: #1a1a2e; padding: 20px 32px; margin: 0 -24px 32px; }
    .pet-banner-title { font-size: 1.5rem; font-weight: 900; color: #fff; text-transform: uppercase; letter-spacing: 0.02em; }
    .pet-layout { display: flex; gap: 40px; padding: 32px 0 80px; align-items: flex-start; }
    .pet-left { flex: 1; }
    .pet-right { flex: 0 0 380px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 28px; position: sticky; top: 120px; }
    .pet-image { width: 100%; border-radius: 4px; margin-bottom: 24px; }
    .pet-progress { margin-bottom: 24px; }
    .pet-progress-bar { height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; margin-bottom: 8px; }
    .pet-progress-fill { height: 100%; background: #5660fe; border-radius: 4px; transition: width 0.5s; }
    .pet-progress-text { display: flex; justify-content: space-between; font-size: 14px; color: rgba(255,255,255,0.6); }
    .pet-progress-text strong { color: #fff; }
    .pet-signers-title { font-size: 14px; font-weight: 800; color: #fff; margin-bottom: 12px; }
    .pet-signer { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 13px; }
    .pet-signer-name { color: rgba(255,255,255,0.7); }
    .pet-signer-state { color: rgba(255,255,255,0.4); }
    .pet-signer-time { color: rgba(255,255,255,0.3); }
    .pet-body { font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.8; margin-bottom: 32px; }
    .pet-body p { margin-bottom: 1.25em; }
    .pet-body strong { color: #fff; font-weight: 700; }
    .pet-body a { color: #5660fe; }
    .pet-section-title { font-size: 20px; font-weight: 900; color: #fff; margin: 32px 0 12px; display: flex; align-items: center; gap: 8px; }
    .pet-recipients { font-size: 15px; color: rgba(255,255,255,0.6); margin-bottom: 24px; }
    .pet-message-preview { border: 1px solid rgba(255,255,255,0.1); padding: 16px; font-size: 14px; color: rgba(255,255,255,0.6); line-height: 1.7; max-height: 200px; overflow-y: auto; margin-bottom: 16px; border-radius: 4px; }
    /* Form */
    .pet-form-title { font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 8px; }
    .pet-form-desc { font-size: 13px; color: rgba(255,255,255,0.4); margin-bottom: 20px; line-height: 1.5; }
    .pet-input { width: 100%; background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 12px 14px; font-size: 14px; margin-bottom: 12px; outline: none; border-radius: 4px; }
    .pet-input:focus { border-color: #5660fe; }
    .pet-input::placeholder { color: rgba(255,255,255,0.3); }
    .pet-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .pet-submit { width: 100%; background: #5660fe; color: #fff; border: none; padding: 14px; font-size: 15px; font-weight: 700; cursor: pointer; border-radius: 4px; margin-top: 8px; transition: background 0.2s; }
    .pet-submit:hover { background: #4850e6; }
    .pet-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); border-radius: 8px; padding: 20px; margin-bottom: 20px; color: #22c55e; font-size: 15px; font-weight: 600; text-align: center; }
    @media (max-width: 768px) {
        .pet-layout { flex-direction: column; }
        .pet-right { flex: auto; position: static; }
    }
</style>
@endsection

@section('body')
<div class="pet-page">
    <div class="pet-banner">
        <div class="pet-banner-title">{{ $petition->title }}</div>
    </div>

    <div class="pet-layout">
        {{-- Left: Content --}}
        <div class="pet-left">
            @if($petition->image)
                <img src="{{ Storage::url($petition->image) }}" class="pet-image" alt="{{ $petition->title }}">
            @endif

            {{-- Progress --}}
            <div class="pet-progress">
                <div class="pet-progress-bar">
                    <div class="pet-progress-fill" style="width: {{ $petition->progress_percent }}%;"></div>
                </div>
                <div class="pet-progress-text">
                    <span><strong>{{ number_format($petition->signature_count) }}</strong> sent</span>
                    <span><strong>{{ number_format($petition->signature_goal) }}</strong> goal</span>
                </div>
            </div>

            {{-- Recent signers --}}
            @if($recentSigners->isNotEmpty())
                <div class="pet-signers-title">Most recent signers:</div>
                @foreach($recentSigners as $signer)
                    <div class="pet-signer">
                        <span class="pet-signer-name">{{ $signer->first_name }} {{ substr($signer->last_name, 0, 1) }}.</span>
                        <span class="pet-signer-state">{{ $signer->state }}</span>
                        <span class="pet-signer-time">{{ $signer->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            @endif

            {{-- Body --}}
            @if($petition->body)
                <div class="pet-body" style="margin-top: 32px;">
                    {!! $petition->body !!}
                </div>
            @endif

            {{-- Recipients --}}
            @if($petition->recipients)
                <div class="pet-section-title">Message Recipients:</div>
                <div class="pet-recipients">{{ $petition->recipients }}</div>
            @endif

            {{-- Suggested message --}}
            @if($petition->suggested_subject || $petition->suggested_message)
                <div class="pet-section-title">Review Message</div>
                <p style="font-size: 14px; color: rgba(255,255,255,0.5); margin-bottom: 16px;">We put in some suggestions, but make the message your own where possible!</p>

                @if($petition->suggested_subject)
                    <div style="font-weight: 700; color: #fff; margin-bottom: 4px;">Subject:</div>
                    <div style="font-size: 15px; color: rgba(255,255,255,0.7); margin-bottom: 16px;">{{ $petition->suggested_subject }}</div>
                @endif

                @if($petition->suggested_message)
                    <div style="font-weight: 700; color: #fff; margin-bottom: 4px;">Body:</div>
                    <div class="pet-message-preview">{{ $petition->suggested_message }}</div>
                @endif
            @endif
        </div>

        {{-- Right: Sign Form --}}
        <div class="pet-right">
            @if(request('signed'))
                <div class="pet-success">Thank you for signing! Your voice matters.</div>
            @endif

            <div class="pet-form-title">Fill out your information</div>
            <div class="pet-form-desc">Fields below are required by many public officials' systems.</div>

            <form method="POST" action="/petition/{{ $petition->slug }}/sign">
                @csrf
                <div class="pet-grid-2">
                    <input type="text" name="first_name" class="pet-input" placeholder="First name *" required>
                    <input type="text" name="last_name" class="pet-input" placeholder="Last name *" required>
                </div>
                <input type="email" name="email" class="pet-input" placeholder="E-mail address *" required>
                <div class="pet-grid-2">
                    <input type="text" name="city" class="pet-input" placeholder="City">
                    <input type="text" name="state" class="pet-input" placeholder="State">
                </div>
                <div class="pet-grid-2">
                    <input type="text" name="zip_code" class="pet-input" placeholder="Zip Code">
                    <input type="text" name="phone" class="pet-input" placeholder="Phone">
                </div>

                @if($petition->suggested_message)
                    <textarea name="custom_message" class="pet-input" rows="4" placeholder="Your message (optional)">{{ $petition->suggested_message }}</textarea>
                @endif

                <button type="submit" class="pet-submit">Sign Petition</button>
            </form>
        </div>
    </div>
</div>
@endsection

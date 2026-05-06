@extends('app')

@section('title'){{ $prisoner->name }} — National Political Prisoner Coalition @endsection

@section('head')
<meta name="description" content="{{ $prisoner->name }}{{ $prisoner->aka ? ' (AKA '.$prisoner->aka.')' : '' }} — {{ substr(strip_tags($prisoner->description ?? ''), 0, 155) }}">
<meta property="og:title" content="{{ $prisoner->name }} — NPPC">
<meta property="og:description" content="{{ substr(strip_tags($prisoner->description ?? ''), 0, 200) }}">
@if($prisoner->photo)<meta property="og:image" content="{{ asset('storage/'.$prisoner->photo) }}">@endif
<meta property="og:url" content="{{ url('/prisoner/'.$prisoner->slug) }}">
<meta property="og:type" content="profile">
<style>
    .prisoner-page { max-width: 1100px; margin: 0 auto; padding: 0 24px; font-family: Avenir, Helvetica, Arial, sans-serif; }
    .prisoner-hero { display: flex; gap: 48px; padding: 48px 0 40px; align-items: flex-start; }
    .prisoner-info { flex: 1; }
    .prisoner-photo-col { flex: 0 0 380px; }
    .prisoner-photo { width: 100%; border-radius: 8px; overflow: hidden; }
    .prisoner-photo img { width: 100%; height: auto; display: block; }
    .prisoner-photo-placeholder { width: 100%; aspect-ratio: 3/4; background: linear-gradient(135deg, #111 0%, #1a1a2e 100%); display: flex; align-items: center; justify-content: center; border-radius: 8px; }
    .prisoner-name { font-size: 3rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 8px; }
    .prisoner-aka { font-size: 16px; color: rgba(255,255,255,0.5); margin-bottom: 24px; font-style: italic; }
    .prisoner-meta { margin-bottom: 32px; }
    .prisoner-meta-row { display: flex; margin-bottom: 6px; font-size: 15px; line-height: 1.5; }
    .prisoner-meta-label { font-weight: 700; color: #fff; min-width: 160px; flex-shrink: 0; }
    .prisoner-meta-value { color: rgba(255,255,255,0.7); }
    .prisoner-status-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
    .prisoner-badge { padding: 4px 14px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; border-radius: 4px; }
    .prisoner-badge-custody { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
    .prisoner-badge-released { background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
    .prisoner-badge-exile { background: rgba(234,179,8,0.15); color: #eab308; border: 1px solid rgba(234,179,8,0.3); }
    .prisoner-badge-trial { background: rgba(59,130,246,0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); }

    /* Counter */
    .prisoner-counter-label { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.5); margin-bottom: 8px; }
    .prisoner-counter-nums { font-size: 1.25rem; font-weight: 900; color: #fff; margin-bottom: 24px; }

    /* Social */
    .prisoner-social { display: flex; gap: 12px; margin-bottom: 24px; }
    .prisoner-social a { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); transition: background 0.15s; }
    .prisoner-social a:hover { background: rgba(255,255,255,0.12); }
    .prisoner-social a svg { width: 16px; height: 16px; fill: #fff; }

    /* Support button */
    .prisoner-support-btn { display: inline-block; background: #5660fe; color: #fff; padding: 12px 32px; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; border-radius: 24px; text-decoration: none; transition: background 0.2s; }
    .prisoner-support-btn:hover { background: #4850e6; }

    /* Divider */
    .prisoner-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 48px 0; }

    /* Biography */
    .prisoner-bio-title { font-size: 2.5rem; font-weight: 900; color: #fff; margin-bottom: 24px; }
    .prisoner-bio-content { font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.8; }
    .prisoner-bio-content p { margin-bottom: 1.25em; }

    /* Cases */
    .prisoner-cases-title { font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 16px; }
    .prisoner-case-card { border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 24px; margin-bottom: 16px; }
    .prisoner-case-inst { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px; }
    .prisoner-case-meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .prisoner-case-field { font-size: 14px; }
    .prisoner-case-field-label { color: rgba(255,255,255,0.5); }
    .prisoner-case-field-value { color: rgba(255,255,255,0.8); }

    /* Tags */
    .prisoner-tags { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px; }
    .prisoner-tag { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); padding: 4px 12px; font-size: 12px; color: rgba(255,255,255,0.6); border-radius: 4px; }

    /* Rich content */
    .prose-content { font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.8; }
    .prose-content p { margin-bottom: 1.25em; }
    .prose-content a { color: #5660fe; text-decoration: underline; }
    .prose-content a:hover { color: #7880ff; }
    .prose-content h2 { font-size: 1.5rem; font-weight: 800; color: #fff; margin: 32px 0 12px; }
    .prose-content h3 { font-size: 1.25rem; font-weight: 700; color: #fff; margin: 24px 0 8px; }
    .prose-content ul, .prose-content ol { margin: 0 0 1.25em 1.5em; }
    .prose-content li { margin-bottom: 0.4em; }
    .prose-content blockquote { border-left: 3px solid #5660fe; padding: 12px 20px; margin: 1.5em 0; color: rgba(255,255,255,0.6); font-style: italic; }
    .prose-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 16px 0; }
    .prose-content iframe, .prose-content embed, .prose-content object { max-width: 100%; margin: 16px 0; border-radius: 8px; }
    .prose-content strong { color: #fff; font-weight: 700; }

    @media (max-width: 768px) {
        .prisoner-hero { flex-direction: column-reverse; }
        .prisoner-photo-col { flex: auto; width: 100%; }
        .prisoner-name { font-size: 2rem; }
        .prisoner-case-meta { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('body')
<div class="prisoner-page">
    {{-- Hero --}}
    <div class="prisoner-hero">
        <div class="prisoner-info">
            <h1 class="prisoner-name">{{ $prisoner->name }}</h1>
            @if($prisoner->aka)
                <div class="prisoner-aka">AKA: {{ $prisoner->aka }}</div>
            @endif

            {{-- Status badges --}}
            <div class="prisoner-status-badges">
                @if($prisoner->in_custody)<span class="prisoner-badge prisoner-badge-custody">In Custody</span>@endif
                @if($prisoner->released)<span class="prisoner-badge prisoner-badge-released">Released</span>@endif
                @if($prisoner->currently_in_exile || $prisoner->in_exile)<span class="prisoner-badge prisoner-badge-exile">In Exile</span>@endif
                @if($prisoner->awaiting_trial)<span class="prisoner-badge prisoner-badge-trial">Awaiting Trial</span>@endif
            </div>

            {{-- Meta info --}}
            <div class="prisoner-meta">
                @if($prisoner->birthdate)
                    <div class="prisoner-meta-row"><span class="prisoner-meta-label">Date of birth:</span><span class="prisoner-meta-value">{{ $prisoner->birthdate->format('d. m. Y') }}</span></div>
                @endif
                @if($prisoner->age)
                    <div class="prisoner-meta-row"><span class="prisoner-meta-label">Age:</span><span class="prisoner-meta-value">{{ $prisoner->age }}{{ $prisoner->death_date ? ' (Deceased)' : '' }}</span></div>
                @endif
                @if($prisoner->gender)
                    <div class="prisoner-meta-row"><span class="prisoner-meta-label">Gender:</span><span class="prisoner-meta-value">{{ $prisoner->gender }}</span></div>
                @endif
                @if($prisoner->race)
                    <div class="prisoner-meta-row"><span class="prisoner-meta-label">Race:</span><span class="prisoner-meta-value">{{ $prisoner->race }}</span></div>
                @endif
                @if($prisoner->era)
                    <div class="prisoner-meta-row"><span class="prisoner-meta-label">Era:</span><span class="prisoner-meta-value">{{ $prisoner->era }}</span></div>
                @endif
                @if($prisoner->inmate_number)
                    <div class="prisoner-meta-row"><span class="prisoner-meta-label">Inmate Number:</span><span class="prisoner-meta-value">#{{ $prisoner->inmate_number }}</span></div>
                @endif
                @if($prisoner->state)
                    <div class="prisoner-meta-row"><span class="prisoner-meta-label">State:</span><span class="prisoner-meta-value">{{ $prisoner->state }}</span></div>
                @endif
                @if($prisoner->address)
                    <div class="prisoner-meta-row"><span class="prisoner-meta-label">Address:</span><span class="prisoner-meta-value">{{ $prisoner->address }}</span></div>
                @endif
            </div>

            {{-- Imprisonment + exile counters (rendered separately when both apply) --}}
            @php
                $totalDays = $prisoner->cases->sum('imprisoned_for_days');
                $totalExileDays = $prisoner->cases->sum('in_exile_for_days');

                $renderCounter = function (int $totalDays, string $label) {
                    if ($totalDays <= 0) return '';
                    $years = intdiv($totalDays, 365);
                    $months = intdiv($totalDays % 365, 30);
                    $days = $totalDays % 30;
                    $nums = '';
                    if ($years > 0) $nums .= $years . ' ' . ($years === 1 ? 'Year' : 'Years') . ' ';
                    if ($months > 0) $nums .= $months . ' ' . ($months === 1 ? 'Month' : 'Months') . ' ';
                    $nums .= $days . ' ' . ($days === 1 ? 'Day' : 'Days');
                    return '<div class="prisoner-counter-label">' . e($label) . ':</div>' .
                           '<div class="prisoner-counter-nums">' . e(trim($nums)) . '</div>';
                };
            @endphp
            @if($totalDays > 0)
                {!! $renderCounter($totalDays, 'Time Imprisoned') !!}
            @endif
            @if($totalExileDays > 0)
                {!! $renderCounter($totalExileDays, 'Time in Exile') !!}
            @endif

            {{-- Social links --}}
            @if($prisoner->website || $prisoner->twitter || $prisoner->facebook || $prisoner->instagram)
                <div class="prisoner-social">
                    @if($prisoner->website)<a href="{{ $prisoner->website }}" target="_blank" title="Website"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg></a>@endif
                    @if($prisoner->twitter)<a href="{{ $prisoner->twitter }}" target="_blank" title="Twitter"><svg viewBox="0 0 24 24"><path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/></svg></a>@endif
                    @if($prisoner->facebook)<a href="{{ $prisoner->facebook }}" target="_blank" title="Facebook"><svg viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg></a>@endif
                    @if($prisoner->instagram)<a href="{{ $prisoner->instagram }}" target="_blank" title="Instagram"><svg viewBox="0 0 24 24"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8 1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/></svg></a>@endif
                </div>
            @endif

            {{-- Support button --}}
            <a href="/prisoner-outreach" class="prisoner-support-btn">Support</a>

            {{-- Ideologies & Affiliation tags --}}
            @if(!empty($prisoner->ideologies) || !empty($prisoner->affiliation))
                <div class="prisoner-tags">
                    @foreach($prisoner->ideologies ?? [] as $ideology)
                        <span class="prisoner-tag">{{ $ideology }}</span>
                    @endforeach
                    @foreach($prisoner->affiliation ?? [] as $aff)
                        <span class="prisoner-tag">{{ $aff }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Photo --}}
        <div class="prisoner-photo-col">
            @if($prisoner->photo)
                <div class="prisoner-photo">
                    <img src="{{ asset('storage/' . $prisoner->photo) }}" alt="{{ $prisoner->name }}">
                </div>
            @else
                <div class="prisoner-photo-placeholder">
                    <img src="/images/no-image-available.png" alt="No image available" style="width:70%; height:auto; opacity:0.8;">
                </div>
            @endif
        </div>
    </div>

    <div class="prisoner-divider"></div>

    {{-- Biography --}}
    @if($prisoner->description)
        <div style="max-width: 800px; padding-bottom: 48px;">
            <h2 class="prisoner-bio-title">Biography</h2>
            <div class="prisoner-bio-content">
                @foreach(explode("\n", $prisoner->description) as $para)
                    @if(trim($para))
                        <p>{{ $para }}</p>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Page Content (rich text body) --}}
    @if($prisoner->body)
        <div style="max-width: 800px; padding-bottom: 48px;">
            <div class="prisoner-bio-content prose-content">
                {!! $prisoner->body !!}
            </div>
        </div>
        <div class="prisoner-divider"></div>
    @endif

    {{-- Podcast Episodes --}}
    @php $episodes = \App\Models\PodcastEpisode::published()->where('prisoner_id', $prisoner->id)->orderBy('sort_order')->get(); @endphp
    @if($episodes->isNotEmpty())
        <div class="prisoner-divider"></div>
        <div style="max-width: 800px;">
            <h2 class="prisoner-cases-title">Related Episodes</h2>
            @include('sections.podcast-player', ['episodes' => $episodes])
        </div>
    @endif

    {{-- Cases --}}
    @if($prisoner->cases->isNotEmpty())
        <div class="prisoner-divider"></div>
        <div style="padding-bottom: 80px;">
            <h2 class="prisoner-cases-title">Case{{ $prisoner->cases->count() > 1 ? 's' : '' }}</h2>
            @foreach($prisoner->cases as $case)
                <div class="prisoner-case-card">
                    @if($case->institution)
                        <div class="prisoner-case-inst">{{ $case->institution->name }}</div>
                    @endif
                    <div class="prisoner-case-meta">
                        @if($case->charges)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Charges: </span><span class="prisoner-case-field-value">{{ $case->charges }}</span></div>@endif
                        @if($case->arrest_date)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Arrested: </span><span class="prisoner-case-field-value">{{ $case->arrest_date->format('M j, Y') }}</span></div>@endif
                        @if($case->sentenced_date)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Sentenced: </span><span class="prisoner-case-field-value">{{ $case->sentenced_date->format('M j, Y') }}</span></div>@endif
                        @if($case->incarceration_date)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Incarcerated: </span><span class="prisoner-case-field-value">{{ $case->incarceration_date->format('M j, Y') }}</span></div>@endif
                        @if($case->release_date)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Released: </span><span class="prisoner-case-field-value">{{ $case->release_date->format('M j, Y') }}</span></div>@endif
                        @if($case->sentence)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Sentence: </span><span class="prisoner-case-field-value">{{ $case->sentence }}</span></div>@endif
                        @if($case->prosecutor)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Prosecutor: </span><span class="prisoner-case-field-value">{{ $case->prosecutor }}</span></div>@endif
                        @if($case->judge)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Judge: </span><span class="prisoner-case-field-value">{{ $case->judge }}</span></div>@endif
                        @if($case->convicted)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Convicted: </span><span class="prisoner-case-field-value">{{ $case->convicted }}</span></div>@endif
                        @if($case->plead)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Plead: </span><span class="prisoner-case-field-value">{{ $case->plead }}</span></div>@endif
                        @if($case->institution && $case->institution->state)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Institution State: </span><span class="prisoner-case-field-value">{{ $case->institution->state }}</span></div>@endif
                        @if($case->institution && $case->institution->security)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Security: </span><span class="prisoner-case-field-value">{{ $case->institution->security }}</span></div>@endif
                        @if($case->institution && $case->institution->mailing_address)<div class="prisoner-case-field"><span class="prisoner-case-field-label">Mailing Address: </span><span class="prisoner-case-field-value">{{ $case->institution->mailing_address }}</span></div>@endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

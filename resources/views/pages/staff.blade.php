@extends('app')

@section('head')
<style>
    .staff-page { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
    .staff-hero { display: flex; gap: 48px; align-items: flex-start; padding: 48px 0 64px; }
    .staff-hero-text { flex: 1; }
    .staff-title { font-size: 4rem; font-weight: 900; color: #fff; margin-bottom: 24px; line-height: 1.05; }
    .staff-intro { font-size: 18px; color: rgba(255,255,255,0.7); line-height: 1.7; }
    .staff-hero-image { flex: 0 0 450px; border-radius: 12px; overflow: hidden; }
    .staff-hero-image img { width: 100%; height: auto; }
    .staff-divider { height: 1px; background: rgba(255,255,255,0.1); margin-bottom: 48px; }
    .staff-filter { margin-bottom: 40px; }
    .staff-filter-label { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.5); margin-bottom: 8px; }
    .staff-filter-btns { display: flex; gap: 8px; flex-wrap: wrap; }
    .staff-filter-btn { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.7); padding: 8px 20px; font-size: 14px; font-weight: 600; cursor: pointer; border-radius: 4px; transition: all 0.2s; text-decoration: none; }
    .staff-filter-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .staff-filter-btn.active { background: #5660fe; border-color: #5660fe; color: #fff; }
    .staff-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; padding-bottom: 80px; }
    .staff-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; display: flex; overflow: hidden; cursor: pointer; transition: background 0.2s, border-color 0.2s; }
    .staff-card:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.15); }
    .staff-card-info { flex: 1; padding: 24px; display: flex; flex-direction: column; justify-content: center; }
    .staff-card-name { font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 4px; }
    .staff-card-position { font-size: 14px; color: rgba(255,255,255,0.55); margin-bottom: 8px; line-height: 1.4; }
    .staff-card-group { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #5660fe; }
    .staff-card-image { flex: 0 0 180px; overflow: hidden; }
    .staff-card-image img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(30%); transition: filter 0.3s; }
    .staff-card:hover .staff-card-image img { filter: grayscale(0); }
    .staff-card-placeholder { width: 100%; height: 100%; min-height: 160px; background: linear-gradient(135deg, #1a1a2e 0%, #2a2a4e 100%); display: flex; align-items: center; justify-content: center; }
    .staff-card-placeholder svg { width: 48px; height: 48px; color: rgba(255,255,255,0.1); }

    /* Modal */
    .staff-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 100000; display: none; align-items: center; justify-content: center; }
    .staff-modal-overlay.open { display: flex; }
    .staff-modal { background: #1a1a2e; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; max-width: 640px; width: 90vw; padding: 32px; display: flex; gap: 24px; align-items: flex-start; position: relative; }
    .staff-modal-close { position: absolute; top: 16px; right: 16px; background: none; border: none; color: rgba(255,255,255,0.5); font-size: 24px; cursor: pointer; }
    .staff-modal-image { width: 120px; height: 120px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
    .staff-modal-name { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 4px; }
    .staff-modal-position { font-size: 14px; color: #5660fe; margin-bottom: 12px; }
    .staff-modal-about { font-size: 15px; color: rgba(255,255,255,0.7); line-height: 1.7; white-space: pre-line; }

    @@media (max-width: 768px) {
        .staff-hero { flex-direction: column; }
        .staff-hero-image { flex: auto; width: 100%; }
        .staff-grid { grid-template-columns: 1fr; }
        .staff-title { font-size: 2.5rem; }
        .staff-modal { flex-direction: column; align-items: center; text-align: center; }
    }
</style>
@endsection

@section('body')
<div class="staff-page">
    {{-- Hero --}}
    <div class="staff-hero">
        <div class="staff-hero-text">
            <h1 class="staff-title">Our Staff</h1>
            <p class="staff-intro">Our team includes advocates, researchers, organizers, and policy experts dedicated to fighting for the rights of political prisoners across the United States.</p>
        </div>
        <div class="staff-hero-image">
            <div style="width:100%; height:300px; background:linear-gradient(135deg, #0a0a1a 0%, #1a1040 50%, #5660fe 100%); border-radius:12px; display:flex; align-items:center; justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="rgba(255,255,255,0.1)" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            </div>
        </div>
    </div>

    <div class="staff-divider"></div>

    {{-- Staff Grid --}}
    @if (count($staff) > 0)
    <div class="staff-grid">
        @foreach ($staff as $member)
            <div class="staff-card" onclick="openStaffModal('{{ $member->id }}')">
                <div class="staff-card-info">
                    <div class="staff-card-name">{{ $member->name }}</div>
                    @if($member->position)
                        <div class="staff-card-position">{{ $member->position }}</div>
                    @endif
                    <div class="staff-card-group">{{ $member->group === 'board' ? 'Board' : 'Staff' }}</div>
                </div>
                <div class="staff-card-image">
                    @if($member->image)
                        <img src="/storage/{{ $member->image }}" alt="{{ $member->name }}" loading="lazy" decoding="async">
                    @else
                        <div class="staff-card-placeholder">
                            <img src="/images/no-image-available.png" alt="No image available" style="width:60%; height:auto; opacity:0.8;">
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @else
        @include('sections.coming-soon', ['message' => 'Our staff will be listed here soon. Please check back shortly.'])
    @endif
</div>

{{-- Modal --}}
<div class="staff-modal-overlay" id="staff-modal-overlay" onclick="closeStaffModal()">
    <div class="staff-modal" onclick="event.stopPropagation()">
        <button class="staff-modal-close" onclick="closeStaffModal()">&times;</button>
        <img id="staff-modal-image" class="staff-modal-image" src="" alt="">
        <div>
            <div id="staff-modal-name" class="staff-modal-name"></div>
            <div id="staff-modal-position" class="staff-modal-position"></div>
            <div id="staff-modal-about" class="staff-modal-about"></div>
        </div>
    </div>
</div>

<script>
    var staffData = @json($staff);

    function openStaffModal(id) {
        var member = staffData.find(function(m) { return m.id === id; });
        if (!member) return;
        document.getElementById('staff-modal-image').src = member.image ? '/storage/' + member.image : '';
        document.getElementById('staff-modal-image').style.display = member.image ? 'block' : 'none';
        document.getElementById('staff-modal-name').textContent = member.name;
        document.getElementById('staff-modal-position').textContent = member.position || '';
        document.getElementById('staff-modal-about').textContent = member.about || '';
        document.getElementById('staff-modal-overlay').classList.add('open');
    }

    function closeStaffModal() {
        document.getElementById('staff-modal-overlay').classList.remove('open');
    }
</script>
@endsection

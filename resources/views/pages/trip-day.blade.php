<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Day {{ $day }} - {{ $info['title'] }} | London Trip</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #1a1a2e;
            --secondary: #16213e;
            --accent: #e94560;
            --gold: #d4af37;
            --light: #f8f9fa;
            --text: #333;
            --text-light: #666;
            --border: #e0e0e0;
        }
        body {
            font-family: 'DM Sans', sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .back-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .back-link:hover { color: white; }
        .day-badge {
            background: linear-gradient(135deg, var(--accent) 0%, #ff6b9d 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            position: relative;
            z-index: 1;
        }
        .subtitle {
            font-size: 1.1rem;
            opacity: 0.8;
            position: relative;
            z-index: 1;
            margin-top: 4px;
        }
        .birthday-badge {
            background: linear-gradient(135deg, var(--gold) 0%, #ffd700 100%);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            position: relative;
            z-index: 1;
            margin-top: 12px;
        }
        main { padding: 40px; }
        .nav-days {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 32px;
        }
        .nav-day {
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--text-light);
            background: var(--light);
            transition: all 0.2s;
        }
        .nav-day:hover { background: #e0e0e0; }
        .nav-day.active {
            background: var(--accent);
            color: white;
        }

        /* Attachments section */
        .attachments-section {
            margin-top: 40px;
            padding-top: 32px;
            border-top: 2px solid var(--border);
        }
        .attachments-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        .attachments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
        }
        .attachment-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .attachment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .attachment-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .attachment-card .pdf-preview {
            width: 100%;
            height: 200px;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        .attachment-card .label {
            padding: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--primary);
        }
        .attachment-card a {
            display: block;
            text-decoration: none;
            color: inherit;
        }

        .empty-state {
            text-align: center;
            color: var(--text-light);
            padding: 40px;
            font-style: italic;
        }

        footer {
            background: var(--primary);
            color: rgba(255,255,255,0.7);
            text-align: center;
            padding: 24px;
            font-size: 0.9rem;
        }

        @media (max-width: 600px) {
            body { padding: 16px 8px; }
            header { padding: 28px 20px; }
            h1 { font-size: 1.8rem; }
            main { padding: 24px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-top">
                <a href="/london" class="back-link">&larr; Full Itinerary</a>
                <span class="day-badge">Day {{ $day }}</span>
            </div>
            <h1>{{ $info['title'] }}</h1>
            <p class="subtitle">{{ $info['subtitle'] }}</p>
            @if(!empty($info['birthday']))
                <span class="birthday-badge">🎂 BIRTHDAY!!!</span>
            @endif
        </header>

        <main>
            {{-- Day navigation --}}
            <div class="nav-days">
                @foreach($allDays as $num => $d)
                    <a href="/london/day/{{ $num }}" class="nav-day {{ $num === $day ? 'active' : '' }}">
                        Day {{ $num }}
                    </a>
                @endforeach
            </div>

            {{-- Attachments --}}
            <div class="attachments-section" style="border-top: none; margin-top: 0; padding-top: 0;">
                <h2 class="attachments-title">Bookings & Confirmations</h2>

                @if($attachments->isEmpty())
                    <div class="empty-state">
                        No attachments yet. Upload booking confirmations and tickets from the admin panel.
                    </div>
                @else
                    <div class="attachments-grid">
                        @foreach($attachments as $attachment)
                            <div class="attachment-card">
                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank">
                                    @if(str_starts_with($attachment->file_type, 'image') || str_ends_with($attachment->file_path, '.jpg') || str_ends_with($attachment->file_path, '.png') || str_ends_with($attachment->file_path, '.jpeg'))
                                        <img src="{{ Storage::url($attachment->file_path) }}" alt="{{ $attachment->label ?? 'Attachment' }}">
                                    @else
                                        <div class="pdf-preview">📄</div>
                                    @endif
                                    <div class="label">{{ $attachment->label ?? 'View attachment' }}</div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </main>

        <footer>
            <p>London Adventure 🇬🇧 April 27 - May 2</p>
        </footer>
    </div>
</body>
</html>

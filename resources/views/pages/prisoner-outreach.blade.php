@extends('app')

@section('head')
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700;900&display=swap" rel="stylesheet">
<style>
.outreach-page {
  --outreach-accent: #5660fe;
  --outreach-gray: rgba(255,255,255,0.7);
  --outreach-gray-dim: rgba(255,255,255,0.4);
  font-family: 'Source Sans 3', 'Verlag', Helvetica, Arial, sans-serif;
}

.outreach-container { width: 1080px; margin: 0 auto; max-width: 100%; padding: 0 16px; }

.outreach-intro {
  max-width: 700px;
  margin: 32px 0 48px;
}
.outreach-intro p {
  font-size: 18px;
  color: var(--outreach-gray);
  line-height: 1.6;
  margin-bottom: 16px;
}

.outreach-section {
  display: flex;
  gap: 60px;
  align-items: flex-start;
  margin-bottom: 80px;
}

.outreach-guide { flex: 1; }
.outreach-guide h2 {
  font-size: 28px;
  font-weight: 900;
  margin-bottom: 16px;
  color: #fff;
}
.outreach-guide > p {
  font-size: 16px;
  color: var(--outreach-gray);
  line-height: 1.7;
  margin-bottom: 12px;
}
.outreach-guide ul {
  list-style: none;
  margin: 16px 0;
  padding: 0;
}
.outreach-guide li {
  font-size: 16px;
  color: var(--outreach-gray);
  padding: 8px 0;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  padding-left: 20px;
  position: relative;
}
.outreach-guide li::before {
  content: '\2192';
  position: absolute;
  left: 0;
  color: var(--outreach-accent);
}

/* 3D Postcard */
.postcard-wrapper {
  flex: 0 0 480px;
  perspective: 1000px;
}
.postcard {
  position: relative;
  width: 480px;
  height: 340px;
  background: #f5f0e8;
  border-radius: 4px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
  transform-style: preserve-3d;
  transition: transform 0.15s ease-out;
  cursor: default;
  overflow: hidden;
}
.postcard::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.06'/%3E%3C/svg%3E");
  pointer-events: none;
}
.postcard-inner {
  position: relative;
  width: 100%;
  height: 100%;
  padding: 28px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}
.postcard-left {
  border-right: 1px solid #c5bfb0;
  padding-right: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.postcard-title {
  font-family: 'Source Sans 3', sans-serif;
  font-size: 22px;
  font-weight: 900;
  color: #333;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}
.postcard-instructions { margin-top: 12px; }
.postcard-instructions p {
  font-size: 12px;
  color: #666;
  line-height: 1.5;
  margin-bottom: 8px;
}
.postcard-instructions strong { color: #333; }
.postcard-lines { margin-top: auto; }
.postcard-line {
  height: 1px;
  background: #d0caba;
  margin-bottom: 18px;
  background-image: repeating-linear-gradient(90deg, #d0caba 0px, #d0caba 3px, transparent 3px, transparent 6px);
}
.postcard-right {
  padding-left: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.postcard-stamp {
  width: 60px;
  height: 60px;
  border: 2px dashed #aaa;
  border-radius: 50%;
  align-self: flex-end;
  position: relative;
}
.postcard-stamp::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 40px;
  height: 4px;
  background: repeating-linear-gradient(90deg, #aaa 0px, #aaa 8px, transparent 8px, transparent 12px);
}
.postcard-address { margin-top: auto; }
.postcard-address-label {
  font-size: 13px;
  color: #999;
  margin-bottom: 6px;
}
.postcard-address-line {
  height: 1px;
  background-image: repeating-linear-gradient(90deg, #d0caba 0px, #d0caba 3px, transparent 3px, transparent 6px);
  margin-bottom: 16px;
}
.postcard-shadow {
  position: absolute;
  inset: 0;
  border-radius: 4px;
  pointer-events: none;
  transition: box-shadow 0.15s ease-out;
}

@@media (max-width: 900px) {
  .outreach-section { flex-direction: column-reverse; }
  .postcard-wrapper { flex: auto; width: 100%; }
  .postcard { width: 100%; max-width: 480px; }
}

/* Write-a-letter form */
.letter-form-section {
  max-width: 720px;
  margin: 40px 0 80px;
}
.letter-form-section h2 {
  font-size: 28px;
  font-weight: 900;
  text-transform: uppercase;
  color: #fff;
  margin-bottom: 12px;
  letter-spacing: 0.04em;
}
.letter-form-section .letter-intro {
  font-size: 16px;
  color: var(--outreach-gray);
  line-height: 1.7;
  margin-bottom: 28px;
}
.letter-form label {
  display: block;
  font-size: 16px;
  font-weight: 700;
  color: #fff;
  margin: 24px 0 8px;
}
.letter-form select,
.letter-form textarea {
  width: 100%;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 4px;
  color: #fff;
  font-family: inherit;
  font-size: 15px;
  padding: 10px 12px;
  box-sizing: border-box;
}
.letter-form select { max-width: 320px; }
.letter-form textarea {
  min-height: 220px;
  resize: vertical;
  line-height: 1.5;
}
.letter-form select:focus,
.letter-form textarea:focus {
  outline: none;
  border-color: var(--outreach-accent);
}
/* Native dropdown options render on the OS surface — force dark
   text on a light background so they're legible. */
.letter-form select option {
  color: #111;
  background: #fff;
}
.letter-form .letter-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 24px;
  flex-wrap: wrap;
}
.letter-form .letter-donate { max-width: 220px; }
.letter-form button[type=submit] {
  background: #fff;
  color: #111;
  border: none;
  padding: 12px 28px;
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  border-radius: 2px;
  cursor: pointer;
  transition: background 0.2s;
}
.letter-form button[type=submit]:hover { background: #ddd; }
.letter-form .letter-success {
  background: rgba(86,96,254,0.12);
  border: 1px solid rgba(86,96,254,0.4);
  color: #fff;
  padding: 16px 20px;
  border-radius: 4px;
  margin-bottom: 24px;
}
</style>
@endsection

@section('body')
<div class="outreach-page">
  <div class="outreach-container">
    <h1 class="text-6xl font-black text-white mb-4 mt-12">Prisoner Outreach</h1>
    <div class="line"></div>

    <div class="outreach-intro">
      <p>Writing a letter to a political prisoner is one of the most direct and meaningful actions you can take. Letters provide comfort, reduce isolation, and remind prisoners that they are not forgotten.</p>
    </div>

    <div class="outreach-section">
      <div class="outreach-guide">
        <h2>How to Write a Letter</h2>
        <p>Many political prisoners have been separated from their communities for years or decades. A thoughtful letter can make an enormous difference.</p>
        <ul>
          <li>Use the prisoner's full legal name and identification number</li>
          <li>Include your return address (required by most facilities)</li>
          <li>Write on plain white paper — many facilities reject colored paper or cards</li>
          <li>Avoid staples, paper clips, or stickers</li>
          <li>Do not include photographs unless confirmed acceptable</li>
          <li>Keep your tone warm, respectful, and supportive</li>
          <li>Ask about their interests, reading, and how they are doing</li>
          <li>Mail via USPS — many facilities do not accept other carriers</li>
        </ul>
        <p style="margin-top: 20px;">Visit our <a href="/database" style="color: #5660fe; text-decoration: underline;">prisoner profiles</a> to find mailing addresses and facility-specific guidelines for each political prisoner.</p>
      </div>

      <div class="postcard-wrapper">
        <div class="postcard" id="postcard">
          <div class="postcard-inner">
            <div class="postcard-left">
              <div>
                <div class="postcard-title">Postcard</div>
                <div class="postcard-instructions">
                  <p><strong>Write to a political prisoner.</strong></p>
                  <p>Your words of solidarity and support can provide comfort to someone who has been separated from their community.</p>
                </div>
              </div>
              <div class="postcard-lines">
                <div class="postcard-line"></div>
                <div class="postcard-line"></div>
                <div class="postcard-line"></div>
                <div class="postcard-line"></div>
              </div>
            </div>
            <div class="postcard-right">
              <div class="postcard-stamp"></div>
              <div class="postcard-address">
                <div class="postcard-address-label">to:</div>
                <div class="postcard-address-line"></div>
                <div class="postcard-address-line"></div>
                <div class="postcard-address-label">from:</div>
                <div class="postcard-address-line"></div>
              </div>
            </div>
          </div>
          <div class="postcard-shadow"></div>
        </div>
      </div>
    </div>

    <div class="letter-form-section">
      <h2>Write a Letter to a Prisoner</h2>
      <p class="letter-intro">Type up a solidarity message for someone in the list below and we will post it for you. Include your address in the message if you want to give them a chance to write back, or leave details out to keep your letter anonymous. Please consider a small donation to help cover the cost of printing, envelopes, and stamps.</p>

      @if(request('form_submitted'))
        <div class="letter-success">Thank you — your letter has been received. We'll print and mail it on your behalf.</div>
      @endif

      <form class="letter-form" method="POST" action="/form/prisoner-letter">
        @csrf

        <label for="letter-prisoner">Prisoner Name</label>
        <select id="letter-prisoner" name="prisoner_name" required>
          @forelse($prisoners as $p)
            <option value="{{ $p->name }}">{{ $p->name }}</option>
          @empty
            <option value="" disabled>No prisoners currently listed as in custody.</option>
          @endforelse
        </select>

        <label for="letter-message">Your Message</label>
        <textarea id="letter-message" name="message" required placeholder="Write your message of solidarity here…"></textarea>

        <div class="letter-actions">
          <select class="letter-donate" name="donation_amount" aria-label="Optional donation">
            <option value="">No donation</option>
            <option value="5">Donate $5</option>
            <option value="10">Donate $10</option>
            <option value="25">Donate $25</option>
            <option value="50">Donate $50</option>
          </select>
          <button type="submit">Send</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const card = document.getElementById('postcard');
  if (!card) return;
  const wrapper = card.parentElement;

  wrapper.addEventListener('mousemove', function (e) {
    const rect = wrapper.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotateX = ((y - centerY) / centerY) * -8;
    const rotateY = ((x - centerX) / centerX) * 8;

    card.style.transform = 'rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg)';
    card.querySelector('.postcard-shadow').style.boxShadow =
      (-rotateY * 2) + 'px ' + (rotateX * 2) + 'px 40px rgba(0,0,0,0.4)';
  });

  wrapper.addEventListener('mouseleave', function () {
    card.style.transform = 'rotateX(0) rotateY(0)';
    card.querySelector('.postcard-shadow').style.boxShadow = '0 20px 60px rgba(0,0,0,0.5)';
  });
});
</script>
@endsection

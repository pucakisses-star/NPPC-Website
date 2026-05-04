@php use App\Models\SiteSetting; @endphp
@php
    $heading = SiteSetting::get('callout_heading', 'Support the National Political Prisoner Coalition');
    $text = SiteSetting::get('callout_text', "Your contributions directly help us provide vital support to political prisoners across the United States. With your donation, we can continue to fight for justice, provide legal aid, and assist families in need.\n\nJoin us in making a difference. Every donation, no matter the size, brings us closer to achieving our mission.");
    $buttonLabel = SiteSetting::get('callout_button_label', 'Donate now');
    $buttonUrl = SiteSetting::get('callout_button_url', '/donate');
@endphp

<section class="callout rounded mb-8 mt-4">
    <div class="inner block md:flex justify-between p-8">
        <div class="w-full md:w-1/2 md:pr-16 pr-4 md:py-16 py-8">
            <div class="heading">
                <span>{{ $heading }}</span>
            </div>
        </div>
        <div class="w-full md:w-1/2 pt-8 md:pt-16">
            <div>
                @foreach(explode("\n\n", $text) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
                <a href="{{ $buttonUrl }}" class="btn">{{ $buttonLabel }}</a>
            </div>
        </div>
    </div>
</section>

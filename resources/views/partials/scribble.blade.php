{{-- Scribble text decoration span. Wrap a single word in a heading:
     @include('partials.scribble', ['word' => 'Free'])
     Requires @include('partials.scribble-assets') once in the page head.
     A hand-drawn oval circles the word on hover / keyboard focus; the
     oval SVG is built to fit the word by the shared assets script. --}}
<span class="scr" tabindex="0">{{ $word }}</span>@php /* no trailing whitespace */ @endphp

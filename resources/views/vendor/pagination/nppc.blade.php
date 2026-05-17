@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" style="display:flex; align-items:center; justify-content:center; gap:8px; flex-wrap:wrap; margin-top:48px; color:#fff;">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="padding:8px 14px; border:1px solid rgba(255,255,255,0.12); border-radius:4px; color:rgba(255,255,255,0.3); cursor:not-allowed;">‹ Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" wire:click.prevent="previousPage" rel="prev"
               style="padding:8px 14px; border:1px solid rgba(255,255,255,0.2); border-radius:4px; color:#fff; text-decoration:none; transition:all .15s;"
               onmouseover="this.style.background='rgba(86,96,254,0.15)';this.style.borderColor='#5660fe'"
               onmouseout="this.style.background='transparent';this.style.borderColor='rgba(255,255,255,0.2)'">
                ‹ Prev
            </a>
        @endif

        {{-- Numbered Links --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding:8px 6px; color:rgba(255,255,255,0.5);">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="padding:8px 14px; border:1px solid #5660fe; background:#5660fe; border-radius:4px; color:#fff; font-weight:700; min-width:40px; text-align:center;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" wire:click.prevent="gotoPage({{ $page }})"
                           style="padding:8px 14px; border:1px solid rgba(255,255,255,0.2); border-radius:4px; color:#fff; text-decoration:none; min-width:40px; text-align:center; transition:all .15s;"
                           onmouseover="this.style.background='rgba(86,96,254,0.15)';this.style.borderColor='#5660fe'"
                           onmouseout="this.style.background='transparent';this.style.borderColor='rgba(255,255,255,0.2)'">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" wire:click.prevent="nextPage" rel="next"
               style="padding:8px 14px; border:1px solid rgba(255,255,255,0.2); border-radius:4px; color:#fff; text-decoration:none; transition:all .15s;"
               onmouseover="this.style.background='rgba(86,96,254,0.15)';this.style.borderColor='#5660fe'"
               onmouseout="this.style.background='transparent';this.style.borderColor='rgba(255,255,255,0.2)'">
                Next ›
            </a>
        @else
            <span style="padding:8px 14px; border:1px solid rgba(255,255,255,0.12); border-radius:4px; color:rgba(255,255,255,0.3); cursor:not-allowed;">Next ›</span>
        @endif

        <span style="margin-left:16px; color:rgba(255,255,255,0.5); font-size:14px;">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>
    </nav>
@endif

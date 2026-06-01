@php use App\Models\Page; @endphp
@extends('app')

@section('body')

    <h1 class="text-6xl mt-12 mb-12"> Board of directors</h1>
    <div class="line mt-8 mb-12"></div>


    @php
        $page = Page::where('slug', 'board-of-directors')->first();
        $hasBody = $page && trim(strip_tags($page->body ?? '')) !== '';
        $hasContent = count($directors ?? []) > 0 || $hasBody;
    @endphp

    @if ($hasContent)

    @if($hasBody)
        @markdom($page->body)
    @endif

    <div class="container mx-auto px-4 my-24">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($directors as $member)
                <div
                        class="relative cursor-pointer group"
                        onclick="openModal('{{ $member->id }}')"
                >
                    <img
                            src="/storage/{{ $member->image }}"
                            alt="{{ $member->name }}"
                            class="w-full h-[244px] object-cover border-2 border-gray-700 rounded transition duration-300 filter grayscale group-hover:grayscale-0"
                    >
                    <!-- Overlay -->
                    <div
                            class="relative pt-3 transition duration-300 opacity-90 group-hover:opacity-100"
                    >
                        <h2 class="text-lg font-semibold text-gray-200 mb-0">{{ $member->name }}</h2>
                        <h2 class="text-md text-gray-600 mb-0">{{ $member->position }}</h2>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal Template -->
    <div
            id="modal-overlay"
            class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center"
            style="z-index: 10000;"
            onclick="closeModal()"
    >
        <div
                id="modal-content"
                class="bg-gray-900 rounded-lg shadow-xl max-w-2xl w-full p-6 flex space-x-6 items-start relative border border-gray-700"
                onclick="event.stopPropagation()"
        >
            <img
                    id="modal-image"
                    src=""
                    alt="Staff Image"
                    class="w-24 h-24 rounded object-cover border-2 border-gray-700"
            >
            <div>
                <h2 id="modal-name" class="text-xl font-bold mb-2 text-white"></h2>
                <p id="modal-about" class="text-gray-300 whitespace-pre-line"></p>
            </div>
        </div>
    </div>

    <script>
        const staffData = @json($directors);

        function openModal(id) {
            const member = staffData.find(member => member.id === id);
            if (member) {
                document.getElementById('modal-image').src = `/storage/${member.image}`;
                document.getElementById('modal-name').textContent = member.name;
                document.getElementById('modal-about').textContent = member.about;
                document.getElementById('modal-overlay').classList.remove('hidden');
                document.getElementById('modal-overlay').classList.add('flex');
            }
        }

        function closeModal() {
            document.getElementById('modal-overlay').classList.add('hidden');
            document.getElementById('modal-overlay').classList.remove('flex');
        }
    </script>
    @else
        {{-- No board members listed and no page body yet — show a placeholder
             instead of an empty page. --}}
        <div class="container mx-auto px-4 py-24 md:py-32 text-center">
            <svg class="mx-auto mb-6 h-14 w-14 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>
            </svg>
            <p class="text-sm font-semibold uppercase tracking-widest text-gray-500 mb-3">Under construction</p>
            <h2 class="text-3xl md:text-4xl font-semibold text-gray-200 mb-4">Coming soon</h2>
            <p class="text-lg text-gray-500 max-w-xl mx-auto">Our board of directors will be announced here soon. Please check back shortly.</p>
        </div>
    @endif
@endsection

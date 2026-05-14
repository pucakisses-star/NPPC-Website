@php use App\Models\Page; @endphp
@extends('app')

@section('body')

    <h1 class="text-6xl mt-12 mb-12"> Board of directors</h1>
    <div class="line mt-8 mb-12"></div>


    @php
    $page = Page::where('slug', 'board-of-directors')->first();
    @endphp

    @if($page && $page->body)
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
@endsection

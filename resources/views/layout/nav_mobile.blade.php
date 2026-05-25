@php use App\Views\ViewSupport; @endphp
@php
$menuItems = ViewSupport::getMenuItems();
@endphp
<nav class="fixed top-0 w-full bg-black md:bg-black-500 lg:bg-opacity-40 md:backdrop-filter backdrop-blur  z-[99999] md:hidden">

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex h-16 justify-between">
                <div class="-ml-2 mr-2 flex items-center md:hidden">
                    <!-- Mobile menu button -->
                    <button id="toggle-hamburger" type="button" class="inline-flex items-center justify-center rounded-md p-3 text-gray-300 hover:bg-slate-900 hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false" aria-label="Toggle menu" style="min-width:44px; min-height:44px;">
                        <span class="sr-only">Open main menu</span>
                        <!--
                          Icon when menu is closed.

                          Menu open: "hidden", Menu closed: "block"
                        -->
                        <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <!--
                          Icon when menu is open.

                          Menu open: "block", Menu closed: "hidden"
                        -->
                        <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex flex-shrink-0 items-center">
                    <a href="/"><img src="/logo.svg" alt="Logo" class="block h-14 w-auto"/></a>
                </div>
            <div class="hidden md:ml-6 md:flex md:space-x-8">
                <!-- Current: "border-indigo-500 text-gray-900", Default: "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700" -->
                @foreach($menuItems as $item)
                    <div class="inline-flex relative menu-item">
                        <a href="{{$item->href}}" class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white {{ $item->active ? 'border-indigo-500' : 'border-transparent hover:border-gray-300 hover:border-indigo-500' }}">{{$item->title}}</a>
                        @if($item->children)
                            <div class=" menu-item-children  absolute left-1/2 z-10 -mt-1  w-screen max-w-min -translate-x-1/2 top-12 px-4">
                                <div class="w-56 shrink border border-6 border-indigo-600 rounded-xl bg-slate-900 p-2 text-sm font-semibold leading-6 text-gray-900 shadow-lg ring-1 ring-gray-900/5">
                                   @foreach($item->children as $child)
                                        <a href="{{$child->href}}" class="block text-white p-2 hover:bg-slate">{{$child->title}}</a>
                                   @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
                <div class="flex-shrink-0">
                    <a href="/donate" class="relative inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-8 mt-2 py-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Donate
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div id="mobile-menu" style="display: none; max-height: calc(100vh - 80px); overflow-y: auto;">
        <div class="space-y-1 pb-3 pt-2">
            @foreach($menuItems as $item)
                <a href="{{$item->href}}" class="block border-l-4 border-transparent py-4 pl-4 pr-4 text-base font-medium text-gray-300 hover:border-indigo-500 hover:bg-slate-900 hover:text-white sm:pl-5 sm:pr-6" style="min-height:44px;">{{$item->title}}</a>
                @if($item->children)
                    @foreach($item->children as $child)
                        <a href="{{$child->href}}" class="block border-l-4 border-transparent py-3 pl-8 pr-4 text-sm font-medium text-gray-400 hover:border-indigo-500 hover:bg-slate-900 hover:text-white sm:pl-10 sm:pr-6" style="min-height:44px;">&rsaquo;&nbsp;{{$child->title}}</a>
                    @endforeach
                @endif
            @endforeach
        </div>
        <div class="mt-2 px-4 pb-4">
            <a href="/donate" class="relative block w-full text-center bg-indigo-600 px-8 py-4 text-base font-semibold text-white rounded-md shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Donate
            </a>
        </div>
    </div>

</nav>

<div class="w-full h-[120px] md:hidden"></div>


<script>
    document.getElementById('toggle-hamburger').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenu.style.display === 'none' || mobileMenu.style.display === '') {
            mobileMenu.style.display = 'block';
        } else {
            mobileMenu.style.display = 'none';
        }
    });

</script>

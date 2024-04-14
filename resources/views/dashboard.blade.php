<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('DANA-AUTOGROUP') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("База запчастин") }}
                    <div>
                        <button type="button"
                                class="items-center px-4 py-3 bg-blue-600 font-semibold text-xs text-white uppercase hover:bg-red-500 "
                                id="feel-cats" onclick="FeelCats()">
                            Заповнити категорії
                        </button>
                    </div>
                </div>
                <div class="p-6 text-gray-900 dark:text-gray-100" id="watch">
                    <input type="text" placeholder="Артикул" id="search-article">
{{--                    <input type="text" placeholder="Виробник" id="search-brand">--}}
                    <button type="button"
                            class="items-center px-4 py-3 bg-blue-600 font-semibold text-xs text-white uppercase hover:bg-red-500 "
                            id="search" onclick="SearchDetail()">Шукати
                    </button>
                    <button type="button"
                            class="items-center px-4 py-3 bg-yellow-600 font-semibold text-xs text-white uppercase hover:bg-gray-600 clean" onclick="Clean()">
                        Скинути
                    </button>
                </div>
                <div class="brands"></div>
                <div class="p-6 text-gray-900 dark:text-gray-100 catalogue">
                    @include('layouts.table')
                    {{ $list->links() }}
                </div>
            </div>
        </div>
    </div>
    <div id="modal1" class="modal">
        Зачекайте
    </div>
    <div class="back"></div>
</x-app-layout>

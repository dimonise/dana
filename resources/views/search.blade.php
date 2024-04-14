<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('DANA-AUTOGROUP') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if(request()->routeIs('new-order'))
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        {{ __("Покупець") }}
                    </div>
                    @include('layouts.user')
                @endif
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-1" id="watch">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("Підбір по артикулу") }}
                </div>
                <input type="text" placeholder="Артикул" id="search-article">
                {{--                    <input type="text" placeholder="Виробник" id="search-brand">--}}
                <button type="button"
                        class="items-center px-4 py-3 bg-blue-600 font-semibold text-xs text-white uppercase hover:bg-red-500 "
                        id="search" onclick="SearchDetail()">Шукати
                </button>
                <button type="button"
                        class="items-center px-4 py-3 bg-yellow-600 font-semibold text-xs text-white uppercase hover:bg-gray-600 clean"
                        onclick="Clean()">
                    Скинути
                </button>
            </div>
            <div class="brands"></div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-1">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("Підбір по авто") }}
                </div>
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form id="select-car">
                        <select id="year" onchange="getMark()">
                            <option value="">Оберіть рік випуску</option>
                            @for($i = 1940; $i < (date('Y', time()) + 1); $i++)
                                <option value="{!! $i !!}">{!! $i !!}</option>
                            @endfor
                        </select>
                        <select id="mark" onchange="getModel()">
                            <option value="">Оберіть марку</option>
                        </select>
                        <select id="model" onchange="getBody()">
                            <option value="">Оберіть модель</option>
                        </select>
                        <select id="body" onchange="getEngine()">
                            <option value="">Оберіть тип кузову</option>
                        </select>
                        <select id="engine" onchange="getModif()">
                            <option value="">Оберіть двигун</option>
                        </select>
                        <select id="modif" onchange="getResultAuto()">
                            <option value="">Оберіть модифікацію</option>
                        </select>
                    </form>
                    <div id="details-result"></div>
                    <div id="search-result"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<div id="modal1" class="modal">
    Зачекайте
</div>
<div class="back"></div>

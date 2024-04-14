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
                    <button type="button" class="items-center px-4 py-3 bg-blue-600 font-semibold text-xs text-white uppercase hover:bg-red-500">{{ __("Створити нове Замовлення") }}</button>
                </div>
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="table" id="details-table">
                        <thead>
                        <th>№</th>
                        <th>Офіс</th>
                        <th>Статус</th>
                        <th>Замовник</th>
                        <th>Телефон</th>
                        <th>Сума</th>
                        <th>Доставка</th>
                        <th>Коментар</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        </thead>
                        <tbody>
                        @foreach($orders as $key=>$order)
                        <tr>
                            <td>{!! $order->order_id !!}</td>
                            <td>{!! $order->office_id !!}</td>
                            <td>{!! $order->status !!}</td>
                            <td>{!! $order->name_client !!} {!! $order->sname_client !!}</td>
                            <td>{!! $order->phone !!}</td>
                            <td>{!! $order->price !!}</td>
                            <td>{!! $order->delivery_address !!} ({!! $order->delivery_cost !!} грн.)</td>
                            <td></td>
                            <td><i class="fas fa-print" aria-hidden="true"></i></td>
                            <td><i class="fas fa-edit" aria-hidden="true"></i></td>
                            <td><i class="fas fa-trash" aria-hidden="true"></i></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

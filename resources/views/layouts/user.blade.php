<div class=text-gray-900 dark:text-gray-100">
    <form id="user-info">
        <input type="text" id="user-name" placeholder="Ім'я">
        <input type="text" id="user-sname" placeholder="Прізвище">
        <input type="text" id="user-phone" placeholder="Телефон">
        <input type="hidden" id="type-delivery" >
        <input type="hidden" id="type-delivery-office" >
        <select id="user-delivery-type" onchange="getCity()">
            <option>Обрати тип доставки</option>
            @foreach($deliveries as $delivery)
                <option value="{!! $delivery->id !!}">{!! $delivery->name !!}</option>
            @endforeach
        </select>
        <select id="user-city" onchange="getNPwarhouse()">
            <option>Обрати місто доставки</option>
            @if(isset($cities))
                @foreach($cities as $city)
                    <option value="{!! $city->id !!}">{!! $delivery->name !!}</option>
                @endforeach
            @endif
        </select>
        <select id="user-np">
            <option>Обрати пункт видачі</option>
        </select>
    </form>
</div>
<form id="order-info">
    <button type="button" class="buy items-center px-4 py-3 bg-green-600 font-semibold text-xs text-white uppercase hover:bg-yellow-500" style="display: none" onclick="addInOrder()">ОФОРМИТИ ЗАМОВЛЕННЯ</button>
    <label for="summ" style="color:#ffffff;display: none" class="buy">Загальна сума замовлення</label><input type="text" readonly class="buy" style="display: none" value="" id="summ">
</form>
<div class="p-6 text-gray-900 dark:text-gray-100 catalogue"></div>

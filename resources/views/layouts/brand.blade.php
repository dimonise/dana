<table class="table" id="details-table">
    <th>Артикул</th>
    <th>Виробник</th>
    <th>Найменування</th>
    <th></th>
    @foreach($list as $key => $value)
        <tr>
            <td>{!! $value->article !!}</td>
            <td>{!! $value->brand !!}</td>
            <td>{!! $value->description !!}</td>
            <td>{!! $value->price !!}</td>
            {{--                                <td>{!! $value->delivery !!}</td>--}}
            <td><span style="text-decoration: underline;cursor: pointer" onclick="SearchDetailFull('{!! $value->article !!}','{!! $value->brand !!}')">Пошук</span></td>
        </tr>
    @endforeach
</table>

<table class="table" id="details-table">
    <th>Артикул</th>
    <th>Виробник</th>
    <th>Найменування</th>
    <th>Ціна</th>
    <th>Категория TecDoc</th>
    <th></th>

    @if (isset($search) && $search == 1)
        @foreach($list as $key => $value)
            @if ($key == 'base')
                <tr>
                    <td colspan="6"><h4 class="base">АРТИКУЛ ПО ПРЯМОМУ ЗАПИТУ</h4></td>
                </tr>
                <tr>
                    <td>{!! $value[0]->article !!}</td>
                    <td>{!! $value[0]->brand !!}</td>
                    <td>{!! $value[0]->description !!}</td>
                    <td>{!! $value[0]->price !!}</td>
                    <td>{!! $value[0]->category_id !!}</td>
                    <td><a href="javascript:void(0)" style="color:#008000" onclick="inOrder('{!! $value[0]->id !!}')">У кошик</a></td>
                </tr>
                <tr>
                    <td colspan="6"><h4 class="analog">АНАЛОГИ/ЗАМІННИКИ</h4></td>
                </tr>
            @else
                @foreach($value as $keys => $val)

                    @if(isset($val[0]->article))
                        <tr>
                            <td>{!! $val[0]->article !!}</td>
                            <td>{!! $val[0]->brand !!}</td>
                            <td>{!! $val[0]->description !!}</td>
                            <td>{!! $val[0]->price !!}</td>
                            <td>{!! $val[0]->category_id !!}</td>
                            <td><a href="javascript:void(0)" style="color:#008000" onclick="inOrder('{!! $val[0]->id !!}')">У кошик</a></td>
                        </tr>
                    @endif
                @endforeach
            @endif
        @endforeach
    @else
        @foreach($list as $value)
            <tr>
                <td>{!! $value->article !!}</td>
                <td>{!! $value->brand !!}</td>
                <td>{!! $value->description !!}</td>
                <td>{!! $value->price !!}</td>
                {{--                                <td>{!! $value->delivery !!}</td>--}}
                <td>{!! $value->category_id !!}</td>
            </tr>
        @endforeach
    @endif
</table>

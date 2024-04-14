function SearchDetail() {
    $.ajax({
        url: "/search-detail",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        data: {article: $('#search-article').val()},
        success: function (e) {
            $(".brands").html(e);
        },
        error: function (err) {
        }
    })
}

function SearchDetailFull(articul, brand) {
    $('#modal1').show('fast');
    $('.back').show('fast');
    $.ajax({
        url: "/search-detail",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        data: {article: articul, brand: brand},
        success: function (e) {
            $('#modal1').hide();
            $('.back').hide();
            $(".catalogue").html(e);
            window.scrollTo(0, 0);
        },
        error: function (err) {
        }
    })
}

function Clean() {
    location.reload();
}

function getMark() {
    $.ajax({
        url: "/select-auto-mark",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {year: $('#year').val()},
        success: function (e) {
            var arr = '<option value="">Оберіть марку</option>';
            for (var i = 0; i < e.length; i++) {
                arr += '<option value="' + e[i]['MFA_BRAND'] + '">' + e[i]['MFA_BRAND'] + '</option>';
            }
            $('#mark').html(arr);
        },
        error: function (err) {
        }
    })
}

function getModel() {
    $.ajax({
        url: "/select-auto-model",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {year: $('#year').val(), mark: $('#mark').val()},
        success: function (e) {
            var arr = '<option value="">Оберіть модель</option>';
            for (var i = 0; i < e.length; i++) {
                arr += '<option value="' + e[i]['MOD_ID'] + '">' + e[i]['name'] + '</option>';
            }
            $('#model').html(arr);
        },
        error: function (err) {
        }
    })
}

function getBody() {
    $.ajax({
        url: "/select-auto-body",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {year: $('#year').val(), mark: $('#mark').val(), model: $('#model').val()},
        success: function (e) {
            var arr = '<option value="">Оберіть тип кузову</option>';
            for (var i = 0; i < e.length; i++) {
                arr += '<option value="' + e[i]['bodytype'] + '">' + e[i]['bodytype'] + '</option>';
            }
            $('#body').html(arr);
        },
        error: function (err) {
        }
    })
}

function getEngine() {
    $.ajax({
        url: "/select-auto-engine",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {year: $('#year').val(), mark: $('#mark').val(), model: $('#model').val(), body: $('#body').val()},
        success: function (e) {
            var arr = '<option value="">Оберіть двигун</option>';
            for (var i = 0; i < e.length; i++) {
                arr += '<option value="' + e[i]['enginetype'] + '">' + e[i]['enginetype'] + '</option>';
            }
            $('#engine').html(arr);
        },
        error: function (err) {
        }
    })
}

function getModif() {
    $.ajax({
        url: "/select-auto-modification",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {
            year: $('#year').val(),
            mark: $('#mark').val(),
            model: $('#model').val(),
            body: $('#body').val(),
            engine: $('#engine').val()
        },
        success: function (e) {
            var arr = '<option value="">Оберіть модифікацію</option>';
            for (var i = 0; i < e.length; i++) {
                arr += '<option value="' + e[i]['alias'] + '">' + e[i]['name'] + ' (' + e[i]['alias'] + ')</option>';
            }
            $('#modif').html(arr);
        },
        error: function (err) {
        }
    })
}

function getResultAuto() {
    $.ajax({
        url: "/select-auto-cats",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {
            year: $('#year').val(),
            mark: $('#mark').val(),
            model: $('#model').val(),
            body: $('#body').val(),
            engine: $('#engine').val(),
            modification: $('#modif').val(),
        },
        success: function (e) {
            $('#search-result').empty();
            for (var i = 0; i < e.length; i++) {
                $('#search-result').append('<h5 class="cat-' + i + '"><span style="color: green"> ' + e[i].STR_ID + ' </span>' + e[i].STR_DES_TEXT + '</h5>');
                getSubcategory(e[i].TYP_ID, e[i].STR_ID, i);
            }
        },
        error: function (err) {
        }
    })
}

function getSubcategory(typ_id, str_id, i) {
    $.ajax({
        url: "/select-auto-subcats",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {
            typ_id: typ_id,
            str_id: str_id,
        },
        success: function (e) {
            for (var y = 0; y < e.length; y++) {
                if (e[y].DESCENDANTS == 1) {
                    var counter = String(e[y].TYP_ID) + String(e[y].STR_ID) + String(i) + String(y);
                    var plus = '<span id="plus-' + counter + '"></span>';
                    var clas = 'togle';

                    $('.cat-' + i).after('<h5 class="subcat sub-' + counter + ' ' + clas + ' " onclick="Tog(' + counter + ')">' + plus + e[y].STR_DES_TEXT + '</h5>');
                    getSubsubcategory(e[y].TYP_ID, e[y].STR_ID, counter);
                }
            }
        },
        error: function (err) {
        }
    })
}

function getSubsubcategory(typ_id, str_id, counter) {
    $.ajax({
        url: "/select-auto-subsubcats",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {
            typ_id: typ_id,
            str_id: str_id,
        },
        success: function (e) {
            for (var n = 0; n < e.length; n++) {
                if (e[n].COUNT_D > 0) {
                    $('#plus-' + counter).html('<span style="color: red">+</span>');
                    $('.sub-' + counter).after("<a href='#' onclick='getDetails(\"" + e[n].DETAILS + "\")'><h5 class='subsubcat subsubcats-" + counter + "'>" + e[n].name + "<span style='color: yellow'> " + e[n].COUNT_D + "</span></h5></a>");
                }
            }
        },
        error: function (err) {
        }
    })
}

function Tog(counter) {
    $('.subsubcats-' + counter).toggle();
}

function FeelCats() {
    $('#modal1').show('slow');
    $('.back').show('slow');
    $.ajax({
        url: "/feel-category",
        method: "get",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        success: function (e) {
            $('#modal1').hide();
            alert('Категорії заповнено');
            location.reload();
        },
        error: function (err) {
        }
    })
}

function getDetails(article) {
    $("#details-result").html('');
    var art = String(article);

    $('#modal1').hide('fast');
    $('.back').hide('fast');
    $('#watch').hide('fast');
    $('.brands').text('');
    $('#details-table').remove();

    $('#modal1').show('fast');
    $('.back').show('fast');

    $.ajax({
        url: "/search-search-detail",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {article: art},
        success: function (e) {
            $('#details-result').html('<table class="table" id="details-table" style="border:1px solid #4e5165; border">\n' +
                '                    <th>Артикул</th>\n' +
                '                    <th>Виробник</th>\n' +
                '                    <th>Найменування</th>\n' +
                '                    <th>Ціна</th>\n' +
                '                    <th>Доставка</th>\n' +
                '                    <th></th>\n' +
                '                   ' +
                '<tbody>');

            for (var j = 0; j < e['list'].length; j++) {
                $("#details-table").append('<tr>' +
                    '<td>' + e['list'][j].article + '</td>' +
                    '<td>' + e['list'][j].brand + '</td>' +
                    '<td>' + e['list'][j].description + '</td>' +
                    '<td>' + e['list'][j].price + '</td>' +
                    '<td>' + e['list'][j].delivery + '</td>' +
                    '<td><span style="text-decoration: underline;cursor: pointer" onclick="SearchDetailFull(\'' + e['list'][j].article + '\',\'' + e['list'][j].brand + '\')">Пошук</span></td></tr>');
            }

            $("#details-result").append('</tbody></table>');
            $('#modal1').hide('fast');
            $('.back').hide('fast');
        },
        error: function (err) {
        }
    })
}

function getCity() {
    $.ajax({
        url: "/get-cities",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {type: $('#user-delivery-type').val()},
        success: function (data) {
            if ($('#user-delivery-type').val() == 1) {
                // html = '';
                // for (var i = 0; i < data.length; i++) {
                //     html += '<option value="' + data[i].city_name + '">' + data[i].city_name + ' (' + data[i].AreaDescription + ')</option>';
                // }
                // $('#user-city').html(html);

                // Перечень офисов для самовывоза ******************************************************************
            }
            if ($('#user-delivery-type').val() == 2 || $('#user-delivery-type').val() == 5) {
                html = '<option>Обрати місто доставки</option>';
                for (var i = 0; i < data.length; i++) {
                    html += '<option value="' + data[i].np + '">' + data[i].city_name + ' (' + data[i].AreaDescription + ')</option>';
                }
                $('#user-city').html(html);
            }
            if ($('#user-delivery-type').val() == 3 || $('#user-delivery-type').val() == 6) {
                html = '<option>Обрати місто доставки</option>';
                for (var i = 0; i < data.length; i++) {
                    html += '<option value="' + data[i].me + '">' + data[i].city_name + ' (' + data[i].AreaDescription + ')</option>';
                }
                $('#user-city').html(html);
            }
            if ($('#user-delivery-type').val() == 4) {
                html = '<option>Обрати місто доставки</option>';
                for (var i = 0; i < data.length; i++) {
                    html += '<option value="' + data[i].up + '">' + data[i].city_name + ' (' + data[i].AreaDescription + ')</option>';
                }
                $('#user-city').html(html);
            }
            // тип почтового отделения
            if ($('#user-delivery-type').val() == '2') {
                $('#type-delivery').val('NP');
                $('#type-delivery-office').val('0');
            }
            if ($('#user-delivery-type').val() == '3') {
                $('#type-delivery').val('ME');
                $('#type-delivery-office').val('0');
            }
            if ($('#user-delivery-type').val() == '4') {
                $('#type-delivery').val('UP');
                $('#type-delivery-office').val('0');
            }
            if ($('#user-delivery-type').val() == '5') {
                $('#type-delivery').val('NP');
                $('#type-delivery-office').val('4');
            }
            if ($('#user-delivery-type').val() == '6') {
                $('#type-delivery').val('ME');
                $('#type-delivery-office').val('3');
            }
        },
        error: function (err) {
        }
    })
}

function getNPwarhouse() {
    $.ajax({
        url: "/get-posts",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {
            type: $('#type-delivery').val(),
            type_office: $('#type-delivery-office').val(),
            ref: $('#user-city').val()
        },
        success: function (data) {
            html = '<option>Обрати пункт видачі</option>';
            if ($('#user-delivery-type').val() != 1) {
                for (var i = 0; i < data.length; i++) {
                    html += ('<option value="' + data[i].Ref + '">' + data[i].Description + '</option>');
                }
                $('#user-np').html(html);
            }
        },
        error: function (err) {
        }
    })
}

function inOrder(id) {
    $.ajax({
        url: "/add-to-cart",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {id: id},
        success: function (data) {
            $('#order-info').append('<div class="cart" id="id-'+ data.id +'">' +
                '<input type="text" name="article[]" value="' + data.article + '">' +
                '<input type="text" name="brand[]" value="' + data.brand + '">' +
                '<input type="text" name="description[]" value="' + data.description + '">' +
                '<input type="number" id="colvo_' + data.id + '" name="colvo[]" value="1" onchange="reCalc(' + data.id + ')">' +
                '<input type="text" id="price_one_' + data.id + '" name="price_one[]" value="' + data.price.replace(",",".") + '" class="purchase">' +
                '<input type="text" id="price_' + data.id + '" name="price[]" value="' +  data.price.replace(",",".") + '" class="prices">' +
                '<button class="garbage" onclick="deletePosition('+ data.id +')"><i class="fa fa-trash" aria-hidden="true" ></i></button>' +
                '</div>')

            $('.buy').css('display', 'block');
            calculate();
        },
        error: function (err) {
        }
    })
}

function reCalc(id) {
   var summ = parseFloat($('#colvo_' + id).val()) * parseFloat($('#price_one_' + id).val());
    $('#price_' + id).val(summ.toFixed(2));
    calculate();
}

function deletePosition(id) {
    $('#id-'+id).remove();
    calculate();
}

function calculate() {
    var inputs_prices = $('.prices');
    var totalSum= 0;
    inputs_prices.each(function() {
        var valueP = $(this).val();
        var numValueP = parseFloat(valueP);
        if (!isNaN(numValueP)) {
            totalSum += numValueP;
        }
    });

    $('#summ').val(totalSum.toFixed(2));
}

function addInOrder() {
    var user = $('#user-info').serialize;
    var order = $('#order-info').serialize;
    $.ajax({
        url: "/add-to-order",
        method: "post",
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
        dataType: "json",
        data: {user: user, order:order},
        success: function (data) {

        },
        error: function (err) {
        }
    })
}

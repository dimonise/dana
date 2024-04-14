<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postal extends Model
{
    use HasFactory;

    public function getList($type): string
    {
        $allCity = Postal::all();
        $city = [];
        if ($type == 1) {
            return json_encode($allCity);
        } elseif ($type == 2 || $type == 5) {
            $city = Postal::where('np', '!=', '')->get();
        } elseif ($type == 3 || $type == 6) {
            $city = Postal::where('me', '!=', '')->get();
        } elseif ($type == 4) {
            $city = Postal::where('up', '!=', '')->get();
        }

        return json_encode($city);
    }

    public function getPosts($type, $type_office, $ref): string
    {
        $wh = [];
        if ($type == 'NP' && $type_office == 0) {
            $wh = NovaPochtaWarehouse::where('type', '=', 'NP')->where('type_office', '=', '0')->where('CityRef', '=', $ref)->get(); //отделение
        } elseif ($type == 'NP' && $type_office == 4) {
            $wh = NovaPochtaWarehouse::where('type', '=', 'NP')->where('type_office', '=', '4')->where('CityRef', '=', $ref)->get(); //почтомат
        } elseif ($type == 'ME' && $type_office == 0) {
            $wh = NovaPochtaWarehouse::where('type', '=', 'ME')->where('type_office', '=', '0')->where('CityRef', '=', $ref)->get(); //отделение
        } elseif ($type == 'ME' && $type_office == 3) {
            $wh = NovaPochtaWarehouse::where('type', '=', 'ME')->where('type_office', '>', '0')->where('CityRef', '=', $ref)->get(); //почтомат
        } elseif ($type == 'UP' && $type_office == 0) {
            $wh = NovaPochtaWarehouse::where('type', '=', 'UP')->where('CityRef', '=', $ref)->get(); //отделение и передвижные
        }

        return json_encode($wh);
    }
}

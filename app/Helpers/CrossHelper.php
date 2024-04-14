<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class CrossHelper
{
    public function __construct(protected string $article, protected string $brand)
    {
    }


    public function index()
    {
        $art_id = [];
        $brand_id = DB::connection('tecdoc')->select("SELECT * FROM BRANDS WHERE BRA_BRAND LIKE '%" . $this->brand . "%'");
        if (count($brand_id) != 0) {
            $brandId = $brand_id[0]->BRA_ID;
            $art_id = DB::connection('tecdoc')->select("SELECT * FROM ARTICLES WHERE ART_ARTICLE_NR_CLEAN = '" . $this->article . "' AND ART_SUP_ID = " . $brandId);
        } else {
            if (!empty($this->article) && !empty($this->brand)) {
                /* Если нет бренда в текдоке, значит это кросс */
                $client = new Client();
                $this->brand = str_replace('/','',$this->brand);
                $res = $client->request('GET',
                    'http://194.15.54.191/test/hs/api/cross/' . $this->article . '/' . $this->brand . '/',
                    ['auth' => ['1c', 'z8anfaoq']]);
                if ($res->getStatusCode() == 200) {
                    $art_brand = json_decode($res->getBody()); // response "Артикул": "6PK1680","Производитель": "CONTINENTAL"

                    if (isset($art_brand[0]->Артикул)) {
                        /* ищем айди бренда */
                        $brand_id = DB::connection('tecdoc')->select("SELECT * FROM BRANDS WHERE BRA_BRAND = '" . $art_brand[0]->Производитель . "'");
                        if (count($brand_id) != 0) {
                            $brandId = $brand_id[0]->BRA_ID;
                            $art_id = DB::connection('tecdoc')->select("SELECT * FROM ARTICLES WHERE ART_ARTICLE_NR_CLEAN = '" . $art_brand[0]->Артикул . "' AND ART_SUP_ID = " . $brandId);
                        }
                    }
                }
            }
        }

        return $art_id;
    }
}

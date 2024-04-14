<?php

namespace App\Models;

use App\Helpers\CrossHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Details;

class Category extends Model
{
    use HasFactory;

    public $details;

    public function __construct()
    {
        $this->details = new Details();

    }

    public function index()
    {
        /* получаем все запчасти на складе с нулевой категорией */
        $details = $this->details->getAllWithoutCats();

        /* ищем категорию для каждой запчасти и обновляем таблицу запчастей */
        foreach ($details as $key => $detail) {

            /* ищем айди бренда */
            $brandId = DB::connection('tecdoc')
                ->table('BRANDS')
                ->where('BRA_BRAND', $detail->brand)
                ->first();

            if (isset($brandId->BRA_ID)) {
                /* находим правильный ID артикула */
                $articleId = DB::connection('tecdoc')
                    ->table('ARTICLES')
                    ->where('ART_ARTICLE_NR_CLEAN', $detail->article)
                    ->where('ART_SUP_ID', $brandId->BRA_ID)
                    ->first();

            }

            /* если это не текдоковский артикул, ищем по базе кроссов старт */
            if (empty($articleId)) {
                $article_cross = new CrossHelper($detail->article, $detail->brand);
                $articleId = $article_cross->index();
            }

            /* финиш */
            if (!empty($articleId)) {
                $ga_id_list = $this->searchCategory($articleId);

                if (!empty($ga_id_list)) {
                    $category = DB::connection('tecdoc')->
                    table('SEARCH_TREE')->
                    where('STR_ID', $ga_id_list[count($ga_id_list) - 1])->
                    get();

                } else {
                    continue;
                }

                /* апдейтим поле с айди категории */
                if (isset($category[0]->STR_ID) && $detail->CATEGORY_ID == 0) {
                    //$detailModel->update(['CATEGORY_ID' => $category[0]['STR_ID']], "ARTICLE = '" . $detail['ARTICLE'] . "' AND BRAND_NAME = '" . $detail['BRAND_NAME'] . "'");
                    $this->details->where('article', $detail->article)->where('brand', $detail->brand)->update(['CATEGORY_ID' => $category[0]->STR_ID]);
                }
            }
            unset($category);
        }
        echo json_encode('ok');
        exit();
    }

    public static function searchCategory($articleId)
    {

        if (is_array($articleId)) {
            $articleId = $articleId[0];
        }

        $link = DB::connection('tecdoc')->
        table('LINK_ART')->
        where('LA_ART_ID', $articleId->ART_ID)->
        first();

        if (isset($link->LA_GA_ID)) {
            $ga_id = DB::connection('tecdoc')->
            table('LINK_GA_STR')->
            where('LGS_GA_ID', $link->LA_GA_ID)->
            get();


            $ga_id_list = [];
            foreach ($ga_id as $gi) {
                $ga_id_list [] = $gi->LGS_STR_ID;
            }

            return $ga_id_list;
        } else {
            return false;
        }
    }
}

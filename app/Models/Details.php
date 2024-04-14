<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Details extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['article', 'brand', 'price', 'description', 'delivery'];
    private string $disabledBrands = "'VW (SVW)','VW (FAW)','AUDI (FAW)','NPS','DEPA','NISSAN (DONGFENG)','SKODA (SVW )'";
    private string $disabledBrandsSimple = "'VW (SVW)','VW (FAW)','AUDI (FAW)','NPS','DEPA','SKODA (SVW )'";

    public function UploadDetails($details = []): void
    {
        Details::truncate();
        foreach ($details as $key => $value) {
            Details::create([
                'article' => $value->Артикул,
                'brand' => $value->Производитель,
                'price' => $value->Цена,
                'description' => $value->Наименование,
                'delivery' => $value->Наличие,
            ]);
        }
    }

    public function getAll()
    {
        return Details::paginate(100);
    }

    public function getAllWithoutCats()
    {

        return Details::where('CATEGORY_ID', null)->get();
    }

    public function getSearch($article = '', $brand = '')
    {

        if ($article != null && $brand != null) {
            return $this->getAnalogs($article, $brand);
        }
        if ($article != null && $brand == null) {
            return Details::where('article', $article)->get();
        }
    }

    public function getSearchForAuto($article)
    {
        if ($article) {
            return Details::whereIn('article', $article)->get();
        }
    }

    public function getSearchById($id = null)
    {
        if ($id != null) {
            return Details::where('id', $id)->first();
        }
    }

    public function getAnalogs(string $article = null, string $brand = null)
    {
        $data = [];

        if ($article != null && $brand != null) {
            $data['base'] = Details::where('article', $article)->where('brand', $brand)->get();
            $brandfilter = DB::connection('tecdoc')->table('BRANDS')->where('BRA_BRAND', 'like', '%' . $this->stringfilter($brand) . '%')->get();
            $data['analogs'] = [];
            foreach ($brandfilter as $key => $value) {
                $search_analogs = $this->ArtLookup($article, $value->BRA_ID);

                if (!empty($search_analogs)) {
                    foreach ($search_analogs as $key => $value) {
                        $data['analogs'][] = Details::where('article', $value['ART_ARTICLE_NR_CLEAR'])->where('brand', $value['SUP_BRAND'])->get();
                    }
                }
            }
        }

        return $data;
    }

    private function ArtLookup($NUMBER = '', $BRANDFILTER = '', $LANG_ID = 16)
    {
        /**
         * ARL_KIND - тип номера:
         * 1 - неоригинальный (артикульный) номер, к которому относятся аналоги
         * 2 - торговый номер (номер пользователя)
         * 3 - оригинальный (конструкционный) номер
         * 4 - неоригинальный аналог
         * 5 - штрих-код (номер EAN)
         */

        /** НАЧАЛО запрошенный артикул */

        $data = $data1 = [];

        $SQL_BRAND = "";
        if (isset($BRANDFILTER) && !empty($BRANDFILTER)) {
            $mergeBrands = $this->mergeBrands((int)$BRANDFILTER);
            if ($mergeBrands)
                $SQL_BRAND = " AND IF(BRA_ID, BRA_ID IN ('" . join("', '", $mergeBrands) . "'), ART_SUP_ID IN ('" . join("','", $mergeBrands) . "')) ";
            else
                $SQL_BRAND = " AND IF(BRA_ID, BRA_ID IN ('" . (int)$BRANDFILTER . "'), ART_SUP_ID IN ('" . (int)$BRANDFILTER . "')) ";
        }

        $existBrand = $existArticle = [];

        $result = DB::connection('tecdoc')->
        select("
		SELECT
            ART_ID,
			IF (BRA_ID IS NULL,ART_SUP_ID, BRA_ID) AS BRA_ID,
			IF (ART_LOOKUP.ARL_KIND IN (3, 4), BRANDS.BRA_BRAND, SUPPLIERS.SUP_BRAND) AS BRAND,
			IF (ART_LOOKUP.ARL_KIND IN (3, 4), ART_LOOKUP.ARL_DISPLAY_NR, ARTICLES.ART_ARTICLE_NR) AS NUMBER,
			IF (ART_LOOKUP.ARL_KIND IN (3, 4), ART_LOOKUP.ARL_DISPLAY_NR, ARTICLES.ART_ARTICLE_NR) AS ART_ARTICLE_NR_CLEAR,
			DES_TEXTS.TEX_TEXT AS ART_COMPLETE_DES_TEXT,
			ART_LOOKUP.ARL_KIND,
			1 AS ORIGINAL,
			ART_DES_ID
		FROM ART_LOOKUP
		JOIN ARTICLES ON ARTICLES.ART_ID = ART_LOOKUP.ARL_ART_ID
		JOIN SUPPLIERS ON SUPPLIERS.SUP_ID = ARTICLES.ART_SUP_ID
		JOIN DESIGNATIONS ON DESIGNATIONS.DES_ID = ARTICLES.ART_COMPLETE_DES_ID
		JOIN DES_TEXTS ON DES_TEXTS.TEX_ID = DESIGNATIONS.DES_TEX_ID
        LEFT JOIN BRANDS ON BRANDS.BRA_ID = ART_LOOKUP.ARL_BRA_ID
		WHERE
			ART_LOOKUP.ARL_SEARCH_NUMBER = '" . $NUMBER . "'
            $SQL_BRAND
            AND DESIGNATIONS.DES_LNG_ID = '" . (int)$LANG_ID . "'
            AND IF (ART_LOOKUP.ARL_KIND IN (3, 4), BRANDS.BRA_BRAND, SUPPLIERS.SUP_BRAND) NOT IN (" . $this->disabledBrands . ")
            AND ART_LOOKUP.ARL_KIND IN (1, 3, 4)
		GROUP BY ART_ARTICLE_NR_CLEAR, BRAND, ART_COMPLETE_DES_TEXT, ART_DES_ID
		ORDER BY ARL_BLOCK ASC, ARL_KIND ASC, BRAND, NUMBER, BRA_ID");


        if (empty($result)) {

            $result = DB::connection('tecdoc')->
            select("
			SELECT
            	ART_ID,
				IF (BRA_ID IS NULL,ART_SUP_ID, BRA_ID) AS BRA_ID,
				IF (ART_LOOKUP.ARL_KIND IN (3, 4), BRANDS.BRA_BRAND, SUPPLIERS.SUP_BRAND) AS BRAND,
				IF (ART_LOOKUP.ARL_KIND IN (2, 3, 4), ART_LOOKUP.ARL_DISPLAY_NR, ARTICLES.ART_ARTICLE_NR) AS NUMBER,
				IF (ART_LOOKUP.ARL_KIND IN (2, 3, 4), ART_LOOKUP.ARL_DISPLAY_NR, ARTICLES.ART_ARTICLE_NR) AS ART_ARTICLE_NR_CLEAR,
				DES_TEXTS.TEX_TEXT AS ART_COMPLETE_DES_TEXT,
				ART_LOOKUP.ARL_KIND,
				1 AS ORIGINAL,
				ART_DES_ID
			FROM ART_LOOKUP
			JOIN ARTICLES ON ARTICLES.ART_ID = ART_LOOKUP.ARL_ART_ID
			JOIN SUPPLIERS ON SUPPLIERS.SUP_ID = ARTICLES.ART_SUP_ID
			JOIN DESIGNATIONS ON DESIGNATIONS.DES_ID = ARTICLES.ART_COMPLETE_DES_ID
			JOIN DES_TEXTS ON DES_TEXTS.TEX_ID = DESIGNATIONS.DES_TEX_ID
            LEFT JOIN BRANDS ON BRANDS.BRA_ID = ART_LOOKUP.ARL_BRA_ID
			WHERE
				ART_LOOKUP.ARL_SEARCH_NUMBER = '" . $NUMBER . "'
                $SQL_BRAND
                AND ART_LOOKUP.ARL_KIND IN (1, 2, 3, 4)
                AND DESIGNATIONS.DES_LNG_ID = '" . (int)$LANG_ID . "'
                AND IF (ART_LOOKUP.ARL_KIND IN (3, 4), BRANDS.BRA_BRAND, SUPPLIERS.SUP_BRAND) NOT IN (" . $this->disabledBrandsSimple . ")
				AND ART_LOOKUP.ARL_KIND IN (1, 2, 3, 4)
			GROUP BY ART_ARTICLE_NR_CLEAR, BRAND, ART_COMPLETE_DES_TEXT
			ORDER BY ARL_BLOCK ASC, ARL_KIND ASC, BRAND, NUMBER, BRA_ID");

        }

        // ARL_KIND 1, 3 + ARL_KIND 4
        $data_13 = $data_4 = $Exists = [];
        $GROUP_BY_BRAND = $DESCR = null;
        if ($result)
            foreach ($result as $key => $row1) {
                if ($row1->ARL_KIND == 3)
                    $row1->ART_ID = 0;

                $rowString = [];
                $rowString ['ART_ID'] = $row1->ART_ID;
                $rowString ['SUP_ID'] = $row1->BRA_ID;
                $rowString ['SUP_BRAND'] = $row1->BRAND;
                $rowString ['ART_ARTICLE_NR'] = $row1->NUMBER;
                $rowString ['ART_ARTICLE_NR_CLEAR'] = $row1->ART_ARTICLE_NR_CLEAR;
                $rowString ['TEX_TEXT'] = $row1->ART_COMPLETE_DES_TEXT;
                $rowString ['ARL_KIND'] = $row1->ARL_KIND;
                $rowString ['ORIGINAL'] = $row1->ORIGINAL;
                $rowString ['SUA_NUMBER'] = '';

                if (in_array($row1->ARL_KIND, [1, 3]) && !isset($data_13[$row1->BRAND])) {
                    $data_13 [$row1->BRAND] = $rowString;
                    $DESCR = $row1->ART_COMPLETE_DES_TEXT;
                } elseif (in_array($row1->ARL_KIND, [1, 3]) && $row1->ART_DES_ID != 0) {
                    $data_13 [$row1->BRAND] = $rowString;
                } else {
                    if (($GROUP_BY_BRAND != $row1->BRAND) && !isset($data_13[$row1->BRAND])) {
                        $data_4 [$row1->BRAND] = $rowString;
                    }

                    if (!$DESCR)
                        $DESCR = $row1->ART_COMPLETE_DES_TEXT;
                }

                $existBrand [] = addslashes($row1->BRAND);
                $existArticle [] = $row1->ART_ARTICLE_NR_CLEAR;

                $GROUP_BY_BRAND = $row1->BRAND;

                $gBrands = $this->duplicate_same_brands([['SUP_BRAND' => $row1->BRAND]]);
                foreach ($gBrands as $gB)
                    $Exists [] = $gB['SUP_BRAND'];

                unset($rowString);
            }

        /** КРОССЫ * * * * * * * * * * * */
        $resultCrosses = DB::connection('tecdoc')->
        select("
			SELECT
				'0' AS ART_ID,
				CRO_BRAND AS BRA_ID,
				CRO_BRAND AS BRAND,
				CRO_ARTICLE AS NUMBER,
				CRO_ARTICLE AS ART_ARTICLE_NR_CLEAR,
				IF(CRO_INFO,CRO_INFO,CRO_DESCR) AS ART_COMPLETE_DES_TEXT,
				'4' AS ARL_KIND,
				'1' AS ORIGINAL,
				'' SUA_NUMBER
			FROM W_CROSSES
			WHERE
				CRO_ARTICLE_SEARCH = '" . $NUMBER . "'
				AND CRO_FOUND_TECDOC = '1'
				" . ((isset($BRANDFILTER) && !empty($BRANDFILTER)) ? " AND CRO_BRAND = '" . $BRANDFILTER . "' " : "") . "
				" . (count($Exists) > 0 ? " AND ( CRO_BRAND NOT IN ('" . join("', '", array_unique($Exists)) . "') )" : "") . "
            GROUP BY BRAND, NUMBER, BRA_ID
			ORDER BY BRAND, NUMBER, BRA_ID");

        foreach ($resultCrosses as $key => $row1) {

            $rowString = [];
            $rowString ['ART_ID'] = $row1->ART_ID;
            $rowString ['SUP_ID'] = $row1->BRA_ID;
            $rowString ['SUP_BRAND'] = $row1->BRAND;
            $rowString ['ART_ARTICLE_NR'] = $row1->NUMBER;
            $rowString ['ART_ARTICLE_NR_CLEAR'] = $row1->ART_ARTICLE_NR_CLEAR;
            $rowString ['TEX_TEXT'] = $row1->ART_COMPLETE_DES_TEXT;
            $rowString ['ARL_KIND'] = $row1->ARL_KIND;
            $rowString ['ORIGINAL'] = $row1->ORIGINAL;
            $rowString ['SUA_NUMBER'] = $row1->SUA_NUMBER;

            $data_4 [] = $rowString;

            $existBrand [] = addslashes($row1->BRAND);
            $existArticle [] = $row1->ART_ARTICLE_NR_CLEAR;

            $GROUP_BY_BRAND = $row1->BRAND;

            unset($rowString);
        }

        $data1 = array_merge($data_13, $data_4);

        if (!$BRANDFILTER)
            $data1 = $this->viewPreFilterBrands($data1);

        // 546303K000 = KIA + HUINDAY
        // 96393800 = CHEVROLE + GENERAL MOTORS
        if (isset($BRANDFILTER) && $BRANDFILTER)
            $data1 = $this->duplicate_same_brands($data1);

        /** КОНЕЦ запрошенный артикул */
        /** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

        $crossesArray = $data2 = [];
        if (isset($BRANDFILTER) && $BRANDFILTER) {
            $triggerOn = true;

            /** 1) номер конструкционный с короткого на длинный
             * 1974G:95 > 351974070000:0 */

            $CONSTRUCT_NUMBER = -1;

            $construction = DB::connection('tecdoc')->
            select("
            SELECT `ARL_ART_ID` FROM `ART_LOOKUP`
            WHERE
                `ARL_SEARCH_NUMBER` = '" . $NUMBER . "'
                AND `ARL_KIND` = 2
                AND `ARL_BRA_ID` = 0
                AND `ARL_DISPLAY` = 1");

            if (!empty($construction)) {
                /** 2) подмена на конструкционный для кроссировки далее */
                $newNUMBER = DB::connection('tecdoc')->
                select("
            SELECT
                `ARL_ART_ID`,
                `ARL_SEARCH_NUMBER`
            FROM `ART_LOOKUP`
            WHERE
                `ARL_ART_ID` = '" . $construction[0]->ARL_ART_ID . "'
                AND `ARL_KIND` = 1
                AND `ARL_BRA_ID` = 0
                AND `ARL_DISPLAY_NR` = 0
                AND `ARL_BLOCK` = 0
                AND `ARL_SORT` = 0");
            }

            if (!empty($newNUMBER)) {
                $CONSTRUCT_NUMBER = $newNUMBER[0]->ARL_SEARCH_NUMBER;

                $slideQuery = current($data1);

                $data1 [] = [
                    'ART_ID' => $newNUMBER[0]->ARL_SEARCH_NUMBER,
                    'SUP_ID' => 0,
                    'SUP_BRAND' => $slideQuery['SUP_BRAND'] ?? null,
                    'ART_ARTICLE_NR' => $newNUMBER[0]->ARL_SEARCH_NUMBER,
                    'ART_ARTICLE_NR_CLEAR' => $newNUMBER[0]->ARL_SEARCH_NUMBER,
                    'TEX_TEXT' => $slideQuery['TEX_TEXT'] ?? null,
                    'ARL_KIND' => 0,
                    'ORIGINAL' => 1,
                    'SUA_NUMBER' => ''];
            }

            /** Если ищем по оригиналу, если запрос пришел с оригинала,
             * то берем поиск только на аналоги */

            $fetchNumber = current($data_13);
            $artKindFind = (isset($fetchNumber['ARL_KIND']) && $fetchNumber['ARL_KIND']) ? $fetchNumber['ARL_KIND'] : false;

            if ((isset($artKindFind) && $artKindFind == 3) && $triggerOn) {

                if (isset($BRANDFILTER) && !empty($BRANDFILTER)) {

                    $mergeBrands = $this->mergeBrands((int)$BRANDFILTER);
                    $SQL_BRAND = ($mergeBrands) ?
                        " AND (BRANDS.BRA_ID IN ('" . join("','", $mergeBrands) . "') OR SUPPLIERS.SUP_ID IN ('" . join("','", $mergeBrands) . "')) " :
                        " AND (BRANDS.BRA_ID = '" . (int)$BRANDFILTER . "' OR SUPPLIERS.SUP_ID = '" . (int)$BRANDFILTER . "') ";

                    $result = DB::connection('tecdoc')->
                    select("SELECT DISTINCT ART_LOOKUP.ARL_ART_ID FROM ART_LOOKUP
                    LEFT JOIN BRANDS ON BRANDS.BRA_ID = ART_LOOKUP.ARL_BRA_ID
                    JOIN ARTICLES ON ARTICLES.ART_ID = ART_LOOKUP.ARL_ART_ID
                    JOIN SUPPLIERS ON SUPPLIERS.SUP_ID = ARTICLES.ART_SUP_ID
                    WHERE ART_LOOKUP.ARL_SEARCH_NUMBER IN ('" . $NUMBER . "', '" . $CONSTRUCT_NUMBER . "')
                    $SQL_BRAND");

                    $AID = [0];
                    foreach ($result as $key => $row) {
                        $AID [] = $row['ARL_ART_ID'];
                    }

                    $result = DB::connection('tecdoc')->
                    select("
                    SELECT
                        ARTICLES.ART_ID AS ART_ID,
                        SUPPLIERS.SUP_ID AS SUP_ID,
                        SUPPLIERS.SUP_BRAND AS SUP_BRAND,
                        ARTICLES.ART_ARTICLE_NR AS ART_ARTICLE_NR,
                        ARTICLES.ART_ARTICLE_NR AS ART_ARTICLE_NR_CLEAR,
                        TEX.TEX_TEXT,
                        '0' AS ORIGINAL,
                        SUA_NUMBER
                    FROM ARTICLES
                    JOIN SUPPLIERS ON SUPPLIERS.SUP_ID = ARTICLES.ART_SUP_ID
                    JOIN DESIGNATIONS DES ON DES.DES_ID = ARTICLES.ART_COMPLETE_DES_ID
                    JOIN DES_TEXTS TEX ON DES.DES_TEX_ID = TEX.TEX_ID
                    LEFT JOIN SUPERSEDED_ARTICLES ON SUPERSEDED_ARTICLES.SUA_ART_ID = ARTICLES.ART_ID
                    WHERE ARTICLES.ART_ID IN (" . join(", ", $AID) . ") AND DES.DES_LNG_ID = '" . (int)$LANG_ID . "'
                    ORDER BY SUP_BRAND, ART_ARTICLE_NR;");


                    if ($result) {
                        foreach ($result as $key => $row) {
                            $data [] = $row;
                        }
                    }
                }
            } /** с аналога на аналог */
            else {
                /** ARL_KIND - тип номера:
                 * 1 - неоригинальный (артикульный) номер, к которому относятся аналоги
                 * 2 - торговый номер (номер пользователя)
                 * 3 - оригинальный (конструкционный) номер
                 * 4 - неоригинальный аналог
                 * 5 - штрих-код (номер EAN)
                 */
                $SQL_BRAND = "";
                if (isset($BRANDFILTER) && !empty($BRANDFILTER)) {
                    $mergeBrands = $this->mergeBrands((int)$BRANDFILTER);
                    if ($mergeBrands) {
                        $mergeBrands = array_unique($mergeBrands);
                        $SQL_BRAND = "AND (BRANDS.BRA_ID IN ('" . join("','", $mergeBrands) . "') OR SUPPLIERS.SUP_ID IN ('" . join("','", $mergeBrands) . "')) ";
                    } else {
                        $SQL_BRAND = "AND (BRANDS.BRA_ID = '" . (int)$BRANDFILTER . "' OR SUPPLIERS.SUP_ID = '" . (int)$BRANDFILTER . "') ";
                    }
                }

                /** Берем из выдачи поиска по группам те позиции которые не нужно показывать в
                 * аналогах.заметинетях и исключаем из выдачи дубликаты */
                $iSQL = '';
                if (count($existBrand) > 0 && count($existArticle) > 0) {
                    $existBrand = array_unique($existBrand);
                    $existArticle = array_unique($existArticle);

                    $iSQL .= "HAVING ( SUP_BRAND NOT IN ('" . join("', '", $existBrand) . "') AND ART_ARTICLE_NR_CLEAR NOT IN ('" . join("','", $existArticle) . "') )";
                }

                $AK = "3, 4";

                $result = DB::connection('tecdoc')->
                select("
			    SELECT
				    IF (ART_LOOKUP2.ARL_KIND IN (3), 0, ART_LOOKUP.ARL_ART_ID) AS ART_ID,
				    ART_LOOKUP2.ARL_KIND,
					IF (ART_LOOKUP2.ARL_KIND IN (" . $AK . "), BRANDS2.BRA_ID, SUPPLIERS2.SUP_ID) AS SUP_ID,
					IF (ART_LOOKUP2.ARL_KIND IN (" . $AK . "), BRANDS2.BRA_BRAND, SUPPLIERS2.SUP_BRAND) AS SUP_BRAND,
					IF (ART_LOOKUP2.ARL_KIND IN (" . $AK . "), ART_LOOKUP2.ARL_DISPLAY_NR, ARTICLES2.ART_ARTICLE_NR) AS ART_ARTICLE_NR,
					IF (ART_LOOKUP2.ARL_KIND IN (" . $AK . "), ART_LOOKUP2.ARL_DISPLAY_NR, ARTICLES2.ART_ARTICLE_NR) AS ART_ARTICLE_NR_CLEAR,
					TEX.TEX_TEXT,
					'0' AS ORIGINAL,
					SUA_NUMBER

				FROM ART_LOOKUP
				JOIN ARTICLES ON ARTICLES.ART_ID = ART_LOOKUP.ARL_ART_ID
				JOIN SUPPLIERS ON SUPPLIERS.SUP_ID = ARTICLES.ART_SUP_ID
                JOIN DESIGNATIONS DES ON (DES.DES_ID = ARTICLES.ART_COMPLETE_DES_ID)
				JOIN DES_TEXTS TEX ON (DES.DES_TEX_ID = TEX.TEX_ID)
                LEFT JOIN BRANDS ON BRANDS.BRA_ID = ART_LOOKUP.ARL_BRA_ID

				JOIN ART_LOOKUP AS ART_LOOKUP2 FORCE KEY (PRIMARY) ON ART_LOOKUP2.ARL_ART_ID = ART_LOOKUP.ARL_ART_ID
				JOIN ARTICLES AS ARTICLES2 ON ARTICLES2.ART_ID = ART_LOOKUP2.ARL_ART_ID
				JOIN SUPPLIERS AS SUPPLIERS2 FORCE KEY (PRIMARY) ON SUPPLIERS2.SUP_ID = ARTICLES2.ART_SUP_ID
                LEFT JOIN BRANDS AS BRANDS2 ON BRANDS2.BRA_ID = ART_LOOKUP2.ARL_BRA_ID

				LEFT JOIN SUPERSEDED_ARTICLES ON SUPERSEDED_ARTICLES.SUA_ART_ID = ARTICLES.ART_ID
				WHERE
					ART_LOOKUP.ARL_SEARCH_NUMBER IN ('" . $NUMBER . "', '" . $CONSTRUCT_NUMBER . "')
                    $SQL_BRAND
					AND DES.DES_LNG_ID = '" . (int)$LANG_ID . "'
					AND (ART_LOOKUP.ARL_KIND, ART_LOOKUP2.ARL_KIND) IN (
					   (1, 1), (1, 3),
					   (3, 1), (3, 3), (3, 4),
					   (4, 1), (1, 4)
                    )
				GROUP BY ART_ARTICLE_NR_CLEAR, SUP_BRAND
				$iSQL
				ORDER BY SUP_BRAND, ART_ARTICLE_NR");

                if ($result) {
                    foreach ($result as $key => $row) {
                        if ($row->ARL_KIND == 3)
                            $row->ART_ID = 0;

                        $data [] = $row;
                    }
                }
            }


            # Поиск по базе кроссов + кроссировка на оригинал обратная от кроссов к текдоку
            $data2 = $this->findArticlesByCrosses($BRANDFILTER, $NUMBER, $LANG_ID, array_merge((array)$data1, (array)$data, (array)$crossesArray));

            if (count($data2) <= 0) {
                # Если кроссировка ничего не дала, значит будем искать кроссы с помощью оригиналов
                # $data - это данные их текдока там аналоги и оригиналы
                $data = array_map(function ($val) {
                    return (array)$val;
                }, $data);

                $data2 = $this->findCrossesByOriginals(array_merge((array)$data1, (array)$data, (array)$crossesArray), $NUMBER, $BRANDFILTER, $DESCR);
            }
        }
        //Конец запроса с учетом бренда

        /* объединяем результат */
        $return = array_merge((array)$data1, (array)$data, (array)$data2);

        /* подмена id бренда для частных случаем */
        $return = $this->refix_supid_replace($return);

        return $return;
    }

    /** Кроссировка */
    private function findArticlesByCrosses($BRANDFILTER = '', $NUMBER = '', $LANG_ID = 16, $Exists = []): array
    {
        $data = [];
        if ($BRANDFILTER && $NUMBER) {
            $rowOne = DB::connection('tecdoc')->
            select("
			SELECT DISTINCT CRO_ID, CRO_DESCR
			FROM W_CROSSES
			WHERE
				CRO_BRAND = '" . $BRANDFILTER . "' AND
				CRO_ARTICLE_SEARCH = '" . $NUMBER . "' AND
				CRO_FOUND_TECDOC = 1");

            if (!empty($rowOne)) {
                $CRO_ID = $rowOne[0]->CRO_ID;
                $DESCR = $rowOne[0]->CRO_DESCR;
            }

            if (isset($CRO_ID)) {

                $for_tecdoc = $plusCrosses = [];
                $result = DB::connection('tecdoc')->
                select("
				SELECT DISTINCT CROSPLIT_BRAND, CROSPLIT_ARTICLE, CROSPLIT_ARTICLE_VIEW
				FROM W_CROSSES_SPLIT
				WHERE CROSPLIT_CRO_ID = '" . (int)$CRO_ID . "' AND CROSPLIT_FOUND_TECDOC = 1");

                $i = 0;
                foreach ($result as $key => $fetchAll) {
                    $i++;

                    if ($i == 1) {
                        $for_tecdoc [] = $fetchAll['CROSPLIT_ARTICLE'];
                    }

                    $plusCrosses [] = [
                        'ART_ID' => 0,
                        'SUP_ID' => 0,
                        'SUP_BRAND' => $fetchAll['CROSPLIT_BRAND'],
                        'ART_ARTICLE_NR' => ($fetchAll['CROSPLIT_ARTICLE_VIEW'] ? $fetchAll['CROSPLIT_ARTICLE_VIEW'] : $fetchAll['CROSPLIT_ARTICLE']),
                        'ART_ARTICLE_NR_CLEAR' => UiModel::stringfilter($fetchAll['CROSPLIT_ARTICLE']),
                        'TEX_TEXT' => $DESCR,
                        'ORIGINAL' => 0,
                    ];
                }

                // кроссировку к текдоку делаем только по 1 номеру
                if (isset($for_tecdoc) && count($for_tecdoc) > 0) {
                    /**
                     * $sql = "SELECT DISTINCT
                     * ARTICLES.ART_ID AS ART_ID,
                     * IF (ART_LOOKUP2.ARL_KIND = 3, BRANDS.BRA_ID, SUPPLIERS.SUP_ID) AS SUP_ID,
                     * IF (ART_LOOKUP2.ARL_KIND = 3, BRANDS2.BRA_BRAND, SUPPLIERS2.SUP_BRAND) AS SUP_BRAND,
                     * IF (ART_LOOKUP2.ARL_KIND IN (2, 3), ART_LOOKUP2.ARL_DISPLAY_NR, ARTICLES2.ART_ARTICLE_NR) AS ART_ARTICLE_NR,
                     * CLEAN_NUMBER(IF (ART_LOOKUP2.ARL_KIND IN (2, 3), ART_LOOKUP2.ARL_DISPLAY_NR, ARTICLES2.ART_ARTICLE_NR)) AS ART_ARTICLE_NR_CLEAR,
                     * TEX.TEX_TEXT,
                     * '0' AS ORIGINAL
                     * FROM
                     * ART_LOOKUP
                     * LEFT JOIN BRANDS ON BRANDS.BRA_ID = ART_LOOKUP.ARL_BRA_ID
                     * INNER JOIN ARTICLES ON ARTICLES.ART_ID = ART_LOOKUP.ARL_ART_ID
                     * INNER JOIN SUPPLIERS ON SUPPLIERS.SUP_ID = ARTICLES.ART_SUP_ID
                     * INNER JOIN ART_LOOKUP AS ART_LOOKUP2 FORCE KEY (PRIMARY) ON ART_LOOKUP2.ARL_ART_ID = ART_LOOKUP.ARL_ART_ID
                     * LEFT JOIN BRANDS AS BRANDS2 ON BRANDS2.BRA_ID = ART_LOOKUP2.ARL_BRA_ID
                     * INNER JOIN ARTICLES AS ARTICLES2 ON ARTICLES2.ART_ID = ART_LOOKUP2.ARL_ART_ID
                     * INNER JOIN SUPPLIERS AS SUPPLIERS2 FORCE KEY (PRIMARY) ON SUPPLIERS2.SUP_ID = ARTICLES2.ART_SUP_ID
                     * INNER JOIN DESIGNATIONS DES ON (DES.DES_ID = ARTICLES.ART_COMPLETE_DES_ID)
                     * INNER JOIN DES_TEXTS TEX ON (DES.DES_TEX_ID = TEX.TEX_ID)
                     * WHERE
                     * ART_LOOKUP.ARL_SEARCH_NUMBER IN ('".join("','",$for_tecdoc)."')
                     * AND ART_LOOKUP2.ARL_KIND IN (1,4)
                     * AND DES.DES_LNG_ID = '".(int)$LANG_ID."'
                     * GROUP BY ART_ARTICLE_NR_CLEAR, SUP_BRAND
                     * ORDER BY SUP_BRAND,ART_ARTICLE_NR"; */

                    $is_this_original_sql = "SELECT * FROM  `ART_LOOKUP` WHERE  `ARL_SEARCH_NUMBER` LIKE  '" . current($for_tecdoc) . "' AND  `ARL_KIND` =  '3'";
                    $is_this_original_sql = mysqli_query($this->connectTecdoc, $is_this_original_sql);
                    $is_this_original_sql = mysqli_fetch_assoc($is_this_original_sql);

                    if ($is_this_original_sql) {
                        $sql = "
                        SELECT
    						ARTICLES.ART_ID AS ART_ID,
    						IF (ART_LOOKUP.ARL_KIND = 3, SUPPLIERS.SUP_ID, BRANDS.BRA_ID) AS SUP_ID,
    						SUPPLIERS.SUP_BRAND AS SUP_BRAND,
    						IF (ART_LOOKUP.ARL_KIND IN (1, 2, 4), ART_LOOKUP.ARL_DISPLAY_NR, ARTICLES.ART_ARTICLE_NR) AS ART_ARTICLE_NR,
    						CLEAN_NUMBER(IF (ART_LOOKUP.ARL_KIND IN (1, 2, 4), ART_LOOKUP.ARL_DISPLAY_NR, ARTICLES.ART_ARTICLE_NR)) AS ART_ARTICLE_NR_CLEAR,
    						TEX.TEX_TEXT,
    						'0' AS ORIGINAL,
    						SUA_NUMBER
    					FROM ART_LOOKUP
    					LEFT JOIN BRANDS ON BRANDS.BRA_ID = ART_LOOKUP.ARL_BRA_ID
    					INNER JOIN ARTICLES ON ARTICLES.ART_ID = ART_LOOKUP.ARL_ART_ID
    					INNER JOIN SUPPLIERS AS SUPPLIERS ON SUPPLIERS.SUP_ID = ARTICLES.ART_SUP_ID
    					INNER JOIN DESIGNATIONS DES ON (DES.DES_ID = ARTICLES.ART_COMPLETE_DES_ID)
    					INNER JOIN DES_TEXTS TEX ON (DES.DES_TEX_ID = TEX.TEX_ID)
    					LEFT JOIN SUPERSEDED_ARTICLES ON SUPERSEDED_ARTICLES.SUA_ART_ID = ARTICLES.ART_ID
    					WHERE
    						ART_LOOKUP.ARL_SEARCH_NUMBER IN ('" . join("', '", $for_tecdoc) . "') AND
    						DES.DES_LNG_ID = '" . (int)$LANG_ID . "'
    					GROUP BY SUP_BRAND,ART_ARTICLE_NR_CLEAR";

                        //echo '<pre>'.$sql.'</pre>';

                        $result = mysqli_query($this->connectTecdoc, $sql);

                        while ($rowReplacement = mysqli_fetch_assoc($result))
                            $data [] = $rowReplacement;
                    }

                    return array_merge((array)$plusCrosses, (array)$data);
                }
            }
        }

        return $data;
    }

    private function refix_supid_replace(array $list = [])
    {
        return $this->priorite_sup_id($list);
    }

    /**
     * подмена SUP_ID по id
     * @param array $list
     * @return number[]
     */
    private function priorite_sup_id(array $list = [])
    {
        $ret = [];
        foreach ($list as $row) {
            //RENAULT RENAULT -> TRUCKS
            $row = (array)$row;
            if ($row['SUP_ID'] == 739)
                $row['SUP_ID'] = 566;

            $ret [] = $row;
        }

        return $ret;
    }

    private function mergeBrands($id = false)
    {
        $string = false;

        switch ($id) {

            case 568:
                return [1292, 568]; #ROVER + LAND
            case 1292:
                return [1292, 568]; #ROVER + LAND

            case 34:
                return [34, 40, 10223]; #KNECHT MAHLE -> KNECHT
            case 40:
                return [34, 40, 10223]; #KNECHT MAHLE -> MAHLE FILTERS
            case 10223:
                return [34, 40, 10223]; #KNECHT MAHLE -> MAHLE ORIGINALS
            case 11280:
                return [34, 40, 10223, 11280]; #KNECHT MAHLE -> MAHLE ORIGINALS

            case 579:
                return [579, 874]; #TOYOTA LEXUS
            case 874:
                return [579, 874]; #TOYOTA LEXUS

            case 657:
                return [657, 504, 587, 575, 573, 565, 'VAG']; #VAG AUDI VW SKODA SEAT PORSHE
            case 504:
                return [657, 504, 587, 575, 573, 565, 'VAG']; #VAG AUDI VW SKODA SEAT PORSHE
            case 587:
                return [657, 504, 587, 575, 573, 565, 'VAG']; #VAG AUDI VW SKODA SEAT PORSHE
            case 575:
                return [657, 504, 587, 575, 573, 565, 'VAG']; #VAG AUDI VW SKODA SEAT PORSHE
            case 573:
                return [657, 504, 587, 575, 573, 565, 'VAG']; #VAG AUDI VW SKODA SEAT PORSHE
            case 565:
                return [657, 504, 587, 575, 573, 565, 'VAG']; #VAG AUDI VW SKODA SEAT PORSHE
            case 'VAG':
                return [657, 504, 587, 575, 573, 565, 'VAG']; #VAG AUDI VW SKODA SEAT PORSHE

            case 558:
                return [558, 1234]; #NISSAN INFINITY
            case 1234:
                return [558, 1234]; #NISSAN INFINITY

            case 85:
                return [85, 361]; #KYB KAYABA
            case 361:
                return [85, 361]; #KYB KAYABA

            case 648:
                return [648, 647, 648, 'KIA', 'HYUNDAI']; #KIA HYUDAI
            case 647:
                return [648, 647, 648, 'KIA', 'HYUNDAI']; #KIA HYUDAI MOBIS
            case 648:
                return [648, 647, 648, 'KIA', 'HYUNDAI']; #KIA HYUDAI MOBIS
            case 'KIA':
                return [648, 647, 648, 'KIA', 'HYUNDAI']; #KIA HYUDAI MOBIS
            case 'HYUNDAI':
                return [648, 647, 648, 'KIA', 'HYUNDAI']; #KIA HYUDAI MOBIS

            case 11098:
                return [20, 11098, 186, 10752, 10753, 11277]; #LUCAS LUCAS ELEC
            case 20:
                return [20, 11098, 186, 10752, 10753, 11277]; #LUCAS LUCAS ELEC
            case 186:
                return [20, 11098, 186, 10752, 10753, 11277]; #LUCAS LUCAS ELEC
            case 10752:
                return [20, 11098, 186, 10752, 10753, 11277]; #LUCAS LUCAS ELEC
            case 10753:
                return [20, 11098, 186, 10752, 10753, 11277]; #LUCAS LUCAS ELEC
            case 11277:
                return [20, 11098, 186, 10752, 10753, 11277]; #LUCAS LUCAS ELEC

            case 10626:
                return [511, 10626]; #BMW BMW BRILL
            case 511:
                return [511, 10626]; #BMW BMW BRILL

            case 11172:
                return [11172, 10903, 376, 83, 768, 10903]; #VDO SIEME
            case 10903:
                return [11172, 10903, 376, 83, 768, 10903]; #VDO SIEME
            case 376:
                return [11172, 10903, 376, 83, 768, 10903]; #VDO SIEME
            case 83:
                return [11172, 10903, 376, 83, 768, 10903]; #VDO SIEME
            case 768:
                return [11172, 10903, 376, 83, 768, 10903]; #VDO SIEME
            case 10903:
                return [11172, 10903, 376, 83, 768, 10903]; #VDO SIEME

            case 2:
                return [2, 105, 314, 10181]; #HELLA
            case 105:
                return [2, 105, 314, 10181]; #HELLA
            case 314:
                return [2, 105, 314, 10181]; #HELLA
            case 10181:
                return [2, 105, 314, 10181]; #HELLA

            case 525:
                return [525, 814]; #FORD FORD USA
            case 814:
                return [525, 814]; #FORD FORD USA

            case 514:
                return [514, 563, 839, 'CITROËN', 'PEUGEOT']; #CITROËN PEUGEOT CITROEN/PEUGEOT
            case 563:
                return [514, 563, 839, 'CITROËN', 'PEUGEOT']; #CITROËN PEUGEOT CITROEN/PEUGEOT
            case 839:
                return [514, 563, 839, 'CITROËN', 'PEUGEOT']; #CITROËN PEUGEOT CITROEN/PEUGEOT
            case 'CITROËN':
                return [514, 563, 839, 'CITROËN', 'PEUGEOT']; #CITROËN PEUGEOT CITROEN/PEUGEOT
            case 'PEUGEOT':
                return [514, 563, 839, 'CITROËN', 'PEUGEOT']; #CITROËN PEUGEOT CITROEN/PEUGEOT

            case 11271:
                return [11271, 11161]; #SAKURA
            case 11161:
                return [11271, 11161]; #SAKURA

            case 895:
                return [895, 104]; #ZF
            case 104:
                return [895, 104]; #ZF

            case 2:
                return [2, 10120]; #HELLA
            case 10120:
                return [2, 10120]; #HELLA

            case 480:
                return [480, 'RAL']; #HELLA
            case 'RAL':
                return [480, 'RAL']; #HELLA

            case 60:
                return [60, 10690, 10531, 11285, 'GOETZE ENGINE', 'GOETZE']; #GOETZE
            case 10690:
                return [60, 10690, 10531, 11285, 'GOETZE ENGINE', 'GOETZE']; #GOETZE
            case 10531:
                return [60, 10690, 10531, 11285, 'GOETZE ENGINE', 'GOETZE']; #GOETZE
            case 11285:
                return [60, 10690, 10531, 11285, 'GOETZE ENGINE', 'GOETZE']; #GOETZE
            case 'GOETZE ENGINE':
                return [60, 10690, 10531, 11285, 'GOETZE ENGINE', 'GOETZE']; #GOETZE
            case 'GOETZE':
                return [60, 10690, 10531, 11285, 'GOETZE ENGINE', 'GOETZE']; #GOETZE

            case 10557:
                return [833, 10557]; #TRW
            case 833:
                return [833, 10557]; #TRW

            case 561:
                return [583, 561, 792, 602, 649, 'GENERAL MOTORS']; #OPEL VAUX GM CHEVROLE DAEWOO
            case 583:
                return [583, 561, 792, 602, 649, 'GENERAL MOTORS']; #OPEL VAUX GM CHEVROLE DAEWOO
            case 792:
                return [583, 561, 792, 602, 649, 'GENERAL MOTORS']; #OPEL VAUX GM CHEVROLE DAEWOO
            case 602:
                return [583, 561, 792, 602, 649, 'GENERAL MOTORS']; #OPEL VAUX GM CHEVROLE DAEWOO
            case 649:
                return [583, 561, 792, 602, 649, 'GENERAL MOTORS']; #OPEL VAUX GM CHEVROLE DAEWOO
            case 'GENERAL MOTORS':
                return [583, 561, 792, 602, 649, 'GENERAL MOTORS']; #OPEL VAUX GM CHEVROLE DAEWOO

            case 524:
                return [524, 546]; #FIAT LANCIA
            case 546:
                return [524, 546]; #FIAT LANCIA

            case 312:
                return [312, 199]; #VEMO VAICO
            case 199:
                return [312, 199]; #VAICO VEMO

            case 566:
                return [566, 739]; #RENAULT RENAULT TRUCKS
            case 739:
                return [566, 739]; #RENAULT RENAULT TRUCKS
        }
        return $string;
    }

    private function duplicate_same_brands(array $list = [])
    {
        $default = $list;
        $return = [];

        if (count($list) == 1) {
            $list = current($list);

            // HYUNDAI / KIA
            if ($list['SUP_BRAND'] == 'HYUNDAI') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'KIA';
                $return ['KIA'] = $list;

                $list ['SUP_BRAND'] = 'HYUNDAI/KIA';
                $return ['HYUNDAI/KIA'] = $list;
            } elseif ($list['SUP_BRAND'] == 'KIA') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'HYUNDAI';
                $return ['HYUNDAI'] = $list;

                $list ['SUP_BRAND'] = 'HYUNDAI/KIA';
                $return ['HYUNDAI/KIA'] = $list;
            } elseif ($list['SUP_BRAND'] == 'HYUNDAI/KIA') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'HYUNDAI';
                $return ['HYUNDAI'] = $list;

                $list ['SUP_BRAND'] = 'KIA';
                $return ['KIA'] = $list;
            } // CHAMPION

            elseif ($list['SUP_BRAND'] == 'CHAMPION LUBRICANTS') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'CHAMPION';
                $return ['CHAMPION'] = $list;

                $list ['SUP_BRAND'] = 'CHAMPION OIL';
                $return ['CHAMPION OIL'] = $list;
            } elseif ($list['SUP_BRAND'] == 'HUCO') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'HÜCO';
                $return ['HÜCO'] = $list;
            } // SSANGYONG
            elseif ($list['SUP_BRAND'] == 'SSANGYONG') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'SSANG YONG';
                $return ['SSANG YONG'] = $list;
            } // NISSAN
            elseif ($list['SUP_BRAND'] == 'NISSAN') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'INFINITI/NISSAN';
                $return ['INFINITI/NISSAN'] = $list;
            } //CHEVROLET / GENERAL MOTORS
            elseif ($list['SUP_BRAND'] == 'CHEVROLET') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'GENERAL MOTORS';
                $return ['GENERAL MOTORS'] = $list;
            } elseif ($list['SUP_BRAND'] == 'OPEL') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'GENERAL MOTORS';
                $return ['GENERAL MOTORS'] = $list;
            } elseif ($list['SUP_BRAND'] == 'DAEWOO') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'GENERAL MOTORS';
                $return ['GENERAL MOTORS'] = $list;
            } // PEUGEOT / CITROËN
            elseif ($list['SUP_BRAND'] == 'PEUGEOT') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'PSA';
                $return ['PSA'] = $list;

                $list ['SUP_BRAND'] = 'CITROËN';
                $return ['CITROËN'] = $list;

                $list ['SUP_BRAND'] = 'CITROËN/PEUGEOT';
                $return ['CITROËN/PEUGEOT'] = $list;
            } elseif ($list['SUP_BRAND'] == 'CITROËN') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'PSA';
                $return ['PSA'] = $list;

                $list ['SUP_BRAND'] = 'PEUGEOT';
                $return ['PEUGEOT'] = $list;

                $list ['SUP_BRAND'] = 'CITROËN/PEUGEOT';
                $return ['CITROËN/PEUGEOT'] = $list;
            } elseif ($list['SUP_BRAND'] == 'PSA') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'CITROËN/PEUGEOT';
                $return ['CITROËN/PEUGEOT'] = $list;
            } // VAG
            elseif ($list['SUP_BRAND'] == 'VW') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $list;
            } elseif ($list['SUP_BRAND'] == 'SKODA') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $list;
            } elseif ($list['SUP_BRAND'] == 'SEAT') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $list;
            } elseif ($list['SUP_BRAND'] == 'AUDI') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $list;
            } elseif ($list['SUP_BRAND'] == 'PORSCHE') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $list;
            } // FAI
            elseif ($list['SUP_BRAND'] == 'FAI AutoParts') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'FAI';
                $return ['FAI'] = $list;
            } // GOETZE
            elseif ($list['SUP_BRAND'] == 'GOETZE') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'GOETZE ENGINE';
                $return ['GOETZE ENGINE'] = $list;
            } elseif ($list['SUP_BRAND'] == 'GOETZE ENGINE') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'GOETZE';
                $return ['GOETZE'] = $list;
            } // TRW
            elseif ($list['SUP_BRAND'] == 'TRW Engine Component') {
                $return [$list['SUP_BRAND']] = $list;
                $list ['SUP_BRAND'] = 'TRW';
                $return ['TRW'] = $list;
            } // VEMO VAICO
            elseif ($list['SUP_BRAND'] == 'VEMO') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'VEMO-VAICO';
                $return ['VEMO-VAICO'] = $list;

                $list ['SUP_BRAND'] = 'VAICO';
                $return ['VAICO'] = $list;
            } elseif ($list['SUP_BRAND'] == 'VAICO') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'VEMO-VAICO';
                $return ['VEMO-VAICO'] = $list;

                $list ['SUP_BRAND'] = 'VEMO';
                $return ['VEMO'] = $list;
            } elseif ($list['SUP_BRAND'] == 'ZF Parts') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'ZF';
                $return ['ZF'] = $list;
            } elseif ($list['SUP_BRAND'] == 'VICTOR REINZ') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'REINZ';
                $return ['REINZ'] = $list;
            } elseif ($list['SUP_BRAND'] == 'LPR') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'LPR/RAL';
                $return ['LPR/RAL'] = $list;

                $list ['SUP_BRAND'] = 'RAL';
                $return ['RAL'] = $list;

                $list ['SUP_BRAND'] = 'AP';
                $return ['AP'] = $list;
            } elseif ($list['SUP_BRAND'] == 'SAKURA AUTOMOTIVE') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'SAKURA';
                $return ['SAKURA'] = $list;
            } elseif ($list['SUP_BRAND'] == 'HELLA') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'BEHR HELLA SERVICE';
                $return ['BEHR HELLA SERVICE'] = $list;
            } elseif ($list['SUP_BRAND'] == 'ALFA ROMEO/FIAT/LANCI') {
                $return [$list['SUP_BRAND']] = $list;

                $list ['SUP_BRAND'] = 'ALFA ROMEO';
                $return ['ALFA ROMEO'] = $list;

                $list ['SUP_BRAND'] = 'FIAT';
                $return ['FIAT'] = $list;

                $list ['SUP_BRAND'] = 'LANCI';
                $return ['LANCI'] = $list;
            } // ПО УМОЛЧАНИЮ
            else {
                $return = $default;
            }
        } else if (count($list) == 2) {

            $lft = current($list);
            $rgt = next($list);

            if ($lft['SUP_BRAND'] == 'CHEVROLET' && $rgt['SUP_BRAND'] == 'DAEWOO') {
                $lft ['SUP_BRAND'] = 'GENERAL MOTORS';
                $return ['GENERAL MOTORS'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'DAEWOO' && $rgt['SUP_BRAND'] == 'CHEVROLET') {
                $lft ['SUP_BRAND'] = 'GENERAL MOTORS';
                $return ['GENERAL MOTORS'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'HYUNDAI' && $rgt['SUP_BRAND'] == 'KIA') {
                $lft ['SUP_BRAND'] = 'HYUNDAI/KIA';
                $return ['HYUNDAI/KIA'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'KIA' && $rgt['SUP_BRAND'] == 'HYUNDAI') {
                $lft ['SUP_BRAND'] = 'HYUNDAI/KIA';
                $return ['HYUNDAI/KIA'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'VW' && $rgt['SUP_BRAND'] == 'AUDI') {
                $lft ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'AUDI' && $rgt['SUP_BRAND'] == 'VW') {
                $lft ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'VW' && $rgt['SUP_BRAND'] == 'SEAT') {
                $lft ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'SEAT' && $rgt['SUP_BRAND'] == 'VW') {
                $lft ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'PEUGEOT' && $rgt['SUP_BRAND'] == 'CITROËN') {
                $lft ['SUP_BRAND'] = 'CITROËN/PEUGEOT';
                $return ['CITROËN/PEUGEOT'] = $lft;

                $lft ['SUP_BRAND'] = 'PSA';
                $return ['PSA'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'CITROËN' && $rgt['SUP_BRAND'] == 'PEUGEOT') {

                $lft ['SUP_BRAND'] = 'CITROËN/PEUGEOT';
                $return ['CITROËN/PEUGEOT'] = $lft;

                $lft ['SUP_BRAND'] = 'PSA';
                $return ['PSA'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'CITROËN' && $rgt['SUP_BRAND'] == 'CITROËN/PEUGEOT') {

                $lft ['SUP_BRAND'] = 'PSA';
                $return ['PSA'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'INFINITI' && $rgt['SUP_BRAND'] == 'NISSAN') {
                $lft ['SUP_BRAND'] = 'INFINITI/NISSAN';
                $return ['INFINITI/NISSAN'] = $lft;
            } elseif ($lft['SUP_BRAND'] == 'NISSAN' && $rgt['SUP_BRAND'] == 'INFINITI') {
                $lft ['SUP_BRAND'] = 'INFINITI/NISSAN';
                $return ['INFINITI/NISSAN'] = $lft;
            }

            $return = array_merge($default, $return);
        } else if (count($list) == 3) {

            $lft = current($list);

            $list_check = [];
            foreach ($list as $one) {
                $list_check [] = $one['SUP_BRAND'];
            }

            if (count(array_diff($list_check, ['VW', 'SKODA', 'SEAT'])) == 0) {
                $lft ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $lft;
                $return = array_merge($default, $return);
            } elseif (count(array_diff($list_check, ['VW', 'AUDI', 'SEAT'])) == 0) {
                $lft ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $lft;
                $return = array_merge($default, $return);
            } elseif (count(array_diff($list_check, ['CITROËN', 'PEUGEOT', 'CITROËN/PEUGEOT'])) == 0) {
                $lft ['SUP_BRAND'] = 'PSA';
                $return ['PSA'] = $lft;
                $return = array_merge($default, $return);
            } elseif (count(array_diff($list_check, ['KNECHT', 'MAHLE ORIGINAL', 'MAHLE FILTER'])) == 0) {
                $lft ['SUP_BRAND'] = 'KNECHT/MAHLE ORIGINAL';
                $return ['KNECHT/MAHLE ORIGINAL'] = $lft;
                $return = array_merge($default, $return);
            } else {
                $return = $default;
            }

        } else if (count($list) == 4) {

            $lft = current($list);

            $list_check = [];
            foreach ($list as $one) {
                $list_check [] = $one['SUP_BRAND'];
            }
            if (count(array_diff($list_check, ['AUDI', 'VW', 'SKODA', 'SEAT'])) == 0) {
                $lft ['SUP_BRAND'] = 'VAG';
                $return ['VAG'] = $lft;
                $return = array_merge($default, $return);
            } else {
                $return = $default;
            }

        } else {
            $return = $default;
        }

        return $return;
    }

    private function viewPreFilterBrands(array $groups = []): array
    {
        $is_lft = $is_rgt = [];

        if (isset($groups) && count($groups) > 0) {
            foreach ($groups as $dd) {
                // VAG bug fix - WHT 003 858
                switch ($dd['SUP_ID']) {
                    case 565:
                        $dd['SUP_ID'] = 657;
                        break;
                }

                $complex = $this->mergeBrands($dd['SUP_ID']);

                if ($complex) {
                    // 1109.AH - может быть много объединений разных брендов
                    $bkey = count($complex) > 1 ? current($complex) : $complex;
                    if ($bkey) $is_lft [$bkey][] = $dd;

                } else {
                    $is_rgt [] = $dd;
                }
            }
        }

        $new_is_lft = [];
        if (isset($is_lft) && count($is_lft) > 0) {
            foreach ($is_lft as $split_lft) {
                $is_lft = $this->merge_complex($split_lft);
                $new_is_lft = array_merge((array)$new_is_lft, (array)$is_lft);
            }
        }

        return array_merge($new_is_lft, $is_rgt);
    }

    private function merge_complex(array $list = []): array
    {
        if (count($list) > 0) {
            $data = $brand_names = [];

            $i = 0;
            foreach ($list as $row) {
                $i++;
                $brand_names [] = $row['SUP_BRAND'];
                if ($i == 1)
                    $data [] = $row;
            }

            $data = current($data);
            $new_brand_name = $this->correct_names(join("/", $brand_names));
            switch ($new_brand_name) {
                case 'KIA/HYUNDAI':
                    $new_brand_name = 'HYUNDAI/KIA';
                    break;
            }
            $data ['SUP_BRAND'] = $new_brand_name;
            return [$data];
        }

        return $list;
    }

    private function correct_names($name = null)
    {
        switch ($name) {
            case 'KNECHT/MAHLE ORIGINAL':
                return 'KNECHT/MAHLE ORIGINAL';
                break;
            case 'KNECHT/MAHLE ORIGINAL/MAHLE FILTER':
                return 'KNECHT/MAHLE ORIGINAL';
                break;
            case 'KNECHT/MAHLE FILTER/MAHLE ORIGINAL':
                return 'KNECHT/MAHLE ORIGINAL';
                break;
            case 'KNECHT/MAHLE/MAHLE FILTER/MAHLE ORIGINAL':
                return 'KNECHT/MAHLE ORIGINAL';
                break;
            case 'KNECHT/MAHLE ORIGINAL/MAHLE/MAHLE FILTER':
                return 'KNECHT/MAHLE ORIGINAL';
                break;
            case 'KNECHT/MAHLE ORIGINAL/MAHLE FILTER/MAHLE':
                return 'KNECHT/MAHLE ORIGINAL';
                break;

            case 'PEUGEOT/CITROËN':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROEN/PEUGEOT/CITROËN/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROËN/PEUGEOT/CITROEN/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROËN/CITROEN/PEUGEOT/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROEN/PEUGEOT/PEUGEOT/CITROËN':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROËN/CITROËN/PEUGEOT/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROËN/PEUGEOT/CITROËN/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'PEUGEOT/CITROËN/CITROËN/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'PEUGEOT/CITROËN/CITROEN/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROEN/PEUGEOT/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROËN/PEUGEOT/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'PEUGEOT/CITROËN/PEUGEOT/CITROËN':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROËN/CITROËN/PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROËN/PEUGEOT/PEUGEOT/CITROËN':
                return 'CITROËN/PEUGEOT';
                break;

            case 'DAEWOO':
                return 'DAEWOO/GENERAL MOTORS';
                break;
            case 'OPEL':
                return 'OPEL/GENERAL MOTORS';
                break;
            case 'CHEVROLET':
                return 'CHEVROLET/GENERAL MOTORS';
                break;
            case 'VAUXHALL':
                return 'VAUXHALL/GENERAL MOTORS';
                break;

            case 'HYUNDAI':
                return 'HYUNDAI/KIA';
                break;
            case 'KIA':
                return 'HYUNDAI/KIA';
                break;

            case 'PEUGEOT':
                return 'CITROËN/PEUGEOT';
                break;
            case 'CITROËN':
                return 'CITROËN/PEUGEOT';
                break;
            case 'HELLA/HELLA PAGID':
                return 'HELLA';
                break;
            case 'LEXUS/TOYOTA':
                return 'TOYOTA/LEXUS';
                break;
            case 'AUDI':
                return 'VAG/AUDI';
                break;
            case 'VW':
                return 'VAG/VW';
                break;
            case 'SKODA':
                return 'VAG/SKODA';
                break;
            case 'SEAT':
                return 'VAG/SEAT';
                break;
            case 'AUDI/SEAT/SKODA/VW':
                return 'VAG';
                break;
            case 'AUDI/SEAT/VW/VAG':
                return 'VAG';
                break;
            case 'VEMO/VAICO':
                return 'VEMO-VAICO';
                break;
            case 'VAICO/VEMO':
                return 'VEMO-VAICO';
                break;
            case 'SAKURA  Automotive':
                return 'SAKURA AUTOMOTIVE';
                break;
            case 'RENAULT TRUCKS/RENAULT':
                return 'RENAULT';
                break;
            case 'ZF LENKSYSTEME/ZF Parts':
                return 'ZF Parts';
                break;

            case 'LUCAS/LUCAS CAV/LUCAS DIESEL/LUCAS ELECTRICAL/LUCAS ENGINE DRIVE/LUCAS TVS':
                return 'LUCAS';
                break;
            case 'LUCAS/LUCAS ELECTRICAL':
                return 'LUCAS';
                break;
            case 'LUCAS ELECTRICAL/LUCAS/LUCAS CAV/LUCAS DIESEL/LUCAS ENGINE DRIVE/LUCAS TVS':
                return 'LUCAS';
                break;

            case 'TRW Engine Component':
                return 'TRW';
                break;
            case 'TRW/TRW Engine Component':
                return 'TRW';
                break;
            case 'TRW Engine Component/TRW':
                return 'TRW';
                break;
        }
        return $name;
    }

    public static function stringfilter($str)
    {
        return str_replace(
            ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')',
                '_', '+', '=', '-', '~', '`', '"', "'", ' ', '№', '%', ';', ':',
                '[', ']', '{', '}', '*', '?', '/', '\'', '|', '.', ',', '	'], '', $str);
    }

    private function findCrossesByOriginals($AnalogOriginals = [], $NUMBER = '', $BRANDFILTER = '', $DESCR = ''): array
    {
        $EXIST = $SQL_MERGE = $data = $CRO_ID = [];

        if ($AnalogOriginals && $NUMBER && $BRANDFILTER) {
            foreach ($AnalogOriginals as $key => $AO) {
                if ($AO['ART_ARTICLE_NR_CLEAR'] && $AO['SUP_BRAND']) {
                    // блок для исключения
                    $EXIST [] = $this->stringfilter(strtoupper($AO['SUP_BRAND']) . strtoupper($AO['ART_ARTICLE_NR_CLEAR']));

                    // запрос по данным
                    $SQL_MERGE [strtoupper($AO['SUP_BRAND'])] = strtoupper($AO['ART_ARTICLE_NR_CLEAR']);
                }
            }

            if ($SQL_MERGE) {
                $I = [];
                foreach ($SQL_MERGE as $SQL_B => $SQL_A)
                    if ($SQL_B && $SQL_A)
                        $I [] = " (CROSPLIT_BRAND = '" . $SQL_B . "' AND CROSPLIT_ARTICLE  = '" . $SQL_A . "') ";

                if ($I) {
                    $result = DB::connection('tecdoc')->
                    select("
        			SELECT DISTINCT CROSPLIT_CRO_ID
        			FROM W_CROSSES_SPLIT
        			WHERE " . join(" OR ", $I) . " AND CROSPLIT_FOUND_TECDOC = 1");

                    if ($result) {
                        foreach ($result as $key => $row) {
                            if ($row->CROSPLIT_CRO_ID)
                                $CRO_ID [] = $row->CROSPLIT_CRO_ID;
                        }
                    }
                }
            }
        }

        if (isset($CRO_ID) && count($CRO_ID) > 0) {
            $CRO_ID = array_unique($CRO_ID);

            $result = DB::connection('tecdoc')->
            select("
				SELECT DISTINCT
					'0' AS ART_ID,
					CRO_BRAND AS SUP_ID,
					CRO_BRAND AS SUP_BRAND,
					CRO_ARTICLE AS ART_ARTICLE_NR,
					CRO_ARTICLE_SEARCH AS ART_ARTICLE_NR_CLEAR,
					IF(CRO_INFO, CRO_INFO, CRO_DESCR) AS TEX_TEXT,
					'0' AS ORIGINAL
				FROM W_CROSSES
				WHERE
					CRO_ID IN (" . join(", ", $CRO_ID) . ")
                    AND CRO_FOUND_TECDOC = '1'");

            foreach ($result as $key => $row) {
                if (!$row->TEX_TEXT)
                    $row->TEX_TEXT = $DESCR;

                // ключ исключения
                $keyExist = $this->stringfilter(strtoupper($row->SUP_BRAND) . strtoupper($row->ART_ARTICLE_NR));
                if (!in_array($keyExist, $EXIST)) {
                    $data [] = $row;
                }
            }
        }

        unset($EXIST);
        unset($keyExist);

        return $data;
    }
}

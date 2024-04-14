<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Helpers\CrossHelper;

class Cars extends Model
{
    use HasFactory;

    public function Search($year = null, $mark = null, $model = null, $body = null, $engine = null, $modification = null)
    {

        if ($year != null && $mark == null && $model == null && $body == null && $engine == null && $modification == null) {
            $result = DB::connection('tecdoc')->table('MANUFACTURERS')
                ->select('MFA_BRAND')
                ->join('MODELS', 'MOD_MFA_ID', '=', 'MFA_ID')
                ->whereRaw('? between MODELS.year_from and MODELS.year_to', [$year])
                ->groupBy('MANUFACTURERS.MFA_ID')
                ->orderBy('MANUFACTURERS.MFA_BRAND')
                ->get();
        } elseif ($mark != null && $year != null && $model == null && $body == null && $engine == null && $modification == null) {
            $result = DB::connection('tecdoc')->table('MANUFACTURERS')
                ->select('MODELS.name', 'MODELS.MOD_ID')
                ->join('MODELS', 'MOD_MFA_ID', '=', 'MFA_ID')
                ->where('MANUFACTURERS.MFA_BRAND', $mark)
                ->whereRaw('? between MODELS.year_from and MODELS.year_to', [$year])
                //->groupBy('MODELS.model_group')
                ->get();
        } elseif ($year != null && $mark != null && $model != null && $body == null && $engine == null && $modification == null) {
            $result = DB::connection('tecdoc')->table('MANUFACTURERS')
                ->join('MODELS', 'MOD_MFA_ID', '=', 'MFA_ID')
                ->join('TYPES', 'TYP_MOD_ID', '=', 'MODELS.MOD_ID')
                ->whereRaw('? between MODELS.year_from and MODELS.year_to', [$year])
                ->where('MANUFACTURERS.MFA_BRAND', $mark)
                ->where('MODELS.MOD_ID', $model)
                ->groupBy('bodytype')
                ->orderBy('bodytype')
                ->get();
        } elseif ($year != null && $mark != null && $model != null && $body != null && $engine == null && $modification == null) {
            $result = DB::connection('tecdoc')->table('MANUFACTURERS')
                ->join('MODELS', 'MOD_MFA_ID', '=', 'MFA_ID')
                ->join('TYPES', 'TYP_MOD_ID', '=', 'MODELS.MOD_ID')
                ->whereRaw('? between MODELS.year_from and MODELS.year_to', [$year])
                ->where('MANUFACTURERS.MFA_BRAND', $mark)
                ->where('MODELS.MOD_ID', $model)
                ->where('TYPES.bodytype', $body)
                ->groupBy('TYPES.enginetype')
                ->orderBy('TYPES.enginetype')
                ->get();
        } elseif ($year != null && $mark != null && $model != null && $body != null && $engine != null && $modification == null) {
            $result = DB::connection('tecdoc')->table('MANUFACTURERS')
                ->join('MODELS', 'MOD_MFA_ID', '=', 'MFA_ID')
                ->join('TYPES', 'TYP_MOD_ID', '=', 'MODELS.MOD_ID')
                ->whereRaw('? between MODELS.year_from and MODELS.year_to', [$year])
                ->where('MANUFACTURERS.MFA_BRAND', $mark)
                ->where('MODELS.MOD_ID', $model)
                ->where('TYPES.bodytype', $body)
                ->where('TYPES.enginetype', $engine)
                ->groupBy('TYPES.TYP_ID')
                ->orderBy('TYPES.name')
                ->get();
        } elseif ($year != null && $mark != null && $model != null && $body != null && $engine != null && $modification != null) {

            $result = DB::connection('tecdoc')->table('MANUFACTURERS')
                ->join('MODELS', 'MOD_MFA_ID', '=', 'MFA_ID')
                ->join('TYPES', 'TYP_MOD_ID', '=', 'MODELS.MOD_ID')
                ->whereRaw('? between MODELS.year_from and MODELS.year_to', [$year])
                ->where('MANUFACTURERS.MFA_BRAND', $mark)
                ->where('MODELS.MOD_ID', $model)
                ->where('TYPES.bodytype', $body)
                ->where('TYPES.enginetype', $engine)
                ->where('TYPES.alias', $modification)
                ->groupBy('TYPES.TYP_ID')
                ->orderBy('TYPES.name')
                ->first();

            $result = json_encode($this->Search_treeModel_query($result->TYP_ID, 10001, 16));

        }

        return $result;
    }

    private function Search_treeModel_query($TYP_ID, $STR_ID, $LANG_ID = 16)
    {
        $TYP_ID = $TYP_ID ? $TYP_ID : 2;
        $STR_ID = $STR_ID ? $STR_ID : 10002;
        $LANG_ID = $LANG_ID ? $LANG_ID : 16;

        $sql = DB::connection('tecdoc')->select("
        SELECT
        	STR_ID,
        	TEX_TEXT AS STR_DES_TEXT,
        	IF(EXISTS(
    			SELECT SEARCH_TREE2.STR_ID
    			FROM SEARCH_TREE AS SEARCH_TREE2
    			WHERE SEARCH_TREE2.STR_ID_PARENT <=> SEARCH_TREE.STR_ID
    			LIMIT 1
            ), 1, 0) AS DESCENDANTS,
            STR_ID_PARENT
        FROM
        	SEARCH_TREE
        	JOIN DESIGNATIONS ON DES_ID = STR_DES_ID
        	JOIN DES_TEXTS ON TEX_ID = DES_TEX_ID
        WHERE
            STR_ID_PARENT <=> '" . (int)$STR_ID . "' AND
        	DES_LNG_ID = '" . (int)$LANG_ID . "' AND
        	EXISTS (
        		SELECT LGS_STR_ID
            	FROM LINK_GA_STR
            	JOIN LINK_LA_TYP ON LAT_TYP_ID = '" . (int)$TYP_ID . "' AND LAT_GA_ID = LGS_GA_ID

            	/*JOIN LINK_ART ON LA_ID = LAT_LA_ID*/

            	WHERE LGS_STR_ID = STR_ID
            	LIMIT 1
        	)

        ORDER BY STR_DES_TEXT ASC");


        $data = [];
        foreach ($sql as $key => $row) {
            /** 13771 - Исключить Мотоцикл */
            if (!in_array($row->STR_ID, [13771]))

                if ($row->DESCENDANTS == 1) {
                    $this->Search_treeModel_query(
                        $row->STR_ID,
                        $TYP_ID,
                        $LANG_ID
                    );
                    $data[] = ['STR_ID' => $row->STR_ID, 'STR_DES_TEXT' => $row->STR_DES_TEXT, 'STR_ID_PARENT' => $row->STR_ID_PARENT, 'TYP_ID' => $TYP_ID];
                }

        }

        return $data;
    }

    public function getSubcategory(int $typ_id = null, int $str_id = null)
    {

        $subcat = DB::connection('tecdoc')->
        select("SELECT STR_ID, TEX_TEXT AS STR_DES_TEXT,
                        IF( EXISTS( SELECT * FROM SEARCH_TREE AS SEARCH_TREE2 WHERE SEARCH_TREE2.STR_ID_PARENT <=> SEARCH_TREE.STR_ID LIMIT 1 ), 1, 0) AS DESCENDANTS
                        FROM SEARCH_TREE
                            INNER JOIN DESIGNATIONS ON DES_ID = STR_DES_ID
                            INNER JOIN DES_TEXTS ON TEX_ID = DES_TEX_ID
                        WHERE STR_ID_PARENT <=> $str_id
                        AND DES_LNG_ID = 16
                        AND EXISTS ( SELECT * FROM LINK_GA_STR INNER JOIN LINK_LA_TYP ON LAT_TYP_ID = $typ_id AND LAT_GA_ID = LGS_GA_ID INNER JOIN LINK_ART ON LA_ID = LAT_LA_ID WHERE LGS_STR_ID = STR_ID LIMIT 1 )
                        ORDER BY STR_DES_TEXT DESC");
        $data = [];
        foreach ($subcat as $key => $res) {
            $details = DB::table('details')->where('category_id', $res->STR_ID)->count();
            $data[] = ['TYP_ID' => $typ_id, 'STR_ID' => $res->STR_ID, 'DESCENDANTS' => $res->DESCENDANTS, 'STR_DES_TEXT' => $res->STR_DES_TEXT, 'COUNT_D' => $details];
        }
        return json_encode($data);
    }

    public function getSubSubcategory(int $typ_id = null, int $str_id = null)
    {

        $subcat = DB::connection('tecdoc')->table('catalogue')->where('parent_id', $str_id)->get();
        $data = [];
        foreach ($subcat as $key => $res) {
            $details = DB::table('details')->where('category_id', $res->id)->get();

            $count_details = 0;
            $details_array = [];
            foreach ($details as $key => $detail) {

                $article_cross = new CrossHelper($detail->article, $detail->brand);
                $articleId = $article_cross->index();

                if (!empty($articleId[0])) {
                    $applicability = DB::connection('tecdoc')->
                    select("SELECT DISTINCT  *,
                        MANUFACTURERS.MFA_BRAND  brand,
                        MODELS.name  mod_name
                    FROM LINK_ART
                    JOIN LINK_LA_TYP ON LAT_LA_ID = LA_ID
                    JOIN TYPES ON TYP_ID = LAT_TYP_ID
                    JOIN MODELS ON MOD_ID = TYP_MOD_ID
                    JOIN MANUFACTURERS ON MFA_ID = MOD_MFA_ID
                    LEFT JOIN LINK_TYP_ENG ON LTE_TYP_ID = TYP_ID
                    WHERE
                      LA_ART_ID = '" . $articleId[0]->ART_ID . "' AND TYP_ID = '" . $typ_id . "'");
                    if (!empty($applicability)) {
                        $count_details++;
                        $details_array[] =  $detail->article;
                    }
                }
            }
            $details_array_result = implode(',',$details_array);

            $data[] = ['alias' => $res->alias, 'STR_ID' => $res->id, 'name' => $res->name, 'COUNT_D' => $count_details, 'DETAILS' => $details_array_result];
        }
        return json_encode($data);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Details;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class DetailsController extends Controller
{
    private $details;

    public function __construct()
    {
        $this->details = new Details();
    }

    public function index()
    {
        $details['list'] = $this->details->getAll();
        return view('dashboard', $details);
    }

    protected function get_details()
    {
        set_time_limit(0);
        $client = new Client();
        $res = $client->request('GET',
            'http://194.15.54.191/test/hs/api/FullPrice/fxGXX13iRkE5y0f0NvQAz9mjrAFtF4sRT9QRqZXhifgypLGAF',
            ['auth' => ['1c', 'z8anfaoq']]);
        $result = json_decode($res->getBody());

        $this->details->UploadDetails($result);
    }

    public function search_details(Request $request)
    {
        $article = $request->article;
        if ($request->brand) {
            $brand = $request->brand;
            $details['list'] = $this->details->getSearch($article, $brand);
            $details['search'] = 1;

            $html = view('layouts.table', $details);
        } else {
            $details['list'] = $this->details->getSearch($article);
            $html = view('layouts.brand', $details);
        }
        return $html;
    }


    public function search_search_details(Request $request) :string
    {
        $article = $request->article;
        $brand = $request->brand;
        $arts = explode(',', $article);
        $article = array_unique($arts);

        $details['list'] = $this->details->getSearchForAuto($article);

        return json_encode($details);
    }
}

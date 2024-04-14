<?php

namespace App\Http\Controllers;

use App\Models\Cars;
use App\Models\Details;
use App\Models\Delivery;
use App\Models\Postal;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SelectCarController extends Controller
{
    private $cars;
    private $postal;
    private $delivery;

    public function __construct()
    {
        $this->cars = new Cars();
        $this->postal = new Postal();
        $this->delivery = new Delivery();
    }

    public function index()
    {
        $result['deliveries'] = $this->delivery->getList();
        return view('search',$result);
    }

    public function getMark(Request $request)
    {
        $result = $this->cars->Search($request->year);
        return json_decode($result, true);
    }

    public function getModel(Request $request)
    {
        $result = $this->cars->Search($request->year, $request->mark);
        return json_decode($result, true);
    }

    public function getBody(Request $request)
    {
        $result = $this->cars->Search($request->year, $request->mark, $request->model);
        return json_decode($result, true);
    }

    public function getEngine(Request $request)
    {
        $result = $this->cars->Search($request->year, $request->mark, $request->model, $request->body);
        return json_decode($result, true);
    }

    public function getModification(Request $request)
    {
        $result = $this->cars->Search($request->year, $request->mark, $request->model, $request->body, $request->engine);
        return json_decode($result, true);
    }

    public function getCategories(Request $request)
    {
        $result = $this->cars->Search($request->year, $request->mark, $request->model, $request->body, $request->engine, $request->modification);
        return json_decode($result, true);
    }

    public function getSubCategories(Request $request)
    {
        $result = $this->cars->getSubcategory($request->typ_id, $request->str_id);
        return json_decode($result, true);
    }

    public function getSubSubCategories(Request $request)
    {
        $result = $this->cars->getSubSubcategory($request->typ_id, $request->str_id);
        return json_decode($result, true);
    }

    // для нового заказа старт
    /**
     * return NP, MEESTEXPRESS, UKRPOST cities info
     * @return mixed
     */
    public function getList(Request $request)
    {
        $result = $this->postal->getList($request->type);
        return json_decode($result);
    }

    /**
     * return NP, MEESTEXPRESS, UKRPOST warehouses info
     * @return mixed
     */
    public function getPosts(Request $request)
    {
        $result = $this->postal->getPosts($request->type,$request->type_office,$request->ref);
        return json_decode($result);
    }
}

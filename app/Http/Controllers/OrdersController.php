<?php

namespace App\Http\Controllers;

use App\Models\Details;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdersController extends Controller
{
    private $orders;
    private $details;

    public function __construct()
    {
        $this->orders = new Orders();
        $this->details = new Details();
    }

    public function index(Request $request) :View
    {
        if (isset($request->office)) {

        } elseif (isset($request->office) && isset($request->status)) {

        } elseif (isset($request->status)) {

        } else {
            $result['orders'] =  $this->orders->index();
//            $result['orders'] =  $this->orders->with('getAll')->get();
        }

        return view('orders', $result);
    }

    public function newOrder(Request $request) :string {
        $detail_info = $this->details->getSearchById($request->id);
        $details['id'] = $request->id;
        $details['article'] = $detail_info['article'];
        $details['brand'] = $detail_info['brand'];
        $details['price'] = $detail_info['price'];
        $details['description'] = $detail_info['description'];

        return json_encode($details);
    }

    public function addOrder(Request $request) {
        $userInfo = $request->user;
        $orderInfo = $request->order;

        $dataOrder = [
            'office_id' => $orderInfo['user_city'] ? $orderInfo['user_city'] : 0,
            'status' => 1,
            'name_client' => 1,
        ];

        $dataCart = [

        ];
    }
}

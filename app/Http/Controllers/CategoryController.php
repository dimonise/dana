<?php

namespace App\Http\Controllers;

use App\Models\Details;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{

    public $category;
    public $details;
    public function __construct()
    {
        $this->category = new Category();
        $this->details = new Details();
    }

    public function index()
    {
        Artisan::call('app:feel-cats');
        $details['list'] = $this->details->getAll();
        return view('dashboard', $details);
    }

}

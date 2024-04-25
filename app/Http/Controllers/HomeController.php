<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\{ShopiApiTrait};

class HomeController extends Controller
{

    use ShopiApiTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function cuadro()
    {
        return view('cuadro');
    }    

    public function almacen(){
        $apiVer=env("SHOPIFY_VER");
        $b2c=$this->shopiRequest( [
            "verbo"=>"GET",
            "url"=> "/admin/api/{$apiVer}/locations.json",
            "opciones"=>[],
            "tipo"=>true
        ] );

        $b2b=$this->shopiRequest( [
            "verbo"=>"GET",
            "url"=> "/admin/api/{$apiVer}/locations.json",
            "opciones"=>[],
            "tipo"=>false
        ] );

        return ["B2C"=>$b2c,"B2B"=>$b2b];

    }

    public function configStore(){
        return view('config');
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\{GraphQlTrait,QuerysTrait};

class ShopifyController extends Controller
{
    use GraphQlTrait,QuerysTrait;

    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }    

    public function getProducts(Request $request)
    {
        $query = $this->queryGetProducts();

        // Si prevNext y cursor están presentes, los usamos
        $prevNext = $request->input('prevNext');
        $cursor = $request->input('cursor');

        $first=10;
        $after = $before = null;

        // Solo configuramos los cursores si prevNext está presente
        if ($prevNext) {
            if ($prevNext == 'after') {
                $after = $cursor;
            } elseif ($prevNext == 'before') {
                $before = $cursor;
                $query=$this->queryGetProductsPrev();
            }
        }

        $variables = [
            'first' => $first, // Obtener los primeros 10 productos
            'after' => $after,
            'before' => $before,
            'query' => '', // Término de búsqueda (nombre del producto o SKU)
        ];

        $response = $this->shopiGraph($query, $variables);

        return $response;
    }
    
   
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\{GraphQlTrait,QuerysTrait,ShopiApiTrait};

class CuadroController extends Controller
{
    
    use GraphQlTrait,QuerysTrait,ShopiApiTrait;

    public function crearProductoFront(Request $request){

        $form=request()->all();

        $filter="title:{$form['producto']}";     

        $query = $this->queryGetProducts();

        $agTEST=1;
        $agCount=0;

        $variables = [
            'first' => 1,
            'after' => null,
            'before' => null,
            'query' => $filter,
        ];        

        $hasNextPage = true;
        
        while ($hasNextPage) {

            $response = $this->shopiGraph($query, $variables);
            
            if( $response["data"]["products"]["pageInfo"]["startCursor"] == null ){
                return response()->json(['msj' => "El producto no existe"], 401);
            }

            foreach ($response['data']['products']['edges'] as $clave => $producto) {     
                
                $input=$this->mapProductApi( $producto );    
                          
                try {
                    $crearProducto=$this->crearProductoShopify( $input );                    
                    return response()->json(['msj'=>"Se creo el producto B2B",'response' => $crearProducto], 200);
                } catch (\Exception $e) {
                    return response()->json(['msj' => $e], 401);                    
                }
            }

        }
        

    }    


}

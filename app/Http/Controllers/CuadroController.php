<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\{GraphQlTrait,QuerysTrait,ShopiApiTrait};

class CuadroController extends Controller
{
    
    use GraphQlTrait,QuerysTrait,ShopiApiTrait;

    public function crearProductoFront(Request $request){

        $form=request()->all();

        $filter="title:'{$form['producto']}'";     

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

    public function stockFront(Request $request){

        $form=request()->all();

        $sku=$form['sku'];
        
        $origen=$this->buscarSKU( $sku );        
        if(!$origen["data"]["productVariants"]["pageInfo"]["startCursor"] ){            
            return response()->json(['msj' => "NO SE ENCONTRO EN ORIGEN {$sku}"], 401);            
        }
        $getID=explode("/",$origen["data"]["productVariants"]["edges"][0]["node"]["inventoryItem"]["id"] );

        
        $b2b=$this->buscarSKU( $sku,false );      
        if(!$b2b["data"]["productVariants"]["pageInfo"]["startCursor"] ){            
            return response()->json(['msj' => "NO SE ENCONTRO EN B2B {$sku}"], 401);             
        }          
        $getIDb2b=explode("/",$b2b["data"]["productVariants"]["edges"][0]["node"]["inventoryItem"]["id"] );

        if( $origen["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"] != $b2b["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"] ){

            $response=$this->ajustarStock([
                "inventory_item_id"=> array_pop($getIDb2b),
                "available"=>$origen["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"]
            ]);

            return response()->json(['msj'=>"Se establecio el STOCK en: {$origen["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"]}"], 200);

        }else{            
            return response()->json(['msj'=>"No hay cambios para el producto"], 200);
        }        

    }


}

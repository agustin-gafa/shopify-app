<?php

namespace App\Traits;

use GuzzleHttp\Client;

use App\Traits\QuerysTrait;

trait GraphQlTrait {

    use QuerysTrait;

    public function shopiGraph($query,$variables, $origen=true){
        
        $apiVer=env('SHOPIFY_VER');

        if($origen==true){
            $store=env('SHOPIFY_URL');
            $apiCla=env('SHOPIFY_CLAVE_API');
            $token=env('SHOPIFY_TOKEN');
        }else{            
            $store=env('B2B_URL');
            $apiCla=env('B2B_CLAVE_API');
            $token=env('B2B_TOKEN');
        }


        $url = "https://{$store}/admin/api/{$apiVer}/graphql.json";

        $client=new Client;     
        try {
            $response = $client->post($url, [
                'auth' => [$apiCla, $token],
                'json' => ['query' => $query,'variables' => $variables],
            ]);    
            $data=json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);

            // if( $data["errors"] ){
            //     return ["ERROR"=>$data];
            // }

            return $data;


        } catch (\Exception $e) {
            // Manejo de errores
            return 'Error: ' . $e->getMessage();
        }
          
    }   

    
    public function buscarSKU($sku,$origen=true){
        $querySKU=$this->queryFindVariantSKU();
        // $location=($origen?env("SHOPIFY_LOCATION_ID"):env("B2B_LOCATION_ID") );
        // $location=['14512980040','62947852456'];
        // $skuVariables=[ "sku"=>$variante["node"]["sku"] ];
        $skuVariables=[
            'first' => 1,
            'after' => null,
            'before' => null,
            'query'=>"sku:{$sku}",
            // 'locationIds'=>$location #"gid://shopify/Location/{$location}"
            // 'query' => "sku:{$variante["node"]["sku"]}",                        
        ];
        // dd( $skuVariables );
        return $this->shopiGraph($querySKU, $skuVariables, $origen );
    }

    public function buscarProducto($productName){
        $filter="title:'{$productName}'";     

        $query = $this->queryGetProducts();

        $variables = [
            'first' => 1,
            'after' => null,
            'before' => null,
            'query' => $filter,
        ];        

        $exiteB2B=$this->shopiGraph($query, $variables,false);

        $validacion=( !$exiteB2B["data"]["products"]["pageInfo"]["startCursor"]== null?true:false );           
        return [ "validacion"=>$validacion,"producto"=>$exiteB2B ];
    }
      


}
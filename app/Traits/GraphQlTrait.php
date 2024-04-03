<?php

namespace App\Traits;

use GuzzleHttp\Client;

// use App\Traits\GraphQlTrait;

trait GraphQlTrait {

    // use QuerysTrait;

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
      


}
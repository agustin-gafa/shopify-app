<?php

namespace App\Traits;

use GuzzleHttp\Client;

trait ShopiApiTrait {

    public function setBaseUrl(){

        // $store=env('B2B_URL');
        // $apiCla=env('B2B_CLAVE_API');
        // $token=env('B2B_TOKEN');

        return "https://".env('B2B_CLAVE_API').":".env('B2B_TOKEN')."@".env('B2B_URL');

        // return "https://".env('SHOPIFY_CLAVE_API').":".env('SHOPIFY_TOKEN')."@".env('SHOPIFY_URL');
    }

    public function shopiRequest($datos){ //Set the status as active, draft, or archived
        $client=new Client;        
        $response = $client->request($datos['verbo'], $this->setBaseUrl().$datos["url"], $datos['opciones'] );                
        return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);        
    }

    function ajustarStock($data){
        $apiVer=env("SHOPIFY_VER");
        $orden=$this->shopiRequest( [
            "verbo"=>"POST",
            // "url"=> "/admin/api/2022-10/inventory_levels/adjust.json",
            "url"=>"/admin/api/{$apiVer}/inventory_levels/set.json",
            "opciones"=>[
                'json'=>[
                    'location_id'           => env('B2B_LOCATION_ID'),
                    'inventory_item_id'     => $data['inventory_item_id'],
                    'available'  => $data['available'],                
                ]
            ],
        ] );

        return $orden;
    }      

    public function crearProductoShopify($producto){

        $apiVer=env("SHOPIFY_VER");
        return $this->shopiRequest( [
            "verbo"=>"POST",
            "url"=>"/admin/api/{$apiVer}/products.json",            
            "opciones"=>[
                'json'=>[
                    'product'=>$producto
                ]
            ],
        ] );           

    }

    public function mapProductApi($producto){
        $variantes = collect($producto['node']['variants']['edges'])->map(function ($item) use($producto) {
            // unset($item["node"]["id"]); // Eliminar el campo "id" de cada variante
            // $item["node"]["inventoryQuantity"]=10; // Eliminar el campo "id" de cada variante
            
            // $opciones=[];
            // $split=explode(" / ", $item["node"]["title"] );
            // foreach ($split as $clave => $atributo) {
            //     array_push( $opciones, ["name"=>"Color","value"=>$atributo] );
            // }
            
            // $item["node"]["selectedOptions"]=$opciones;

            // return $item["node"];

            


            return $this->mapVariantApi( $item["node"],$producto['node']['title'] );

        })->toArray();

        // $str = implode (",", $producto["node"]["tags"]);      
        
        $img=collect($producto["node"]["images"]["edges"]);

        $imgSRC = $img->map(function ($item) {
            return ["src"=>$item["node"]["src"]];
        });

        // dd( $imgSRC );
        

        return [
            "title"                 => $producto['node']['title'],
            "body_html"             => $producto['node']['descriptionHtml'],
            "vendor"                => $producto['node']['vendor'],
            "tags"                  => implode (",", $producto["node"]["tags"]),
            "images"                => $imgSRC,
            "status"                => strtolower($producto['node']['status']),
            "fulfillment_service"   => "manual",
            "inventory_management"  => "shopify",
            "variants"              => $variantes,
            "options"               => [
                                        [
                                            'name'     => "Talla",
                                            "position" => 1,
                                        ]
                                    ],

        ];
    }

    public function mapVariantApi($variante,$titulo){

        $split=explode(" / ", $variante["title"] );

        return [
             'title'                => $variante["title"],
             'status'               => 'draft',
             'sku'                  => $variante['sku'],
            //  'barcode'              => $variante['EAN'],
             'price'                => $variante['price'],
            //  'compare_at_price'     => $variante['Precio'],
             "option1"              => $split[0],
            //  "option2"              => $split[1],
            //  "option3"              => $variante['Descripcion_Marca'],
             "inventory_quantity"   => (int)$variante['inventoryQuantity'],
             "fulfillment_service"  => "manual",
             "inventory_management" => "shopify",
            //  "weight"               => "1.5",                     
            //  "metafields"           =>[
            //      [                        
            //          "key"        => "paleta",
            //          "value"      => $variante['Id_Paleta'],
            //          "type" => "single_line_text_field",
            //          "namespace"  => "rknVariant",
            //      ],
            //  ]
         ];
    }

    
}
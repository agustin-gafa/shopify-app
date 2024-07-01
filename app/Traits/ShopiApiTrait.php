<?php

namespace App\Traits;

use GuzzleHttp\Client;

trait ShopiApiTrait {

    public function setBaseUrl($tipo){

        // $store=env('B2B_URL');
        // $apiCla=env('B2B_CLAVE_API');
        // $token=env('B2B_TOKEN');

        if($tipo==true){
            return "https://".env('SHOPIFY_CLAVE_API').":".env('SHOPIFY_TOKEN')."@".env('SHOPIFY_URL');
        }

        return "https://".env('B2B_CLAVE_API').":".env('B2B_TOKEN')."@".env('B2B_URL');

    }

    public function shopiRequest($datos){ //Set the status as active, draft, or archived
        $client=new Client;        
        $response = $client->request($datos['verbo'], $this->setBaseUrl($datos['tipo']).$datos["url"], $datos['opciones'] );                
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
            "tipo"=>$data['tipo']
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
            "tipo"=>false
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

            

            // if( $item["node"]["sku"] ){
                return $this->mapVariantApi( $item["node"],$producto['node']['title'] );
            // }

        })->toArray();

        // $str = implode (",", $producto["node"]["tags"]);    
            $collection = collect($variantes);
            $filtered = $collection->whereNotNull()->toArray();
        
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
                "variants"              => $filtered,
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

            $stock=$this->evaluarStock($variante["inventoryItem"]["inventoryLevels"]["edges"] );
            
            $agSKU=$this->evalSku( $variante['sku'],$variante['id'] );

            return [
                'title'                => $variante["title"],
                'status'               => 'draft',
                'sku'                  => $agSKU,
                //  'barcode'              => $variante['EAN'],
                'price'                => $variante['price'],
                //  'compare_at_price'     => $variante['Precio'],
                "option1"              => $split[0],
                //  "option2"              => $split[1],
                //  "option3"              => $variante['Descripcion_Marca'],
                "inventory_quantity"   => $stock, #(int)$variante['inventoryQuantity'],
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


    public function evaluarStock($arrayAlmacen){
        $almacenesOrigen=$arrayAlmacen; #$origen["data"]["productVariants"]["edges"][0]["node"]["inventoryItem"]["inventoryLevels"]["edges"];
        $selectAlmacen=explode(",",env("SHOPIFY_LOCATION_ID"));
        $stockSum=0;
        foreach ($almacenesOrigen as $indexAl => $almacen) {
            $buscarAlmacen=explode("/",$almacen["node"]["location"]["id"]);
            if( in_array( array_pop($buscarAlmacen), $selectAlmacen ) ){
                $stockSum+=$almacen["node"]["quantities"][0]["quantity"];
            }
            
        }

        return $stockSum;
    }

    public function crearVarianteShopify($id,$variante){

        $apiVer=env("SHOPIFY_VER");
        return $this->shopiRequest( [
            "verbo"=>"POST",
            "url"=>"/admin/api/{$apiVer}/products/{$id}/variants.json",            
            "opciones"=>[
                'json'=>[
                    'variant'=>$variante
                ]
            ],
            "tipo"=>false
        ] );           

    }

    public function evalSku($sku,$id){
        if( $sku== "" ){
            $agSepara=explode("/",$id);
            $rknGetId=array_pop($agSepara);                        
            $agSKU="ADM{$rknGetId}";

            $apiVer=env("SHOPIFY_VER");
            $data= $this->shopiRequest( [
                "verbo"=>"PUT",
                "url"=>"/admin/api/{$apiVer}/variants/{$rknGetId}.json",            
                "opciones"=>[
                    "json"=>[
                        'id'=>$rknGetId,
                        'variant'=>[
                            "sku"=>$agSKU                                
                        ]
                    ]
                ],
                "tipo"=>true
            ] );       

        }else{
            $agSKU= $sku;
        }

        return $agSKU;
    }
    
}
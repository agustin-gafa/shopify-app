<?php

namespace App\Traits;

use GuzzleHttp\Client;

use Illuminate\Support\Facades\Log;

trait ShopiApiTrait {

    public function setBaseUrl($tipo){

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
            return $this->mapVariantApi( $item["node"],$producto['node']['title'] );        
        })->toArray();

         
            $collection = collect($variantes);
            $filtered = $collection->whereNotNull()->toArray();
        
            $img=collect($producto["node"]["images"]["edges"]);

            $imgSRC = $img->map(function ($item) {
                return ["src"=>$item["node"]["src"]];
            });         

            return [
                "title"                 => $producto['node']['title'],
                "handle"                => $producto['node']['handle'],
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

            $stock=$this->evaluarStock($variante["inventoryItem"]["inventoryLevels"]["edges"] );
            
            $agSKU=$this->evalSku( $variante['sku'],$variante['id'] );

            return [
                'title'                => $variante["title"],
                'status'               => 'draft',
                'sku'                  => $agSKU,
                //  'barcode'              => $variante['EAN'],
                'price'                => $variante['price'],
                //  'compare_at_price'     => $variante['Precio'],
                "option1"              => $variante["title"], //$option,
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






    // METODOS para METACAMPOS


    public function evalMetacampos($producto,$segundoArrMeta,$productoEnB2B){        

        $metacampos=$this->compararArraysSinId($producto["node"]["metafields"]["edges"],$segundoArrMeta );

        if($metacampos["hayCambios"]){
            
            $apiVer=env("SHOPIFY_VER");


            $agGetIdMeta=explode("/",$productoEnB2B );
            $select_B2b_idMeta=array_pop($agGetIdMeta);

            

            if( count($metacampos["NUEVO"]) > 0 ){
                Log::info("::CREAR METACAMPOS");

                foreach ($metacampos["NUEVO"] as $claveMeta => $metacampo) {

                    try {
                        $this->shopiRequest( [
                            "verbo"=>"POST",
                            "url"=>"/admin/api/{$apiVer}/products/{$select_B2b_idMeta}/metafields.json",
                            "opciones"=>[
                                'json'=>[
                                    "metafield"=>$metacampo
                                ]
                            ],
                            "tipo"=>false
                        ] );
                        Log::info("METACAMPO {$metacampo["key"]} CREADO");
                    } catch (Exception $e) {
                        Log::info("ERROR: {$metacampo["key"]}, el campo contiene propiedades que se pueden compartir");
                    }

                }
                
            }

            if( count($metacampos["ACTUALIZAR"]) > 0 ){
                Log::info("::ACTUALIZAR METACAMPOS");
                
                foreach ($metacampos["ACTUALIZAR"] as $claveMetaA => $metacampoA) {                    

                    $selectMetaId=explode("/",$metacampoA["id"] );
                    $getMetadId=array_pop($selectMetaId);

                        $this->shopiRequest( [
                            "verbo"=>"PUT",
                            "url"=>"/admin/api/{$apiVer}/products/{$select_B2b_idMeta}/metafields/{$getMetadId}.json",
                            "opciones"=>[
                                'json'=>[
                                    "metafield"=>[
                                        "id"=>$getMetadId,                                        
                                        "value"=>$metacampoA["value"],
                                        // "type"=>$metacampoA["type"],
                                        // "namespace"=>$metacampoA["namespace"],
                                        // "key"=>$metacampoA["key"]
                                    ]
                                ]
                            ],
                            "tipo"=>false
                        ] );
                        Log::info("METACAMPO {$metacampoA["key"]} ACTUALIZADO");

                }
            }

        }else{
            Log::info("NO SE DETECTARON CAMBIOS EN METACAMPOS");
        }
    }


    public function compararArraysSinId($array1, $array2) {
        $actualizar = [];
        $nuevo = [];
        $hayCambios = false;

        // Tipos excluidos
        $tiposExcluidos = ['list.product_reference', 'single_line_text_field', 'list.metaobject_reference'];

        // Filtrar array1 eliminando los elementos con tipos excluidos
        $array1 = array_filter($array1, function($item) use ($tiposExcluidos) {
            return !in_array($item['node']['type'], $tiposExcluidos);
        });

        foreach ($array1 as $item1) {
            $node1 = $item1['node'];
            $encontrado = false;

            foreach ($array2 as $item2) {
                $node2 = $item2['node'];

                // Verificar si todas las claves excepto 'value' coinciden
                $todasLasClavesCoinciden = true;
                foreach ($node1 as $clave => $valor) {
                    if ($clave !== 'id' && $clave !== 'value' && (!isset($node2[$clave]) || $node1[$clave] !== $node2[$clave])) {
                        $todasLasClavesCoinciden = false;
                        break;
                    }
                }

                if ($todasLasClavesCoinciden) {
                    $encontrado = true;
                    if ($node1['value'] !== $node2['value']) {
                        $actualizar[] = [
                            'id' => $node2['id'], // Usar el ID del segundo array para actualizar
                            'key' => $node1['key'],
                            'value' => $node1['value'], // Usar el value del primer array
                            'type' => $node1['type'],
                            'namespace' => $node1['namespace']
                        ];
                        $hayCambios = true;
                    }
                    break;
                }
            }

            if (!$encontrado) {
                $nuevo[] = [
                    'key' => $node1['key'],
                    'value' => $node1['value'],
                    'type' => $node1['type'],
                    'namespace' => $node1['namespace']
                ];
                $hayCambios = true;
            }
        }

        return [
            'hayCambios' => $hayCambios,
            'ACTUALIZAR' => $actualizar,
            'NUEVO' => $nuevo
        ];
    }


    
}
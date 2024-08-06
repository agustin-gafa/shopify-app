<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Traits\{GraphQlTrait,QuerysTrait,ShopiApiTrait};
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CreateAddProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GraphQlTrait,QuerysTrait,ShopiApiTrait;
    
    protected $fechaProducto;
    protected $producto;
    /**
     * Create a new job instance.
     */
    public function __construct($fechaProducto,$producto)
    {      
        $this->fechaProducto=$fechaProducto;
        $this->producto=$producto;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {



        Log::info("FECHA MOD: {$this->fechaProducto} - {$this->producto["node"]["title"]}");                          

        $verificaExist=$this->buscarProducto($this->producto["node"]["title"]);    
                

        if(!$verificaExist["validacion"]){

            $input=$this->mapProductApi( $this->producto );    
            Log::info("CREAR {$input['title']}");      
            

            // dd($input);

            
            
            if( count($input["variants"])>0 ){
                $creaProducto=$this->crearProductoShopify( $input );

                $productoEnB2B=$creaProducto["product"]["id"];
                $segundoArrMeta=[];

                Log::info("Creado {$input['title']} *****");
            }else{
                Log::info("Producto sin SKU {$input['title']} ");
            }

        }else{

            $segundoArrMeta=$verificaExist["producto"]["data"]["products"]["edges"][0]["node"]["metafields"]["edges"];
            $productoEnB2B=$verificaExist["producto"]["data"]["products"]["edges"][0]["node"]["id"];

            Log::info("MODIFICAR/AGREGAR {$this->producto["node"]["title"]}");                                            

            $titlesInSecondArray = array_map(function ($item) {
                return $item['node']['title'];
            }, $verificaExist["producto"]["data"]["products"]["edges"][0]["node"]["variants"]["edges"] );
                                    
            
            $missingProducts = array_filter($this->producto["node"]["variants"]["edges"], function ($item) use ($titlesInSecondArray) {
                return !in_array($item['node']['title'], $titlesInSecondArray);
            });

            if( count($missingProducts)>0 ){

                $agGetId=explode("/",$verificaExist["producto"]["data"]["products"]["edges"][0]["node"]["id"] );
                $select_B2b_id=array_pop($agGetId);
                
                foreach ($missingProducts as $indexNewVariant => $newVariant) {                    

                    $agSKU=$this->evalSku( $newVariant["node"]['sku'],$newVariant["node"]['id'] );

                        $stockOrigen=$this->evaluarStock( $newVariant["node"]["inventoryItem"]["inventoryLevels"]["edges"] );
                        $items=[
                            "product_id"=>$select_B2b_id,
                            "title"=> $newVariant["node"]['title'],
                            "status"=> "draft",
                            "sku"=> $agSKU,
                            "price"=> $newVariant["node"]['price'],                                    
                            "option1"=> $newVariant["node"]['title'],
                        ];
                        

                        $varianteCreada=$this->crearVarianteShopify($select_B2b_id,$items);                                

                        $responseStock=$this->ajustarStock([
                            "inventory_item_id"=> $varianteCreada["variant"]["inventory_item_id"],
                            "available"=>$stockOrigen,
                             "tipo"=>false
                        ]);                        

                        Log::info("Variante agregada: {$this->producto["node"]["title"]} - {$newVariant["node"]['title']}");
                    // }else{
                    //     Log::info("Variante NO TIENE SKU: {$this->producto["node"]["title"]} - {$newVariant["node"]['title']}");
                    // }

                    
                }                            
            }else{
                Log::info("NO HAY CAMBIOS EN EL PRODUCTO");
            }

        }        


        // Agregar METACAMPOS

        $metacampos=$this->compararArraysSinId($this->producto["node"]["metafields"]["edges"],$segundoArrMeta );

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


                    // dd( $metacampoA );

                    $selectMetaId=explode("/",$metacampoA["id"] );
                    $getMetadId=array_pop($selectMetaId);

                    // dd($getMetadId);

                    // try {
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
                    // } catch (Exception $e) {
                    //     Log::info("ERROR: {$metacampoA["key"]}, el campo contiene propiedades que se pueden compartir");
                    // }

                }
            }

        }else{
            Log::info("NO SE DETECTARON CAMBIOS EN METACAMPOS");
        }

        
    }



    // METODOS
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

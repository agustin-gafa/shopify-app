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
            
            if( count($input["variants"])>0 ){
                $this->crearProductoShopify( $input );
                Log::info("Creado {$input['title']} *****");
            }else{
                Log::info("Producto sin SKU {$input['title']} ");
            }

        }else{
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

        
    }
}

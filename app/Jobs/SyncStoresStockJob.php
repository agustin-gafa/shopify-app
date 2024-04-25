<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Traits\{ShopiApiTrait,GraphQlTrait};
use Illuminate\Support\Facades\Log;

class SyncStoresStockJob implements ShouldQueue
{
    use ShopiApiTrait,GraphQlTrait,Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhookData;
    protected $selectStore;

    /**
     * Create a new job instance.
     */
    public function __construct($webhookData,$selectStore)
    {
        $this->webhookData=$webhookData;
        $this->selectStore=$selectStore;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $almacenesB2C=explode(",",env( "SHOPIFY_LOCATION_ID" ));
        $getID=$almacenesB2C[0];
        $getID2nd=$almacenesB2C[1];
        $getAlmacenB2B=env("B2B_LOCATION_ID");
        $alm1st=0;
        $alm2nd=0;

        Log::info("Busqueda de productos");
        foreach ($this->webhookData["line_items"] as $clave => $producto) {
            $busca=$this->buscarSKU( $producto['sku'],$this->selectStore );            

            if(!$busca["data"]["productVariants"]["pageInfo"]["startCursor"] ){
                Log::info("NO se encontro SKU:{$producto['sku']}");                    
            }else{

                $getIDInventarioItem=explode("/", $busca["data"]["productVariants"]["edges"][0]["node"]["inventoryItem"]["id"] );

                $ultimoInventarioItem=array_pop( $getIDInventarioItem );

                foreach ( $busca["data"]["productVariants"]["edges"][0]["node"]["inventoryItem"]["inventoryLevels"]["edges"] as $indexAlm => $almacenAg) {
                    $almacenBusqueda=explode("/",$almacenAg["node"]["location"]["id"]);

                    $ultimo=array_pop($almacenBusqueda);

                    if( $ultimo == $getID || $ultimo== $getAlmacenB2B ){
                        $alm1st=1;
                        $primerAlmacen=$almacenAg["node"]["quantities"][0]["quantity"];                        
                    }
                    if($this->selectStore==true){
                        if( $ultimo == $getID2nd ){
                            $alm2nd=1;
                            $segundoAlmacen=$almacenAg["node"]["quantities"][0]["quantity"];
                        }       
                    }           
                }

                
                if($alm1st==1 || $alm2nd==1){
                    $cantidad=$producto['quantity'];
                    $almacenB2C=$primerAlmacen-$cantidad;

                    if($this->selectStore==true){                  
                        Log::info("La actualizacion va hacia B2C");                
                        
                        if( $almacenB2C<0 && $alm2nd==1 ){

                                $response2nd=$this->ajustarStock([
                                    "inventory_item_id"=> $ultimoInventarioItem,
                                    "available"=>$segundoAlmacen+$almacenB2C,
                                    "tipo"=>$this->selectStore
                                ]);

                                // array_push($agRequest, ["2nd"=>["sku"=>$producto['sku'],"almacen"=>$getID2nd, "IDitem"=>$ultimoInventarioItem, "available"=>$segundoAlmacen+$almacenB2C] ]  );
                                $segAlmacenResta=$segundoAlmacen+$almacenB2C;
                                Log::info("Se ajusto STOCK:{$producto['sku']}, almacen:{$getID2nd}, IDitem:{$ultimoInventarioItem}, available:{$segAlmacenResta}" );                            

                            $almacenB2C=0;
                        }
                        
                    }else{
                        Log::info("La actualizacion va hacia B2B"); 
                        $getID=$getAlmacenB2B;                                      
                    }
                    
                    Log::info("Se ajusto STOCK:{$producto['sku']}, almacen:{$getID}, IDitem:{$ultimoInventarioItem}, available:{$almacenB2C}" );                        
                    $response=$this->ajustarStock([
                        "inventory_item_id"=> $ultimoInventarioItem,
                        "available"=>$almacenB2C,
                        "tipo"=>$this->selectStore
                    ]);
                }else{
                    Log::info("ERROR: NO COINCIDEN LOS ALMACENES");
                    break;
                }

            }

        }
                
        Log::info("::::Finalizo proceso SYNC");
        
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


use App\Traits\{ShopiApiTrait,GraphQlTrait};
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,ShopiApiTrait,GraphQlTrait;

    protected $producto;
    /**
     * Create a new job instance.
     */
    public function __construct($producto)
    {
        $this->producto=$producto;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info(":::STOCK: {$this->producto["node"]["updatedAt"]}");

        foreach ( $this->producto["node"]["variants"]["edges"] as $claveVar => $variante) {                    
            
            Log::info("BUSCANDO EN B2B: {$variante["node"]["sku"]}");
            $b2b=$this->buscarSKU( $variante["node"]["sku"],false );      
            if(!$b2b["data"]["productVariants"]["pageInfo"]["startCursor"] ){
                Log::info("NO SE ENCONTRO EN B2B {$variante["node"]["sku"]}");                        
            }else{
                $getIDb2b=explode("/",$b2b["data"]["productVariants"]["edges"][0]["node"]["inventoryItem"]["id"] );

                $stockOrigen=$this->evaluarStock( $variante["node"]["inventoryItem"]["inventoryLevels"]["edges"] );

                if( $stockOrigen != $b2b["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"] ){

                    $response=$this->ajustarStock([
                        "inventory_item_id"=> array_pop($getIDb2b),
                        "available"=>$stockOrigen,
                         "tipo"=>false
                    ]);

                    Log::info("Ajuste de stock de {$b2b["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"]} > {$stockOrigen }");

                }else{
                    Log::info("No hay cambios para el producto");
                }

                // dd( ["STOCK"=>$stockOrigen] );
            }         


        }        
    }
}

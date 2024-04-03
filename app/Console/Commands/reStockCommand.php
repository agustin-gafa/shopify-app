<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Traits\{ShopiApiTrait,GraphQlTrait};

class reStockCommand extends Command
{

    use ShopiApiTrait,GraphQlTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:re-stock {sku}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $sku=$this->argument('sku');

        $this->line("BUSCANDO EN ORIGEN: {$sku}");
        $origen=$this->buscarSKU( $sku );        
        if(!$origen["data"]["productVariants"]["pageInfo"]["startCursor"] ){
            $this->line("<fg=black;bg=red> NO SE ENCONTRO EN ORIGEN {$sku}</>");
            exit();
        }
        $getID=explode("/",$origen["data"]["productVariants"]["edges"][0]["node"]["inventoryItem"]["id"] );


        $this->line("BUSCANDO EN B2B: {$sku}");
        $b2b=$this->buscarSKU( $sku,false );      
        if(!$b2b["data"]["productVariants"]["pageInfo"]["startCursor"] ){
            $this->line("<fg=black;bg=red> NO SE ENCONTRO EN B2B {$sku}</>");
            exit();
        }          
        $getIDb2b=explode("/",$b2b["data"]["productVariants"]["edges"][0]["node"]["inventoryItem"]["id"] );

        if( $origen["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"] != $b2b["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"] ){

            $response=$this->ajustarStock([
                "inventory_item_id"=> array_pop($getIDb2b),
                "available"=>$origen["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"]
            ]);

            $this->line("<fg=black;bg=green> Se establecio el STOCK en: {$origen["data"]["productVariants"]["edges"][0]["node"]["inventoryQuantity"]}</>");

        }else{
            $this->line("<fg=black;bg=blue>No hay cambios para el producto</>");
        }

    }
}

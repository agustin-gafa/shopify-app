<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Traits\{GraphQlTrait,QuerysTrait,ShopiApiTrait};

class AgTestCommand extends Command
{
    use GraphQlTrait,QuerysTrait,ShopiApiTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

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

        $variante=47720459436370; #41491241238593;

            $apiVer=env("SHOPIFY_VER");
           $data= $this->shopiRequest( [
                "verbo"=>"PUT",
                "url"=>"/admin/api/{$apiVer}/variants/{$variante}.json",            
                "opciones"=>[
                    "json"=>[
                        'id'=>$variante,
                        'variant'=>[
                            "sku"=>"PRUEBAS"                                
                        ]
                    ]
                ],
                "tipo"=>true
            ] );  
        dd($data);
    }
}

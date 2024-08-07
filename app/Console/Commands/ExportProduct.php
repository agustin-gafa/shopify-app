<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Traits\{GraphQlTrait,QuerysTrait,ShopiApiTrait};
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\{CreateAddProductsJob};
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExportProduct extends Command
{
    use DispatchesJobs,GraphQlTrait,QuerysTrait,ShopiApiTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-product {inicio=HOY} {producto=-} {first=1}';

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

        Log::info(":::::::::::::::::::: INICIA PROCESO | ".date('Y-m-d H:i:s')." ::::::::::::::::::::");

        $first=( $this->argument('producto')!="-"?1:$this->argument('first') );
        
        $fechaAG=Carbon::now();
        $inicio=( $this->argument('inicio')=='HOY'?$fechaAG->sub(1,'hour')->toIso8601ZuluString():Carbon::create( $this->argument('inicio') )->toIso8601ZuluString() );
        
        $query = $this->queryGetProducts();   
        
        $filter=( $this->argument('producto')!="-"?"title:'{$this->argument('producto')}'":"updated_at:>{$inicio}" );        
        // $graphQuery=( $this->argument('inicio')=='RK' );
        
        // START JOB
        // $this->dispatch((new CreateAddProductsJob($first, $filter, $inicio, $query))->onQueue('procesarproducto'));

        $variables = [
            'first' => (int)$first,
            'after' => null,
            'before' => null,
            "query" => $filter,
        ];

        // dd( $variables );
        
        $hasNextPage = true;
        
        while ($hasNextPage) {

            Log::info("Busca productos B2C");
            $response = $this->shopiGraph($query, $variables);

            // dd( $response );


            if( array_key_exists('data',$response) ){

                // $this->line("<fg=black;bg=green>::Procesa 100</>"); 
            
                $hasNextPage = $response['data']['products']['pageInfo']['hasNextPage'];
                if ($hasNextPage) {
                    $variables['after'] = $response['data']['products']['pageInfo']['endCursor'];
                }        
                            

                // dd( $response['data']['products']['edges'] );

                foreach ($response['data']['products']['edges'] as $clave => $producto) {

                    // dd( $producto );
                    
                    $fechaProducto = Carbon::parse($producto["node"]["updatedAt"]);
                    
                    
                    if ($fechaProducto->gte($inicio) || $this->argument('producto')!='-' ) {

                        // dd( $producto["node"]["metafields"]["edges"]  );
                        
                        
                        // dd( $producto["node"]["id"] );

                        Log::info("Agrega JOB: {$producto["node"]["title"]}");
                        $this->dispatch((new CreateAddProductsJob( $fechaProducto,$producto ))->onQueue('procesarproducto'));  
                        
                        // dd("Detener JOB");

                    }else{                    
                        $hasNextPage=false;
                        Log::info('::::::::::::::::::: TERMINO STOCK | '.date('Y-m-d H:i:s').' :::::::::::::::::::');
                        break;
                    }

                }
            }else{

                $this->line("<fg=black;bg=green>::Termino porque DATA no tiene nada</>"); 

            }






        }

        

        Log::info("::: FIN comando");
      
    }



   

}

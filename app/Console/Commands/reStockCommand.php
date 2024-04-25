<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Traits\{ShopiApiTrait,GraphQlTrait};
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\{StockCommandJob};
use Carbon\Carbon;

class reStockCommand extends Command
{

    use DispatchesJobs,ShopiApiTrait,GraphQlTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:re-stock {inicio=HOY} {producto=-}';

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

        // $modifiedSince = Carbon::parse('2024-04-12 18:00:00'); // Parsea la fecha y hora en Carbon
        
        $filter=( $this->argument('producto')!="-"?" AND title:'{$this->argument('producto')}'":'' );

        $fechaAG=Carbon::now();
        $inicio=( $this->argument('inicio')=='HOY'?$fechaAG->sub(1,'hour')->toIso8601ZuluString():Carbon::create( $this->argument('inicio') )->toIso8601ZuluString() );
        
        // $modifiedSinceIso8601 = $inicio->toIso8601ZuluString();

        // dd( $inicio );

        // $filter="2024-04-12T17:30Z";
        $query = $this->queryGetProducts();
        $variables = [
            "first" => 1,
            "after" => null,
            "before" => null,
            // "query" => "",
            "query" => "updated_at:>{$inicio}{$filter}",
        ];

        
        // dd( $variables );
                
        $hasNextPage = true;
        
        while ($hasNextPage) {
            
            $response = $this->shopiGraph($query, $variables);

            $hasNextPage = $response['data']['products']['pageInfo']['hasNextPage'];
            if ($hasNextPage) {
                $variables['after'] = $response['data']['products']['pageInfo']['endCursor'];
            }

            foreach ($response['data']['products']['edges'] as $clave => $producto) {   
                

                $fechaProducto = Carbon::parse($producto["node"]["updatedAt"]);

                if ($fechaProducto->gte($inicio)) {

                    $this->dispatch((new StockCommandJob( $producto ))->onQueue('procesarstock'));

                }else{
                    Log::info(":::::TERMINO STOCK:::::");
                    $hasNextPage=false;
                    break;
                }

            }

        }

    }
}

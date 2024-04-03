<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Traits\{GraphQlTrait,QuerysTrait,ShopiApiTrait};

class ExportProduct extends Command
{
    use GraphQlTrait,QuerysTrait,ShopiApiTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-product';

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

        $this->line("<fg=black;bg=blue>:::::::::::::::::::: INICIA PROCESO | ".date('Y-m-d H:i:s')." ::::::::::::::::::::</>");

        $query = $this->queryGetProducts();

        $agTEST=1;
        $agCount=0;

        $variables = [
            'first' => 10,
            'after' => null,
            'before' => null,
            'query' => '',
        ];        
        
        $hasNextPage = true;

        
        while ($hasNextPage) {

            $response = $this->shopiGraph($query, $variables);

            // dd( $response );


            foreach ($response['data']['products']['edges'] as $clave => $producto) {     

                // dd($producto);

                
                
                // $querySKU=$this->queryGetProducts();
                // // $skuVariables=[ "sku"=>$variante["node"]["sku"] ];
                // $skuVariables=[
                //     'first' => 1,
                //     'after' => null,
                //     'before' => null,
                //     'query'=>"title:{$producto["node"]["title"]}"
                //     // 'query' => "sku:{$variante["node"]["sku"]}",                        
                // ];
                // // dd( $skuVariables );
                // $busqueda=$this->shopiGraph($querySKU, $skuVariables,false );
                // dd( $busqueda );
                


                $input=$this->mapProductApi( $producto );    
                
                // dd( $input );
                
                $this->line("Creando {$input['title']}");            
                try {
                    $crearProducto=$this->crearProductoShopify( $input );
                    $tty=json_encode($crearProducto);
                    $this->line("<fg=black;bg=green> Creado {$input['title']}</>");
                } catch (\Exception $e) {
                    $this->line("<fg=black;bg=red> ERROR {$e}</>");
                    continue;
                }
            }

            $hasNextPage = $response['data']['products']['pageInfo']['hasNextPage'];
            if ($hasNextPage) {
                $variables['after'] = $response['data']['products']['pageInfo']['endCursor'];
                $agCount++;
                if( $agTEST==$agCount ){
                    $hasNextPage=false;
                }
            }

        }

        $this->line('<fg=black;bg=blue>::::::::::::::::::: TERMINO proceso | '.date('Y-m-d H:i:s').' ::::::::::::::::::::</>');

    }

}

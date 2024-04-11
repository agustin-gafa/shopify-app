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
    protected $signature = 'app:export-product {producto=-} {first=1} {limit=1}';

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

        $first=( $this->argument('producto')!="-"?1:$this->argument('first') );
        $filter=( $this->argument('producto')!="-"?"title:'{$this->argument('producto')}'":'' );        

        $query = $this->queryGetProducts();

        $agTEST=$this->argument('limit');
        $agCount=0;

        $variables = [
            'first' => (int)$first,
            'after' => null,
            'before' => null,
            'query' => $filter,
        ];        
        
        // dd( $variables );

        // $exiteB2B=$response = $this->shopiGraph($query, $variables,false);
        // if( !$exiteB2B["data"]["products"]["pageInfo"]["startCursor"] == null ){
        //     $this->line("El producto ya existe en B2B");    
        //     exit();            
        // }


        $hasNextPage = true;
        
        while ($hasNextPage) {

            $response = $this->shopiGraph($query, $variables);

            // dd( $response );            

            foreach ($response['data']['products']['edges'] as $clave => $producto) {     
                
                $input=$this->mapProductApi( $producto );    
                
                dd( $input );
                
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

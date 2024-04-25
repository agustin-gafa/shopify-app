<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use App\Traits\{ShopiApiTrait,GraphQlTrait};
use App\Jobs\{SyncStoresStockJob};
use Illuminate\Foundation\Bus\DispatchesJobs;

class WebHooksController extends Controller
{

    use ShopiApiTrait,GraphQlTrait,DispatchesJobs;

    public function orders(Request $request)
    {

        Log::info("::::SYNC STOCK:Verificar la autenticidad del webhook");
        if( env("WEBHOOK_TEST")==false ){            
            $hmac = $request->header('X-Shopify-Hmac-Sha256')??'';

            $tiendaSolicitud = $request->header('X-Shopify-Shop-Domain')??'';

            $selectStore=($request->header('X-Shopify-Shop-Domain')==env("SHOPIFY_URL")?false:true);

            $firmaWH=($tiendaSolicitud==env("SHOPIFY_URL")?env("SHOPIFY_WH_FIRMA"):env("B2B_WH_FIRMA") );

            $data = $request->getContent();
            $calculatedHmac = base64_encode(hash_hmac('sha256', $data,$firmaWH, true));
    
            if (!hash_equals($hmac, $calculatedHmac)) {
                Log::info("NO es valida la tienda, Fallo la autenticidad del webhook");
                return response()->json(['message' => 'Unauthorized action'], 401);                    
            }                	                
        }else{
            // se usa TRUE para pruebas
            $selectStore=env("WEBHOOK_TEST_RKN");
        }


        $webhookData = $request->all();        

        // Prteparar JOB

        $this->dispatch((new SyncStoresStockJob($webhookData,$selectStore))->onQueue('syncproducto'));
        
        return response()->json(["message"=>"Procesando productos"], 200);
    }


}

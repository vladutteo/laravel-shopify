<?php

namespace App\Jobs;

use App\Helpers\GenerateLiquidFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;

class AfterAuthorizeJob implements ShouldQueue {
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private object $shop;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shop)
    {
        $this->shop = $shop;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $apiVersion = config('shopify-app.api_version');

        $options = new Options();
        $options->setVersion($apiVersion);
        $options->setApiPassword($this->shop->password);

        $api = new BasicShopifyAPI($options);
        $api->setSession(new Session($this->shop->name, $this->shop->password));

        try {

            // Preluăm temele magazinului
            $result = $api->rest('get', '/admin/api/'.$apiVersion.'/themes.json');

            $currentThemeId = 0;

            foreach ($result['body']['themes'] as $key => $theme) {

                // preluăm id-ul temei curente
                if ($theme['role'] === 'main') {
                    $currentThemeId = $theme['id'];
                    break;
                }

            }

            // General fisierul liquid cu javascript-ul
            $raLiquid = (new GenerateLiquidFile($this->shop))->generate();

            // Adăugăm fișierul în temă.
            $api->rest('put', '/admin/api/'.$apiVersion.'/themes/'.$currentThemeId.'/assets.json', [
                'asset' => [
                    'key' => 'snippets/_ra.liquid',
                    'value' => $raLiquid
                ]
            ]);

            // Preluăm index-ul temei
            $themeLiquid = $api->rest('get',
            '/admin/themes/'.$currentThemeId."/assets.json", [
                'asset[key]' => 'layout/theme.liquid'
            ]);

            // include _ra in indexul temei
            $api->rest('put', '/admin/api/'.$apiVersion.'/themes/'.$currentThemeId.'/assets.json', [
                'asset' => [
                    'key' => 'layout/theme.liquid',
                    'value' => $themeLiquid['body']['asset']['value'] . "{% include '_ra' %}"
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('INSTALL ERROR ['. $this->shop->name . '] --- ' . $e->getMessage(). ' Line: ' . $e->getLine());
        }

    }
}

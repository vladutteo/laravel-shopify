<?php

namespace App\Http\Controllers;

use App\Helpers\GenerateLiquidFile;
use App\Jobs\AfterAuthorizeJob;
use App\Models\User;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;
use Osiset\ShopifyApp\Http\Middleware\VerifyShopify;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;

class HomeController extends \Osiset\ShopifyApp\Http\Controllers\HomeController {

    public function index(Request $request): ViewView {
        return View::make(
            'shopify-app::home.index',
            ['shop' => $request->user()]
        );
    }

    public function settings (Request $request): ViewView {

        return View::make(
            'settings', [
                'user' => $request->user(),
                'selectors' => $request->user()->shopSelectors->getAttributes()
            ]
        );

    }

    public function saveSettings(Request $request) {

        $user = $request->user();
        $selectors = $user->shopSelectors;

        $selectors->add_to_cart_selector = $request->ra_add_to_cart;
        $selectors->variations_selector = $request->ra_variation;
        $selectors->images_selector = $request->ra_image;
        $selectors->price_sale_id = $request->ra_price;
        $selectors->save();

        $this->createOrUpdateLiquidFile($request);
    }

    public function saveKeys (Request $request) {

        $inputs = $request->all();
        $user = $request->user();
        $user->tracking_key = $inputs['ra_domain_tracking_key'];
        $user->rest_key = $inputs['ra_api_token'];
        $user->save();

        $this->createOrUpdateLiquidFile($request);
    }

    public function keys (Request $request): ViewView {
        return View::make(
            'keys',
            ['user' => $request->user()]
        );
    }

    public function refreshFiles(Request $request) {
        $this->createOrUpdateLiquidFile($request);
    }

    protected function createOrUpdateLiquidFile($request) {

        $user = $request->user();

        $options = new Options();
        $options->setVersion(config('shopify-app.api_version'));
        $options->setApiPassword($user->password);

        $api = new BasicShopifyAPI($options);
        $api->setSession(new Session($user->name, $user->password));

        $result = $api->rest('get', '/admin/api/2021-01/themes.json');

        $currentThemeId = 0;

        foreach ($result['body']['themes'] as $key => $theme) {

            if ($theme['role'] === 'main') {
                $currentThemeId = $theme['id'];
                break;
            }

        }

        $raLiquid = (new GenerateLiquidFile($user))->generate();

        $api->rest('put', '/admin/api/2021-01/themes/'.$currentThemeId.'/assets.json', [
            'asset' => [
                'key' => 'snippets/_ra.liquid',
                'value' => $raLiquid
            ]
        ]);

    }
}

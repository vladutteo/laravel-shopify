<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;

class EventsController extends Controller {
    public function sendProduct(Request $request) {

        $raProduct = [
            'id' => '',
            'name' => '',
            'url' => '',
            'img' => '',
            'price' => '',
            'promo' => '',
            'brand' => false,
            'category' => [],
            'inventory' => [
                'variations' => false,
                'stock' => false
            ]
        ];

        $store = User::where('name', $request->shop)->first();

        $options = new Options();
        $options->setVersion(config('shopify-app.api_version'));
        $options->setApiPassword($store->password);

        $api = new BasicShopifyAPI($options);
        $api->setSession(new Session($store->name, $store->password));

        $product = $api->rest('get', '/admin/api/2021-07/products/'.$request->product_id.'.json')['body']['product'];

        $raProduct['id'] = $product['id'];
        $raProduct['name'] = $product['title'];
        $raProduct['url'] = 'https://' . $request->shop . '/products/' . rawurlencode($product['handle']);

        $price = $product['variants'][0]['compare_at_price'] > 0 ? $product['variants'][0]['compare_at_price'] : $product['variants'][0]['price'];

        $raProduct['price'] = empty($price) ? 0 : $price;
        $raProduct['promo'] = $product['variants'][0]['price'];
        $raProduct['img'] = $product['image']['src'];

        if (empty($raProduct['img']) || $raProduct['price'] == 0) {
            return '';
        }

        $smartCategories = (array)$api->rest('get', '/admin/api/2021-01/smart_collections.json', [
            'product_id' => $request->product_id
        ])['body']['smart_collections'];

        $customCategories = (array)$api->rest('get', '/admin/api/2021-01/custom_collections.json', [
            'product_id' => $request->product_id
        ])['body']['custom_collections'];

        $categories = array_merge($customCategories['container'],$smartCategories['container']);
        foreach ($categories as $key => $category) {
            $raProduct['category'][] = [
                'id' => $category['id'],
                'name' => $category['title'],
                'parent' => false,
                'breadcrumb' => [],
            ];
        }

        if (empty($raProduct['category'])) {
            return '';
        }

        if (count($product['variants'])) {

            foreach ($product['variants'] as $key => $variant) {
                if (!$raProduct['inventory']['variations'] && $variant['sku'] > 0) {
                    $raProduct['inventory']['variations'] = true;
                }
                $raProduct['inventory']['stock'][str_replace(' / ', '-', $variant['title'])] = $variant['sku'] > 0;
            }

        }


        return '
        if (window.location.pathname.indexOf("/products") !== -1) {
             _ra.sendProductInfo = '.json_encode($raProduct).';

             if (_ra.ready !== undefined) {
                 _ra.sendProduct(_ra.sendProductInfo);
             }
         }';
    }

    public function saveOrder(Request $request) {

        $user = User::whereName($request['shop'])->first();

        return '
                if (window.location.pathname.indexOf("/thank_you") !== -1
                || window.location.pathname.indexOf("/complete") !== -1
                || window.location.pathname.indexOf("/completed") !== -1) {

    (function()
      {
        ra_key = "'.$user->tracking_key.'";
        ra_params = {
            add_to_cart_button_id: "add_to_cart_button_id",
            price_label_id: "price_label_id",
        };

        var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
        document.location.protocol ? "https://" : "http://") + "tracking.retargeting.biz/v3/rajs/"+ra_key+".js";
        var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);
    })();

        var saveOrderInfo = {
            "order_no": Shopify.checkout.order_id,
            "lastname": Shopify.checkout.shipping_address.last_name,
            "firstname": Shopify.checkout.shipping_address.first_name,
            "email": Shopify.checkout.email,
            "phone": Shopify.checkout.shipping_address.phone,
            "state": Shopify.checkout.shipping_address.province,
            "city": Shopify.checkout.shipping_address.city,
            "address": Shopify.checkout.shipping_address.address1 + (Shopify.checkout.shipping_address.address2 !== "" ? " " + Shopify.checkout.shipping_address.address2 : ""),
            "discount": (Shopify.checkout.discount === null ? 0 : Shopify.checkout.discount.amount),
            "discount_code": (Shopify.checkout.discount === null ? "" : Shopify.checkout.discount.code),
            "shipping": Shopify.checkout.shipping_rate.price,
            "rebates": 0,
            "fees": 0,
            "total": Shopify.checkout.total_price
        };

        var saveOrderProducts = [];

        for(var i = 0; i < Shopify.checkout.line_items.length; i ++) {
            saveOrderProducts.push(
                {
                    "id": Shopify.checkout.line_items[i].product_id,
                    "quantity": Shopify.checkout.line_items[i].quantity,
                    "price": Shopify.checkout.line_items[i].price,
                    "variation_code": Shopify.checkout.line_items[i].variant_title.split(" / ").join("-")
                }
            );
        }
        setTimeout(function() {
            _ra = _ra || {};

            if( _ra.ready !== undefined ){
                _ra.saveOrder(saveOrderInfo, saveOrderProducts);
            }
        }, 1000);
        }';
    }
}

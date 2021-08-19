<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ShopSelectors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_selectors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('add_to_cart_button_id')->default('.product-form__cart-submit');
            $table->string('price_sale_id')->default('.price__regular');
            $table->string('add_to_cart_selector')->default('form[action="/cart/add"] [type="submit"]');
            $table->string('variations_selector')->default('variant-radios fieldset input:checked');
            $table->string('quantity_selector')->default('input[name="quantity"]');
            $table->string('images_selector')->default('.product__media-item');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('shop_selectors');
    }
}


@extends('shopify-app::layouts.default')

@section('styles')
    <link href='//fonts.googleapis.com/css?family=Lato:300,400,700,900,300italic' rel='stylesheet' type='text/css'>
    <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css">
    <link
        rel="stylesheet"
        href="https://unpkg.com/@shopify/polaris@6.6.0/dist/styles.css"
    />
@endsection

@section('content')
    <section>
        <form id="settings_form">
        <div class="span-2">
            <div class="input">
                <label for="ra_help_pages">Help Pages</label>
                <input type="text" name="ra_help_pages" id="ra_help_pages" placeholder="about-us" value="" disabled>
                <p class="description">Please add the handles for the pages on which you want the "visitHelpPage" trigger to
                    fire. Use a comma as a separator for listing multiple handles. For example:
                    http://yourshop.com/pages/<strong>about-us</strong> is represented by the "about-us" handle.</p>
            </div>

        </div>
        <div class="content span-2">
            <div class="info">
                <label>JavaScript Query Selectors</label>
                <p>The <a href="http://retargeting.biz">Retargeting</a> App should work out of the box for most themes. But,
                    as themes implementation can vary, in case there would be any problems with triggers not working as
                    expected you can modify the following settings to make sure the triggers are linked to the right theme
                    elements.</p>
            </div>

            <div class="input">
                <label for="ra_">Add To Cart Button</label>
                <input type="text" name="ra_add_to_cart" id="ra_add_to_cart"
                       placeholder='form[action="/cart/add"] [type="submit"]' value="{{ $selectors['add_to_cart_selector'] }}">
                <p class="description">Query selector for the button used to add a product to cart.</p>
            </div>


            <div class="input">
                <label for="ra_">Product Variants Buttons</label>
                <input type="text" name="ra_variation" id="ra_variation" placeholder='[data-option*="option"]'
                       value="{{ $selectors['variations_selector'] }}">
                <p class="description">Query selector for the product options used to change the preferences of the
                    product.</p>
            </div>

            <div class="input">
                <label for="ra_">Product Image</label>
                <input type="text" name="ra_image" id="ra_image" placeholder='.featured img' value="{{ $selectors['images_selector'] }}">
                <p class="description">Query selector for the main product image on a product page.</p>
            </div>

            <div class="input">
                <label for="ra_">Submit Review Button</label>
                <input type="text" name="ra_review" id="ra_review" placeholder='.new-review-form [type="submit"]'
                       value="" disabled>
                <p class="description">Query selector for the button used to submit a comment/review for a product.</p>
            </div>

            <div class="input">
                <label for="ra_">Price Container</label>
                <input type="text" name="ra_price" id="ra_price" placeholder="#price-preview" value="{{ $selectors['price_sale_id'] }}">
                <p class="description">Query selector for the main product price on a product page.</p>
            </div>


        </div>
        </form>
    </section>
@endsection

@section('scripts')
    @include('additional');
    <script>

        saveButton.subscribe(Button.Action.CLICK, data => {

            loading.dispatch(Loading.Action.START);

            utils.getSessionToken(app).then(token => {

                let fields = {};
                fields.ra_add_to_cart = document.getElementById('ra_add_to_cart').value;
                fields.ra_variation = document.getElementById('ra_variation').value;
                fields.ra_image = document.getElementById('ra_image').value;
                fields.ra_price = document.getElementById('ra_price').value;

                let xhttp = new XMLHttpRequest();

                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        toastSavedSelectors.dispatch(Toast.Action.SHOW);
                        loading.dispatch(Loading.Action.STOP);
                    }
                };

                xhttp.open("POST", "/save-settings");
                xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhttp.send(`token=${token}&ra_add_to_cart=${fields.ra_add_to_cart}&ra_variation=${fields.ra_variation}&ra_image=${fields.ra_image}&ra_price=${fields.ra_price}`);

            });

        });

    </script>
@endsection

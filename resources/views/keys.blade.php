
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

        <header class="span-1">
            <h1>Configuration</h1>
        </header>
        <div class="content span-3">
            <div class="input">
                <label for="ra_domain">Domain</label>
                <input type="text" name="ra_domain" id="ra_domain" placeholder="ex: yourshop.myshopify.com"
                       value="{{$user->name}}" disabled>
                <p class="description">The root domain where the shopify shop is installed.</p>
            </div>

            <div class="input">
                <label for="ra_domain_tracking_key">Tracking Key</label>
                <input type="text" name="ra_domain_tracking_key" id="ra_domain_tracking_key"
                       placeholder="ex: 1238BFDOS0SFODBSFKJSDFU2U32" value="{{$user->tracking_key}}">
                <p class="description">You can find your Secure Domain Tracking Key in your <a href="http://retargeting.biz">Retargeting</a>
                    account.</p>
            </div>

            <div class="input">
                <label for="ra_api_token">Token</label>
                <input type="text" name="ra_api_token" id="ra_api_token" placeholder="ex: 1238BFDOS0SFODBSFKJSDFU2U32"
                       value="{{$user->rest_key}}">
                <p class="description">You can find your Secure Token in your <a
                        href="http://retargeting.biz">Retargeting</a> account.</p>
            </div>

        </div>

    </section>
@endsection

@section('scripts')
    @include('additional')
<script>

    saveButton.subscribe(Button.Action.CLICK, data => {

        loading.dispatch(Loading.Action.START);

        utils.getSessionToken(app).then(token => {

            let fields = {};
            fields.ra_domain_tracking_key = document.getElementById('ra_domain_tracking_key').value;
            fields.ra_api_token = document.getElementById('ra_api_token').value;
            fields.ra_domain = document.getElementById('ra_domain').value;

            let xhttp = new XMLHttpRequest();

            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    toastSaved.dispatch(Toast.Action.SHOW);
                    loading.dispatch(Loading.Action.STOP);
                }
            };

            xhttp.open("POST", "/save-keys");
            xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhttp.send(`token=${token}&shop=${fields.ra_domain}&ra_api_token=${fields.ra_api_token}&ra_domain_tracking_key=${fields.ra_domain_tracking_key}`);

        });

    });

</script>
@endsection

<div class="newsletter-popup mfp-hide bg-img" id="newsletter-popup-form"
    style="background: #f1f1f1 no-repeat center/cover url('{{ config('custom.public_path') . '/frontAssets/assets/images/newsletter_popup_bg.jpg' }}');">
    <div class="newsletter-popup-content">
        <img src="{{ config('custom.public_path') . '/frontAssets/assets/images/logo.png'}}" width="111" height="44" alt="Logo" class="logo-newsletter">
        <h2>Subscribe to newsletter</h2>

        <p>
            Subscribe to the Porto mailing list to receive updates on new arrivals, special offers and our
            promotions.
        </p>

        <form action="#">
            <div class="input-group">
                <input type="email" class="form-control" id="newsletter-email" name="newsletter-email"
                    placeholder="Your email address" required />
                <input type="submit" class="btn btn-primary" value="Submit" />
            </div>
        </form>
        <div class="newsletter-subscribe">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="0" id="show-again" />
                <label for="show-again" class="custom-control-label">
                    Don't show this popup again
                </label>
            </div>
        </div>
    </div>
    <!-- End .newsletter-popup-content -->

    <button title="Close (Esc)" type="button" class="mfp-close">
        ×
    </button>
</div>
<form action="#" class="extraOrderColumnsPopup form-horizontal product-page justify-content-md-center product-form">
    <h2>Send tracking number</h2>
    <div class="form-group text-widget productName">
        <label for="tracking_number">
            Tracking number
        </label>
        <input
            type="text"
            id="tracking_number"
            name="tracking_number"
            class="form-control"
            value=""
            required="required"
        >
    </div>

    <input type="hidden" name="order_original_id_order" value="{$order->id}" />

    <div class="form-group">
        <button
                type="submit"
                id="carrierNumberSubmit"
                name="carrierNumberSubmit"
                class="btn-primary btn btn">
            Add tracking number
        </button>
    </div>
    <div class="form-group clogElement" style="display: none">
        <div class="clog"></div>
    </div>
</form>


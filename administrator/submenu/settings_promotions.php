<h2 class="mb-4">Settings: Promotions & Discount</h2>
<div class="alert alert-info" role="alert">
  Manage promotions and discount codes here. This is a sub-menu item under Settings.
</div>
<div class="card shadow-sm p-4">
    <h5>Create New Discount Code</h5>
    <form>
        <div class="mb-3">
            <label for="discountCode" class="form-label">Discount Code</label>
            <input type="text" class="form-control" id="discountCode" placeholder="e.g., SUMMER20">
        </div>
        <div class="mb-3">
            <label for="discountValue" class="form-label">Discount Value (%)</label>
            <input type="number" class="form-control" id="discountValue" step="1" placeholder="e.g., 20">
        </div>
        <div class="mb-3">
            <label for="expiryDate" class="form-label">Expiry Date</label>
            <input type="date" class="form-control" id="expiryDate">
        </div>
        <button type="submit" class="btn btn-primary">Save Discount</button>
    </form>
</div>
// --- Toast auto-show on page load ---
document.addEventListener('DOMContentLoaded', function () {
  var toastElList = [].slice.call(document.querySelectorAll('.toast'));
  toastElList.forEach(function (toastEl) {
    var toast = new bootstrap.Toast(toastEl);
    toast.show();
  });

  // --- Tab persistence on page load ---
  if (typeof activeTab !== 'undefined' && activeTab) {
    var triggerEl = document.querySelector(`[data-bs-toggle="tab"][href="#${activeTab}"]`);
    if (triggerEl) {
      var tab = new bootstrap.Tab(triggerEl);
      tab.show();
    }
  }

  // --- Clean URL on tab change and after toast display ---
  function cleanUrl() {
    if (window.history.replaceState) {
      const url = new URL(window.location);
      if (url.searchParams.has('success') || url.searchParams.has('error')) {
        url.searchParams.delete('success');
        url.searchParams.delete('error');
        window.history.replaceState({ path: url.href }, '', url.href);
      }
    }
  }

  // Clean URL when any toast is hidden
  const toastContainer = document.querySelector('.toast-container');
  if (toastContainer) {
    toastContainer.addEventListener('hidden.bs.toast', function () {
      cleanUrl();
    });
  }

  // Clean URL on tab clicks
  document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabLink => {
    tabLink.addEventListener('click', function () {
      const tabId = this.getAttribute('href').substring(1);
      if (window.history.replaceState) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({ path: url.href }, '', url.href);
      }
      cleanUrl();
    });
  });

  // Initial clean-up in case the page loaded with success/error in URL without user interaction
  cleanUrl();
});

// --- Toast function (global) ---
function showToast(message, type = 'success') {
  const toastContainer = document.querySelector('.toast-container');
  if (!toastContainer) {
    console.error('Toast container not found!');
    return;
  }

  const iconClass = type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill';
  const bgColorClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';

  const toastHtml = `
    <div class="toast align-items-center ${bgColorClass} border-0" role="alert" aria-live="assertive" aria-atomic="true"
         data-bs-autohide="true" data-bs-delay="3000">
      <div class="d-flex">
        <div class="toast-body">
          <i class="${iconClass} me-2"></i>
          ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;

  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = toastHtml;
  const toastElement = tempDiv.firstElementChild;
  toastContainer.appendChild(toastElement);

  const toast = new bootstrap.Toast(toastElement);
  toast.show();

  toastElement.addEventListener('hidden.bs.toast', function () {
    toastElement.remove();
    // Clean URL after toast is dismissed
    if (typeof cleanUrl === 'function') cleanUrl();
  });
}

// --- Address Dropdowns ---
let regions = [], provinces = [], cities = [];
function loadJSON(url) {
  return fetch(url).then(res => res.json());
}
function populateRegions() {
  const regionSelect = document.getElementById('region');
  if (!regionSelect) return;
  regionSelect.innerHTML = '<option value="">Select Region</option>';
  regions.forEach(region => {
    let opt = document.createElement('option');
    opt.value = region.key;
    opt.textContent = region.long + ' (' + region.name + ')';
    regionSelect.appendChild(opt);
  });
}
function populateProvinces(regionKey) {
  const provinceSelect = document.getElementById('province');
  if (!provinceSelect) return;
  provinceSelect.innerHTML = '<option value="">Select Province</option>';
  provinces.filter(p => p.region === regionKey)
    .forEach(prov => {
      let opt = document.createElement('option');
      opt.value = prov.key;
      opt.textContent = prov.name;
      provinceSelect.appendChild(opt);
    });
}
function populateCities(provinceKey) {
  const citySelect = document.getElementById('city');
  if (!citySelect) return;
  citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
  cities.filter(c => c.province === provinceKey)
    .forEach(city => {
      let opt = document.createElement('option');
      opt.value = city.name;
      opt.textContent = city.name;
      citySelect.appendChild(opt);
    });
}
document.addEventListener('DOMContentLoaded', async function () {
  if (!document.getElementById('region')) return;
  regions = await loadJSON('../assets/ph-address/regions.json');
  provinces = await loadJSON('../assets/ph-address/provinces.json');
  cities = await loadJSON('../assets/ph-address/cities.json');
  populateRegions();
  if (typeof address !== 'undefined' && address.region) {
    document.getElementById('region').value = address.region;
    populateProvinces(address.region);
    if (address.province) {
      document.getElementById('province').value = address.province;
      populateCities(address.province);
      if (address.city) {
        document.getElementById('city').value = address.city;
      }
    }
  }
  document.getElementById('region').addEventListener('change', function () {
    populateProvinces(this.value);
    document.getElementById('province').value = '';
    document.getElementById('city').value = '';
  });
  document.getElementById('province').addEventListener('change', function () {
    populateCities(this.value);
    document.getElementById('city').value = '';
  });
});

// --- Remove from Cart AJAX ---
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.remove-from-cart-form').forEach(form => {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(this);
      fetch('../config/remove_from_cart.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800); // Wait for toast before reload
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An error occurred while removing the item.', 'error');
        });
    });
  });
});

// --- Quantity + / - AJAX ---
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.update-quantity-form').forEach(form => {
    const minusBtn = form.querySelector('.btn-qty-minus');
    const plusBtn = form.querySelector('.btn-qty-plus');
    const qtyInput = form.querySelector('input[name="quantity"]');

    minusBtn.addEventListener('click', function () {
      let qty = parseInt(qtyInput.value, 10);
      if (qty > 1) {
        qtyInput.value = qty - 1;
        form.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });

    plusBtn.addEventListener('click', function () {
      let qty = parseInt(qtyInput.value, 10);
      qtyInput.value = qty + 1;
      form.dispatchEvent(new Event('submit', { cancelable: true }));
    });

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(form);
      fetch('../config/update_cart_quantity.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(res => res.json())
        .then(data => {
          showToast(data.message, data.success ? 'success' : 'error');
          if (data.success) {
            setTimeout(() => location.reload(), 800);
          }
        })
        .catch(() => showToast('Error updating quantity.', 'error'));
    });
  });
});
<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=" . urlencode("Please login to access your account."));
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, gender, contact_number, shipping_address, profile_image FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $gender, $contact_number, $shipping_address, $profile_image);
$stmt->fetch();
$stmt->close();
// Note: $conn is closed here after fetching user data.
// It will be re-opened in the activity log section.

$address = null;
if ($shipping_address) {
    $address = json_decode($shipping_address, true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bong Bicycle Shop</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../assets/css/my_account.css">

  <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">
</head>
<body>
<?php include '../components/navigation.php'; ?>


<?php
$success = isset($_GET['success']) ? urldecode($_GET['success']) : '';
$error = isset($_GET['error']) ? urldecode($_GET['error']) : '';
?>
<div aria-live="polite" aria-atomic="true" class="position-relative">
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <?php if ($success): ?>
      <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-autohide="true" data-bs-delay="5000" id="successToast">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($success); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-autohide="true" data-bs-delay="5000" id="errorToast">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-x-circle-fill me-2"></i>
            <?php echo htmlspecialchars($error); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="container-fluid mt-5">
  <div class="row">
    <div class="col-md-3 px-3 mb-4"><div class="card shadow-sm rounded-0">
        <div class="card-body text-center">
          <?php if (!empty($profile_image)): ?>
            <img src="../uploads/profile/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" class="rounded-circle mb-2" style="width:100px;height:100px;object-fit:cover;">
          <?php else: ?>
            <i class="bi bi-person-circle" style="font-size:3rem;color:var(--primary);"></i>
          <?php endif; ?>
          <h4 class="mt-3 mb-1" style="color:var(--primary);"><?php echo htmlspecialchars($name); ?></h4>
          <p class="mb-3 text-muted"><?php echo htmlspecialchars($email); ?></p>
          <form action="../config/upload_profile_image.php" method="POST" enctype="multipart/form-data" class="mt-2">
            <input type="file" name="profile_image" accept="image/*" class="form-control form-control-sm mb-2" style="max-width:200px;display:inline-block;" required>
            <button type="submit" class="btn btn-accent btn-sm">Upload Profile</button>
          </form>
        </div>
        <div class="list-group list-group-flush">
          <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
            <i class="bi bi-person me-2"></i> My Profile
          </a>
          <a href="#cart" class="list-group-item list-group-item-action" data-bs-toggle="tab">
            <i class="bi bi-cart me-2"></i> My Cart
          </a>
          <a href="#orders" class="list-group-item list-group-item-action" data-bs-toggle="tab">
            <i class="bi bi-bag-check me-2"></i> My Orders
          </a>
          <a href="#activity_log" class="list-group-item list-group-item-action" data-bs-toggle="tab">
            <i class="bi bi-activity me-2"></i> Activity Log
          </a>
          <a href="../auth/logout.php" class="list-group-item list-group-item-action text-danger">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-9 px-3"><div class="tab-content card shadow-sm p-4 rounded-0">
        <div class="tab-pane fade show active" id="profile">
          <h5 class="mb-3" style="color:var(--primary);">My Profile</h5>
          <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
          <p><strong>Gender:</strong> <?php echo htmlspecialchars($gender ?? ''); ?></p>
          <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($contact_number ?? ''); ?></p>
          <?php if ($address): ?>
            <div class="mb-3">
              <strong>Shipping Address:</strong><br>
              <?php echo htmlspecialchars($address['street'] ?? ''); ?>
              <?php if (!empty($address['barangay'])) echo ', Barangay ' . htmlspecialchars($address['barangay']); ?>
              <?php if (!empty($address['city'])) echo ', ' . htmlspecialchars($address['city']); ?>
              <?php if (!empty($address['province'])) echo ', ' . htmlspecialchars($address['province']); ?>
              <?php if (!empty($address['region'])) echo ', ' . htmlspecialchars($address['region']); ?>
            </div>
            <button type="button" class="btn btn-accent btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
  <i class="bi bi-pencil-square"></i> Edit Profile
</button>
          <?php else: ?>
            <div class="mb-3 text-muted"><em>No shipping address saved yet.</em></div>
          <?php endif; ?>
          <hr>
          <h6 class="mb-2" style="color:var(--primary);">Edit Shipping Address</h6>
          <form id="shippingAddressForm" method="POST" action="../config/save_address.php">
            <div class="mb-2">
              <label for="region" class="form-label">Region</label>
              <select id="region" name="region" class="form-select" required>
                <option value="">Select Region</option>
              </select>
            </div>
            <div class="mb-2">
              <label for="province" class="form-label">Province</label>
              <select id="province" name="province" class="form-select" required>
                <option value="">Select Province</option>
              </select>
            </div>
            <div class="mb-2">
              <label for="city" class="form-label">City/Municipality</label>
              <select id="city" name="city" class="form-select" required>
                <option value="">Select City/Municipality</option>
              </select>
            </div>
            <div class="mb-2">
              <label for="barangay" class="form-label">Barangay</label>
              <input id="barangay" name="barangay" class="form-control" type="text" placeholder="Enter Barangay" required
                value="<?php echo isset($address['barangay']) ? htmlspecialchars($address['barangay']) : ''; ?>">
            </div>
            <div class="mb-2">
              <label for="street" class="form-label">Street Address</label>
              <input id="street" name="street" class="form-control" type="text" placeholder="Enter Street Address" required
                value="<?php echo isset($address['street']) ? htmlspecialchars($address['street']) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-accent mt-2">Save Address</button>
          </form>
        </div>
        <div class="tab-pane fade" id="cart">
          <h5 class="mb-3" style="color:var(--primary);">My Cart</h5>
          <p>Your cart items will appear here.</p>
        </div>
        <div class="tab-pane fade" id="orders">
          <h5 class="mb-3" style="color:var(--primary);">My Orders</h5>
          <p>Your order history will appear here.</p>
        </div>

        <div class="tab-pane fade" id="activity_log">
          <h5 class="mb-3" style="color:var(--primary);">My Activity Log</h5>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>Date & Time</th>
                  <th>Activity Type</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Re-establish connection for this block as it was closed earlier
                // This ensures the connection is fresh for fetching activity logs
                require_once __DIR__ . '/../config/db.php';
                $user_id = $_SESSION['user_id'];
                // Select from customer_activity_logs table
                $stmt_logs = $conn->prepare("SELECT activity_type, description, activity_timestamp FROM customer_activity_logs WHERE user_id=? ORDER BY activity_timestamp DESC LIMIT 100");
                $stmt_logs->bind_param("i", $user_id);
                $stmt_logs->execute();
                $result_logs = $stmt_logs->get_result();

                if ($result_logs->num_rows > 0) {
                    while ($log = $result_logs->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars(date('M d, Y h:i A', strtotime($log['activity_timestamp']))) . '</td>';
                        echo '<td>' . htmlspecialchars($log['activity_type']) . '</td>';
                        echo '<td>' . htmlspecialchars($log['description']) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="3" class="text-center">No activity history found.</td></tr>';
                }
                $stmt_logs->close();
                $conn->close(); // Close connection after fetching logs for this block
                ?>
              </tbody>
            </table>
          </div>
        </div>
        </div>
    </div>
  </div>
</div>


<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="../config/edit_profile.php">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="editName" class="form-label">Name</label>
          <input type="text" class="form-control" id="editName" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        <div class="mb-3">
          <label for="editGender" class="form-label">Gender</label>
          <select class="form-select" id="editGender" name="gender" required>
            <option value="Male" <?php if($gender=='Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if($gender=='Female') echo 'selected'; ?>>Female</option>
            <option value="Prefer not to say" <?php if($gender=='Prefer not to say') echo 'selected'; ?>>Prefer not to say</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="editContact" class="form-label">Contact Number</label>
          <input type="text" class="form-control" id="editContact" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-accent">Save Changes</button>
      </div>
    </form>
  </div>
</div>
<br><br><br>

<?php include '../components/footer.php'; ?>


<script>
  document.addEventListener('DOMContentLoaded', function () {
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(function (toastEl) {
      var toast = new bootstrap.Toast(toastEl);
      toast.show();
    });
  });
</script>

<script>
let regions = [], provinces = [], cities = [];
const address = <?php echo json_encode($address ?? []); ?>;

function loadJSON(url) {
  return fetch(url).then(res => res.json());
}

function populateRegions() {
  const regionSelect = document.getElementById('region');
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
  citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
  cities.filter(c => c.province === provinceKey)
    .forEach(city => {
      let opt = document.createElement('option');
      opt.value = city.name;
      opt.textContent = city.name;
      citySelect.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', async function() {
  regions = await loadJSON('../assets/ph-address/regions.json');
  provinces = await loadJSON('../assets/ph-address/provinces.json');
  cities = await loadJSON('../assets/ph-address/cities.json');
  populateRegions();

  // Set region if exists
  if (address.region) {
    document.getElementById('region').value = address.region;
    populateProvinces(address.region);

    // Set province if exists
    if (address.province) {
      document.getElementById('province').value = address.province;
      populateCities(address.province);

      // Set city if exists
      if (address.city) {
        document.getElementById('city').value = address.city;
      }
    }
  }

  document.getElementById('region').addEventListener('change', function() {
    populateProvinces(this.value);
    document.getElementById('province').value = '';
    document.getElementById('city').value = '';
  });
  document.getElementById('province').addEventListener('change', function() {
    populateCities(this.value);
    document.getElementById('city').value = '';
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>
</html>
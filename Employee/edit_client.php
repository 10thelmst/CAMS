<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = get_cams_pdo();
$clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : (isset($_POST['client_id']) ? (int)$_POST['client_id'] : 0);

$message = '';
$message_type = '';

if ($clientId <= 0) {
    header('Location: client_history.php');
    exit;
}

// Handle POST update (returns JSON for AJAX requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_client') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '');
        $contact_no = trim($_POST['contact_no'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sex = $_POST['sex'] ?? null;
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
        $address1 = trim($_POST['address1'] ?? '');
        $province_code = trim($_POST['province_code'] ?? null);
        $city_code = trim($_POST['city_code'] ?? null);
        $barangay_code = trim($_POST['barangay_code'] ?? null);
        $region_code = trim($_POST['region_code'] ?? null);
        $is_ofw = isset($_POST['is_ofw']) && ($_POST['is_ofw'] === '1' || $_POST['is_ofw'] === 1) ? 1 : 0;

        // OFW related posted fields (may be absent)
        $ofw_name = trim($_POST['ofw_name'] ?? '');
        $ofw_country = trim($_POST['country'] ?? '');
        $ofw_employment_type = trim($_POST['employment_type'] ?? '');
        $ofw_relationship = trim($_POST['relationship'] ?? '');

        $stmt = $pdo->prepare("UPDATE clients SET first_name = :first_name, middle_name = :middle_name, last_name = :last_name, suffix = :suffix, contact_no = :contact_no, email = :email, sex = :sex, dob = :dob, address1 = :address1, province_code = :province_code, city_code = :city_code, barangay_code = :barangay_code, region_code = :region_code, is_ofw = :is_ofw WHERE id = :id");
        $stmt->execute([
            ':first_name' => $first_name,
            ':middle_name' => $middle_name,
            ':last_name' => $last_name,
            ':suffix' => $suffix,
            ':contact_no' => $contact_no,
            ':email' => $email,
            ':sex' => $sex,
            ':dob' => $dob,
            ':address1' => $address1,
            ':province_code' => $province_code,
            ':city_code' => $city_code,
            ':barangay_code' => $barangay_code,
            ':region_code' => $region_code,
            ':is_ofw' => $is_ofw,
            ':id' => $clientId
        ]);

        // Upsert OFW information if the table/columns exist
        try {
            $ofwCols = $pdo->query('SHOW COLUMNS FROM ofw_information')->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($ofwCols)) {
                $available = [];
                if (in_array('ofw_name', $ofwCols, true)) $available['ofw_name'] = $ofw_name;
                if (in_array('country', $ofwCols, true)) $available['country'] = $ofw_country;
                if (in_array('employment_type', $ofwCols, true)) $available['employment_type'] = $ofw_employment_type;
                if (in_array('relationship', $ofwCols, true)) $available['relationship'] = $ofw_relationship;

                if (!empty($available)) {
                    // check if a record exists
                    $chk = $pdo->prepare('SELECT id FROM ofw_information WHERE client_id = :client_id LIMIT 1');
                    $chk->execute([':client_id' => $clientId]);
                    $existing = $chk->fetch(PDO::FETCH_ASSOC);
                    if ($existing) {
                        // build update
                        $sets = [];
                        $params = [':client_id' => $clientId];
                        foreach ($available as $col => $val) {
                            $sets[] = "$col = :$col";
                            $params[":$col"] = $val;
                        }
                        $sql = 'UPDATE ofw_information SET ' . implode(', ', $sets) . ' WHERE client_id = :client_id';
                        $u = $pdo->prepare($sql);
                        $u->execute($params);
                    } else {
                        // insert
                        $cols = ['client_id'];
                        $place = [':client_id'];
                        $params = [':client_id' => $clientId];
                        foreach ($available as $col => $val) {
                            $cols[] = $col;
                            $place[] = ":$col";
                            $params[":$col"] = $val;
                        }
                        $sql = 'INSERT INTO ofw_information (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $place) . ')';
                        $i = $pdo->prepare($sql);
                        $i->execute($params);
                    }
                }
            }
        } catch (Throwable $e) {
            // ignore OFW upsert errors to avoid blocking client update
        }

        echo json_encode(['ok' => true, 'message' => 'Client record updated successfully.']);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'message' => 'Failed to update client: ' . $e->getMessage()]);
        exit;
    }
}

// Load client
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $clientId]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header('Location: client_history.php');
    exit;
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

ob_start();
?>
<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Edit Client</h3>
        <div class="ml-auto"><a href="client_history.php?client_id=<?php echo h($clientId); ?>" class="btn btn-default btn-sm">Back</a></div>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo h($message_type); ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" id="client_form">
            <input type="hidden" name="action" value="update_client">
            <input type="hidden" name="client_id" value="<?php echo h($clientId); ?>">

            <div class="row">
                <div class="col-md-4 form-group">
                    <label class="required">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?php echo h($client['last_name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4 form-group">
                    <label class="required">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?php echo h($client['first_name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="<?php echo h($client['middle_name'] ?? ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 form-group">
                    <label>Suffix</label>
                    <input type="text" name="suffix" class="form-control" value="<?php echo h($client['suffix'] ?? ''); ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label class="required">Contact Number</label>
                    <input type="text" name="contact_no" id="contact_no" class="form-control" value="<?php echo h($client['contact_no'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo h($client['email'] ?? ''); ?>">
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Gender</label>
                    <select name="sex" class="form-control">
                        <option value="" <?php echo ($client['sex'] ?? '') === '' ? 'selected' : ''; ?>>--</option>
                        <option value="Male" <?php echo ($client['sex'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($client['sex'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?php echo h($client['dob'] ?? ''); ?>">
                </div>
                <div class="col-md-6 form-group">
                    <label>Address 1</label>
                    <input type="text" name="address1" class="form-control" value="<?php echo h($client['address1'] ?? ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Province Code</label>
                    <input type="text" name="province_code" class="form-control" value="<?php echo h($client['province_code'] ?? ''); ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label>City Code</label>
                    <input type="text" name="city_code" class="form-control" value="<?php echo h($client['city_code'] ?? ''); ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label>Barangay Code</label>
                    <input type="text" name="barangay_code" class="form-control" value="<?php echo h($client['barangay_code'] ?? ''); ?>">
                </div>
            </div>

            <div class="card-footer bg-white text-right">
                <a href="client_history.php?client_id=<?php echo h($clientId); ?>" class="btn btn-default mr-2">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
<script>
// Minimal client-side niceties to match create_client2 behaviour
document.addEventListener('DOMContentLoaded', function() {
    const contact = document.getElementById('contact_no');
    if (contact) contact.placeholder = '09XXXXXXXXX';
});
</script>
<?php
$content = ob_get_clean();
require_once 'layout.php';
?>
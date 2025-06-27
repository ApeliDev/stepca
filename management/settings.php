<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = (new Database())->connect();

// Get current settings
$stmt = $db->query("SELECT * FROM system_settings");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
$settingsMap = [];
foreach ($settings as $setting) {
    $settingsMap[$setting['setting_key']] = $setting;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        // Update settings
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        
        $success = 'Settings updated successfully!';
        
        // Refresh settings
        $stmt = $db->query("SELECT * FROM system_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settingsMap = [];
        foreach ($settings as $setting) {
            $settingsMap[$setting['setting_key']] = $setting;
        }
    }
}

$csrfToken = generateCSRFToken();

include '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="sidebar">
        <?php include '../includes/admin_sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>System Settings</h2>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="setting-group">
                <h3>General Settings</h3>
                
                <div class="form-group">
                    <label for="site_maintenance">Site Maintenance Mode</label>
                    <select id="site_maintenance" name="settings[site_maintenance]">
                        <option value="0" <?php echo $settingsMap['site_maintenance']['setting_value'] == '0' ? 'selected' : ''; ?>>No</option>
                        <option value="1" <?php echo $settingsMap['site_maintenance']['setting_value'] == '1' ? 'selected' : ''; ?>>Yes</option>
                    </select>
                    <p class="description"><?php echo htmlspecialchars($settingsMap['site_maintenance']['description']); ?></p>
                </div>
                
                <div class="form-group">
                    <label for="registration_open">New Registrations</label>
                    <select id="registration_open" name="settings[registration_open]">
                        <option value="1" <?php echo $settingsMap['registration_open']['setting_value'] == '1' ? 'selected' : ''; ?>>Open</option>
                        <option value="0" <?php echo $settingsMap['registration_open']['setting_value'] == '0' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                    <p class="description"><?php echo htmlspecialchars($settingsMap['registration_open']['description']); ?></p>
                </div>
            </div>
            
            <div class="setting-group">
                <h3>Payment Settings</h3>
                
                <div class="form-group">
                    <label for="registration_fee">Registration Fee (KES)</label>
                    <input type="number" id="registration_fee" name="settings[registration_fee]" 
                           value="<?php echo htmlspecialchars($settingsMap['registration_fee']['setting_value']); ?>" min="0" step="1">
                    <p class="description"><?php echo htmlspecialchars($settingsMap['registration_fee']['description']); ?></p>
                </div>
                
                <div class="form-group">
                    <label for="referral_bonus">Referral Bonus (KES)</label>
                    <input type="number" id="referral_bonus" name="settings[referral_bonus]" 
                           value="<?php echo htmlspecialchars($settingsMap['referral_bonus']['setting_value']); ?>" min="0" step="1">
                    <p class="description"><?php echo htmlspecialchars($settingsMap['referral_bonus']['description']); ?></p>
                </div>
            </div>
            
            <button type="submit" class="btn-save">Save Settings</button>
        </form>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
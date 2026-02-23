<?php
/**
 * POST /api/package-features/save
 * Bulk-save features for a package (replace strategy — auth required)
 * Body: { "package_id": 1, "features": ["Feature A", "Feature B"] }
 * Features are plain strings; display_order is assigned by array index (0-based).
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$auth  = requireAuth();
$input = getJsonInput();

$packageId = (int) ($input['package_id'] ?? 0);
$features  = $input['features'] ?? [];

if ($packageId <= 0) {
    jsonResponse(400, 'error', null, 'Valid package_id is required.');
}
if (!is_array($features)) {
    jsonResponse(400, 'error', null, 'features must be an array of strings.');
}

// Verify package exists
$check = $conn->prepare("SELECT id FROM packages WHERE id = ?");
$check->bind_param('i', $packageId);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Package not found.');
}
$check->close();

// Replace strategy: delete all existing, then bulk-insert
$conn->begin_transaction();

$del = $conn->prepare("DELETE FROM package_features WHERE package_id = ?");
$del->bind_param('i', $packageId);
if (!$del->execute()) {
    $del->close();
    $conn->rollback();
    logError('Package features delete failed: ' . $conn->error);
    jsonResponse(500, 'error', null, 'Failed to save features.');
}
$del->close();

if (!empty($features)) {
    $ins = $conn->prepare(
        "INSERT INTO package_features (package_id, feature_text, display_order) VALUES (?, ?, ?)"
    );
    $order = 0;
    foreach ($features as $feat) {
        $featClean = sanitize((string) $feat);
        if ($featClean === '') continue;
        $ins->bind_param('isi', $packageId, $featClean, $order);
        if (!$ins->execute()) {
            $ins->close();
            $conn->rollback();
            logError('Package feature insert failed: ' . $conn->error);
            jsonResponse(500, 'error', null, 'Failed to save features.');
        }
        $order++;
    }
    $ins->close();
}

$conn->commit();
jsonResponse(200, 'success', null, 'Features saved successfully.');

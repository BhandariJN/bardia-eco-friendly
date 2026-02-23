<?php
/**
 * POST /api/comparison/save
 * Replace entire comparison matrix (auth required)
 * Body: {
 *   "features": [
 *     { "feature": "Organic Meals", "values": [{"package_id":1,"type":"yes","text":null}, ...] }
 *   ]
 * }
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$auth  = requireAuth();
$input = getJsonInput();

$features = $input['features'] ?? [];
if (!is_array($features)) {
    jsonResponse(400, 'error', null, 'features must be an array.');
}

$allowedTypes = ['yes', 'no', 'text'];

$conn->begin_transaction();

// Wipe existing
if (!$conn->query("DELETE FROM comparison_values") || !$conn->query("DELETE FROM comparison_features")) {
    $conn->rollback();
    logError('Comparison clear failed: ' . $conn->error);
    jsonResponse(500, 'error', null, 'Failed to save comparison data.');
}

$insFeat = $conn->prepare("INSERT INTO comparison_features (feature) VALUES (?)");
$insVal  = $conn->prepare(
    "INSERT INTO comparison_values (comparison_feature_id, package_id, type, text) VALUES (?, ?, ?, ?)"
);

foreach ($features as $featRow) {
    $featName = sanitize((string) ($featRow['feature'] ?? ''));
    if ($featName === '') continue;

    $insFeat->bind_param('s', $featName);
    if (!$insFeat->execute()) {
        $conn->rollback();
        logError('Comparison feature insert failed: ' . $conn->error);
        jsonResponse(500, 'error', null, 'Failed to save comparison data.');
    }
    $featId = (int) $conn->insert_id;

    $values = $featRow['values'] ?? [];
    if (!is_array($values)) continue;

    foreach ($values as $val) {
        $pkgId = (int)    ($val['package_id'] ?? 0);
        $type  = (string) ($val['type']       ?? 'yes');
        $text  = isset($val['text']) ? sanitize((string) $val['text']) : null;

        if ($pkgId <= 0 || !in_array($type, $allowedTypes, true)) continue;

        $insVal->bind_param('iiss', $featId, $pkgId, $type, $text);
        if (!$insVal->execute()) {
            $conn->rollback();
            logError('Comparison value insert failed: ' . $conn->error);
            jsonResponse(500, 'error', null, 'Failed to save comparison data.');
        }
    }
}

$insFeat->close();
$insVal->close();
$conn->commit();

jsonResponse(200, 'success', null, 'Comparison data saved successfully.');

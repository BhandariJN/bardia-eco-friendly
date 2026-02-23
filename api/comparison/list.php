<?php
/**
 * GET /api/comparison/list
 * Returns full comparison matrix: features with per-package values (public)
 */

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

// Get all packages
$pkgResult = $conn->query("SELECT id, name FROM packages ORDER BY id ASC");
$packages  = [];
while ($p = $pkgResult->fetch_assoc()) {
    $p['id'] = (int) $p['id'];
    $packages[] = $p;
}

// Get all comparison features
$featResult = $conn->query("SELECT id, feature FROM comparison_features ORDER BY id ASC");
$features   = [];
while ($f = $featResult->fetch_assoc()) {
    $f['id']     = (int) $f['id'];
    $f['values'] = [];
    $features[$f['id']] = $f;
}

// Get all comparison values
if (!empty($features)) {
    $valResult = $conn->query(
        "SELECT comparison_feature_id, package_id, type, text FROM comparison_values"
    );
    while ($v = $valResult->fetch_assoc()) {
        $featId = (int) $v['comparison_feature_id'];
        $pkgId  = (int) $v['package_id'];
        if (isset($features[$featId])) {
            $features[$featId]['values'][] = [
                'package_id' => $pkgId,
                'type'       => $v['type'],
                'text'       => $v['text'],
            ];
        }
    }
}

jsonResponse(200, 'success', [
    'packages' => $packages,
    'features' => array_values($features),
]);

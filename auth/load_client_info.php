<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = get_cams_pdo();
    $clientId = isset($_GET['client_id']) ? (int) $_GET['client_id'] : 0;

    if ($clientId <= 0) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'message' => 'Client ID is required.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $clientColumns = $pdo->query('SHOW COLUMNS FROM clients')->fetchAll(PDO::FETCH_COLUMN);
    $concernColumns = $pdo->query('SHOW COLUMNS FROM concerns')->fetchAll(PDO::FETCH_COLUMN);
    $statusColumns = $pdo->query('SHOW COLUMNS FROM status_history')->fetchAll(PDO::FETCH_COLUMN);

    $profileFields = [
        'id' => 'c.id',
        'first_name' => 'c.first_name',
        'middle_name' => 'c.middle_name',
        'last_name' => 'c.last_name',
        'suffix' => 'c.suffix',
        'contact_no' => 'c.contact_no',
        'email' => 'c.email',
        'sex' => 'c.sex',
        'dob' => 'c.dob',
        'is_ofw' => 'c.is_ofw',
        'address1' => 'c.address1',
        'region_code' => 'c.region_code',
        'province_code' => 'c.province_code',
        'city_code' => 'c.city_code',
        'barangay_code' => 'c.barangay_code',
    ];

    $selectParts = [];
    foreach ($profileFields as $alias => $expr) {
        if (in_array(str_replace('c.', '', $expr), $clientColumns, true) || $alias === 'id') {
            $selectParts[] = $expr . ' AS ' . $alias;
        }
    }

    $selectParts[] = "TRIM(CONCAT_WS(' ', " .
        (in_array('first_name', $clientColumns, true) ? 'c.first_name' : "''") . ", " .
        (in_array('middle_name', $clientColumns, true) ? 'c.middle_name' : "''") . ", " .
        (in_array('last_name', $clientColumns, true) ? 'c.last_name' : "''") . ", " .
        (in_array('suffix', $clientColumns, true) ? 'c.suffix' : "''") . ")) AS full_name";

    $selectSql = implode(', ', $selectParts);
    $clientStmt = $pdo->prepare("SELECT {$selectSql} FROM clients c WHERE c.id = :client_id LIMIT 1");
    $clientStmt->execute([':client_id' => $clientId]);
    $client = $clientStmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo json_encode([
            'ok' => false,
            'message' => 'Client not found.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $client['full_name'] = trim((string) ($client['full_name'] ?? ''));

    $caseQuery = "SELECT * FROM concerns WHERE client_id = :client_id ORDER BY created_at DESC, id DESC LIMIT 20";
    $caseStmt = $pdo->prepare($caseQuery);
    $caseStmt->execute([':client_id' => $clientId]);
    $cases = $caseStmt->fetchAll(PDO::FETCH_ASSOC);

    // Load OFW information (if any) for the client so UIs can prefill OFW fields
    $ofwStmt = $pdo->prepare("SELECT ofw_first_name, ofw_middle_name, ofw_last_name, ofw_suffix, ofw_name, country, employment_type, relationship FROM ofw_information WHERE client_id = :client_id LIMIT 1");
    $ofwStmt->execute([':client_id' => $clientId]);
    $ofwInfo = $ofwStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    foreach ($cases as &$caseRow) {
        foreach ($caseRow as $key => $value) {
            if ($value === null) {
                $caseRow[$key] = '';
            }
        }

        $caseId = $caseRow['id'] ?? null;
        if ($caseId) {
            $statusStmt = $pdo->prepare("SELECT * FROM status_history WHERE concern_id = :concern_id ORDER BY changed_at DESC, id DESC LIMIT 10");
            $statusStmt->execute([':concern_id' => $caseId]);
            $caseRow['status_history'] = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $caseRow['status_history'] = [];
        }
    }
    unset($caseRow);

    $response = [
        'ok' => true,
        'client' => array_merge($client, $ofwInfo),
        'cases' => $cases,
        'history_count' => count($cases)
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Unable to load client information: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

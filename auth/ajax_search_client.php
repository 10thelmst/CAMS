<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = get_cams_pdo();
    $term = trim($_GET['term'] ?? '');

    if ($term === '') {
        echo json_encode(['ok' => false, 'clients' => [], 'message' => 'Search term is required.']);
        exit;
    }

    $cleanTerm = preg_replace('/\s+/', ' ', $term);
    $digitsOnly = preg_replace('/\D+/', '', $term);
    $likeTerm = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cleanTerm) . '%';
    $nameLike = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cleanTerm) . '%';
    $phoneLike = '%' . ($digitsOnly !== '' ? $digitsOnly : $cleanTerm) . '%';

    $columns = $pdo->query("SHOW COLUMNS FROM clients")->fetchAll(PDO::FETCH_COLUMN);
    $hasMiddleName = in_array('middle_name', $columns, true);
    $hasSuffix = in_array('suffix', $columns, true);
    $hasEmail = in_array('email', $columns, true);
    $hasContactNo = in_array('contact_no', $columns, true);
    $hasFirstName = in_array('first_name', $columns, true);
    $hasLastName = in_array('last_name', $columns, true);

    $fullNameExpression = "TRIM(CONCAT_WS(' ', " .
        ($hasFirstName ? 'c.first_name' : "''") . ", " .
        ($hasMiddleName ? 'c.middle_name' : "''") . ", " .
        ($hasLastName ? 'c.last_name' : "''") . "))";

    $sql = "
        SELECT
            c.id,
            {$fullNameExpression} AS fullname,
            c.contact_no,
            c.email,
            c.sex,
            o.ofw_name,
            o.country,
            (
                SELECT COUNT(*)
                FROM concerns cs
                WHERE cs.client_id = c.id
                AND cs.status <> 'Closed'
            ) AS active_concerns
        FROM clients c
        LEFT JOIN ofw_information o ON o.client_id = c.id
        WHERE (
            " . ($hasContactNo ? "REPLACE(REPLACE(REPLACE(REPLACE(c.contact_no, ' ', ''), '-', ''), '(', ''), ')', '') LIKE :phone_like" : "0 = 1") . "
            OR " . ($hasFirstName || $hasLastName ? "{$fullNameExpression} LIKE :name_like" : "0 = 1") . "
            OR " . ($hasEmail ? "c.email LIKE :email_like" : "0 = 1") . "
            OR CAST(c.id AS CHAR) = :id_exact
        )
        ORDER BY
            CASE
                WHEN " . ($hasContactNo ? "REPLACE(REPLACE(REPLACE(REPLACE(c.contact_no, ' ', ''), '-', ''), '(', ''), ')', '') = :phone_exact" : "0") . " THEN 1
                WHEN {$fullNameExpression} = :name_exact THEN 2
                WHEN " . ($hasContactNo ? "REPLACE(REPLACE(REPLACE(REPLACE(c.contact_no, ' ', ''), '-', ''), '(', ''), ')', '') LIKE :phone_prefix" : "0") . " THEN 3
                ELSE 4
            END,
            c.id DESC
        LIMIT 10
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':phone_like', '%' . preg_replace('/\D+/', '', $term) . '%');
    $stmt->bindValue(':name_like', $nameLike);
    $stmt->bindValue(':email_like', $likeTerm);
    $stmt->bindValue(':id_exact', $cleanTerm);
    $stmt->bindValue(':phone_exact', preg_replace('/\D+/', '', $term));
    $stmt->bindValue(':name_exact', $cleanTerm);
    $stmt->bindValue(':phone_prefix', preg_replace('/\D+/', '', $term) . '%');

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['fullname'] = trim((string)($row['fullname'] ?? ''));
        $row['contact_no'] = $row['contact_no'] ?? '';
        $row['email'] = $row['email'] ?? '';
        $row['sex'] = $row['sex'] ?? '';
        $row['ofw_name'] = $row['ofw_name'] ?? 'N/A';
        $row['country'] = $row['country'] ?? 'N/A';
        $row['active_concerns'] = (int)($row['active_concerns'] ?? 0);
    }
    unset($row);

    echo json_encode([
        'ok' => true,
        'clients' => $rows,
        'count' => count($rows),
        'term' => $cleanTerm
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'clients' => [],
        'message' => 'Search failed: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

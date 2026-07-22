<?php
header('Content-Type: application/json');

$json_path = __DIR__ . '/assets/data/psgc.json';

if (!file_exists($json_path)) {
    echo json_encode(['error' => 'PSGC JSON file not found']);
    exit;
}

$data = json_decode(file_get_contents($json_path), true);
$action = $_GET['action'] ?? '';

if ($action === 'get_regions') {
    $regions = array_map(function($r) {
        return ['code' => $r['code'], 'name' => $r['name']];
    }, $data['regions']);
    echo json_encode($regions);
    exit;
}

if ($action === 'get_provinces') {
    $region_code = $_GET['region_code'] ?? '';
    foreach ($data['regions'] as $region) {
        if ($region['code'] === $region_code) {
            $provinces = array_map(function($p) {
                return ['code' => $p['code'], 'name' => $p['name']];
            }, $region['provinces']);
            echo json_encode($provinces);
            exit;
        }
    }
    echo json_encode([]);
    exit;
}

if ($action === 'get_cities') {
    $region_code = $_GET['region_code'] ?? '';
    $province_code = $_GET['province_code'] ?? '';
    foreach ($data['regions'] as $region) {
        if ($region['code'] === $region_code) {
            foreach ($region['provinces'] as $province) {
                if ($province['code'] === $province_code) {
                    $cities = array_map(function($c) {
                        return ['code' => $c['code'], 'name' => $c['name']];
                    }, $province['cities']);
                    echo json_encode($cities);
                    exit;
                }
            }
        }
    }
    echo json_encode([]);
    exit;
}

if ($action === 'get_barangays') {
    $region_code = $_GET['region_code'] ?? '';
    $province_code = $_GET['province_code'] ?? '';
    $city_code = $_GET['city_code'] ?? '';

    foreach ($data['regions'] as $region) {
        if ($region['code'] === $region_code) {
            foreach ($region['provinces'] as $province) {
                if ($province['code'] === $province_code) {
                    foreach ($region['cities'] as $city) {
                        if ($city['code'] === $city_code) {
                            echo json_encode($city['barangays']);
                            exit;
                        }
                    }
                }
            }
        }
    }
    echo json_encode([]);
    exit;
}
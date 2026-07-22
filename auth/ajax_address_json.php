<?php
// C:\xampp\htdocs\cams\auth\ajax_address_json.php
header('Content-Type: application/json');

// Root path to the config folder containing isaacdarcilla JSON files
$config_dir = dirname(__DIR__) . '/config/';

$action = $_GET['action'] ?? '';

// Helper function to read a JSON file safely
function load_json($filepath) {
    if (!file_exists($filepath)) {
        echo json_encode(['error' => 'File not found: ' . basename($filepath)]);
        exit;
    }
    return json_decode(file_get_contents($filepath), true) ?? [];
}

// 1. Get Regions from region.json
if ($action === 'get_regions') {
    $regions = load_json($config_dir . 'region.json');
    $output = array_map(function($r) {
        return [
            'code' => $r['region_code'],
            'name' => $r['region_name']
        ];
    }, $regions);
    echo json_encode($output);
    exit;
}

// 2. Get Provinces from province.json filtered by region_code
if ($action === 'get_provinces') {
    $region_code = $_GET['region_code'] ?? '';
    $provinces = load_json($config_dir . 'province.json');
    
    $filtered = array_filter($provinces, function($p) use ($region_code) {
        return $p['region_code'] === $region_code;
    });

    $output = array_map(function($p) {
        return [
            'code' => $p['province_code'],
            'name' => $p['province_name']
        ];
    }, array_values($filtered));

    echo json_encode($output);
    exit;
}

// 3. Get Cities/Municipalities from city.json filtered by province_code
if ($action === 'get_cities') {
    $province_code = $_GET['province_code'] ?? '';
    $cities = load_json($config_dir . 'city.json');

    $filtered = array_filter($cities, function($c) use ($province_code) {
        return $c['province_code'] === $province_code;
    });

    $output = array_map(function($c) {
        return [
            'code' => $c['city_code'],
            'name' => $c['city_name']
        ];
    }, array_values($filtered));

    echo json_encode($output);
    exit;
}

// 4. Get Barangays from barangay.json filtered by city_code
if ($action === 'get_barangays') {
    $city_code = $_GET['city_code'] ?? '';
    $barangays = load_json($config_dir . 'barangay.json');

    $filtered = array_filter($barangays, function($b) use ($city_code) {
        return $b['city_code'] === $city_code;
    });

    $output = array_map(function($b) {
        return [
            'code' => $b['brgy_code'],
            'name' => $b['brgy_name']
        ];
    }, array_values($filtered));

    echo json_encode($output);
    exit;
}
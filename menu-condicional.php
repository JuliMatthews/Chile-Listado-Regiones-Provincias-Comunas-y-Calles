<?php

$basePath = dirname(__FILE__);

$comunas = require $basePath . '/comunas.php';

function getCSVOptions($filePath) {
    $options = [];
    if (file_exists($filePath)) {
        $handle = fopen($filePath, 'r');
        while (($line = fgetcsv($handle)) !== false) {
            $line = array_map('trim', $line);
            if (!empty($line[0]) && strtolower($line[0]) !== 'name') {
                $options[] = $line[0];
            }
        }
        fclose($handle);
    }
    return $options;
}

function getCallesFromFile($filePath) {
    $calles = [];
    if (file_exists($filePath)) {
        $handle = fopen($filePath, 'r');
        $first = true;
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') continue;
            if ($first) {
                $first = false;
                if (strpos($line, "\xEF\xBB\xBF") === 0) {
                    $line = trim(substr($line, 3));
                }
                if (strtolower($line) === 'name') continue;
            }
            if (!empty($line)) {
                $calles[] = $line;
            }
        }
        fclose($handle);
    }
    return $calles;
}

$action = $_GET['action'] ?? '';
$result = [];

switch ($action) {
    case 'regiones':
        $result = getCSVOptions($basePath . '/region.csv');
        break;

    case 'provincias':
        $region = $_GET['region'] ?? '';
        if ($region && isset($comunas[$region])) {
            $result = array_keys($comunas[$region]);
        } elseif ($region) {
            $regionPath = $basePath . '/Region/' . $region;
            $result = getCSVOptions($regionPath . '/provincia.csv');
        }
        break;

    case 'comunas':
        $region = $_GET['region'] ?? '';
        $provincia = $_GET['provincia'] ?? '';
        if ($region && $provincia && isset($comunas[$region][$provincia])) {
            $result = $comunas[$region][$provincia];
        } elseif ($region && $provincia) {
            $provinciaPath = $basePath . '/Region/' . $region . '/' . $provincia;
            $result = getCSVOptions($provinciaPath . '/comuna.csv');
        }
        break;

    case 'calles':
        $region = $_GET['region'] ?? '';
        $provincia = $_GET['provincia'] ?? '';
        $comuna = $_GET['comuna'] ?? '';
        $regionPath = $basePath . '/Region';
        if ($region && $provincia && $comuna) {
            $callesPath = $regionPath . '/' . $region . '/' . $provincia . '/calles_' . strtolower(str_replace(' ', '_', $comuna)) . '.csv';
            if (file_exists($callesPath)) {
                $result = getCallesFromFile($callesPath);
            } else {
                $result = [];
            }
        }
        break;

    default:
        http_response_code(400);
        $result = ['error' => 'Acción no válida'];
}

header('Content-Type: application/json');
echo json_encode($result);
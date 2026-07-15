<?php
/**
 * 血统/配对图谱入口
 */
require_once __DIR__ . '/app/bootstrap.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$pdo = get_pdo();

require_once __DIR__ . '/app/controllers/PedigreeController.php';
$controller = new PedigreeController($pdo);

// API routes
if ($action === 'tree' && isset($_GET['id'])) {
    $controller->pedigreeTree($_GET['id']);
    exit;
}
if ($action === 'set_parents') {
    $controller->setParents();
    exit;
}
if ($action === 'create_pairing') {
    $controller->createPairing();
    exit;
}
if ($action === 'delete_pairing') {
    $controller->deletePairing();
    exit;
}
if ($action === 'search_pigeons') {
    $controller->searchPigeons();
    exit;
}
if ($action === 'certificate_search_pigeon') {
    $controller->findPigeonForCertificate();
    exit;
}

// Page routes
if (preg_match('#^pedigree/pairings/?$#', $path)) {
    $controller->myPairings();
    exit;
}
if (preg_match('#^pedigree/strain/([^/]+)/race-results/?$#', $path, $m)) {
    $controller->strainRaceResults($m[1]);
    exit;
}
if (preg_match('#^pedigree/strain/([^/]+)/?$#', $path, $m)) {
    $controller->strainDetail($m[1]);
    exit;
}
if (preg_match('#^pedigree/certificate/?$#', $path)) {
    $controller->certificate();
    exit;
}

// Default: strains list
$controller->strains();
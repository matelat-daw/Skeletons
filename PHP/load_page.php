<?php
header('Content-Type: text/html; charset=UTF-8');

if (!isset($_GET['page'])) {
    http_response_code(400);
    echo 'Error: No se especificó la página';
    exit;
}

$page = $_GET['page'];
$allowed_pages = ['home', 'about', 'contact', 'register', 'login'];

if (!in_array($page, $allowed_pages)) {
    http_response_code(404);
    echo 'Error: Página no encontrada';
    exit;
}

switch ($page) {
    case 'home':
        include 'html/home/index.php';
        break;
    case 'about':
        include 'html/about/index.php';
        break;
    case 'contact':
        include 'html/contact/index.php';
        break;
    case 'register':
        include 'html/register/index.php';
        break;
    case 'login':
        include 'html/login/index.php';
        break;
    default:
        http_response_code(404);
        echo 'Error: Página no encontrada';
        break;
}
?>

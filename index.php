<?php
$url = isset($_GET['url']) ? $_GET['url'] : 'home';
$route = explode('/', $url);

// Example: /user/profile → $route[0] = 'user', $route[1] = 'profile'

switch ($route[0]) {
    case 'home':
        require 'pages/home.php';
        break;
    case 'about':
        require 'pages/about.php';
        break;
    default:
        require 'pages/404.php';
        break;
}
?>

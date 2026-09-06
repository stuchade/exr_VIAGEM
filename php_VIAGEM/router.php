<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/api.php') {
	require __DIR__ . '/backend/api.php';
	return true;
}

if ($path === '/' || $path === '/index.html') {
	header('Content-Type: text/html; charset=utf-8');
	readfile(__DIR__ . '/frontend/index.html');
	return true;
}

return false;

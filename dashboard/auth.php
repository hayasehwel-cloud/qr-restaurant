<?php
session_start();

function require_login() {
    if (empty($_SESSION['restaurant_id'])) {
        header('Location: /dashboard/login.php');
        exit;
    }
}

function current_restaurant() {
    return [
        'id'   => $_SESSION['restaurant_id'],
        'name' => $_SESSION['restaurant_name'],
    ];
}

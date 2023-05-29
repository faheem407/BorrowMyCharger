<?php

$view = new stdClass();

$view->pageTitle = 'Home';

require_once('Models/UserDataSet.php');
$userDataSet = new UserDataSet();

// Proceed only if logged in, else go to login page
if(isset($_COOKIE['token']) && $userDataSet->verifyAuthToken($_COOKIE['token'])) {
    require_once('Models/ChargePointDataSet.php');
    $chargePointDataSet = new ChargePointDataSet();
    $response = json_decode($chargePointDataSet->getAllChargingPointAndTheirUsers());
    if ($response->status_code != 200) {
        $view->error = $response->message;
    }
    else {
        $view->chargePoints = $response->data;
    }
    require_once('Views/map.phtml');
}
else {
    setcookie('token', '', time() - 1, '/');
    header('Location: login.php');
    exit;
}

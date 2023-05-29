<?php

$view = new stdClass();
$view->pageTitle = 'Home';

require_once('Models/UserDataSet.php');
$userDataSet = new UserDataSet();

// Proceed if logged in, else send to login page
if (isset($_COOKIE['token']) && $userDataSet->verifyAuthToken($_COOKIE['token'])) {
    // Check if the user has requested to send an email to a charge point owner
    if (isset($_POST['submit'])) {
        // Report error if missing fields
        if (!(isset($_POST['duration']) && $_POST['duration'] != "") && !(isset($_POST['units']) && $_POST['units'] != "")) {
            $view->error = 'Please fill in all fields';
        }
        // Send email if form is valid
        else {
            $owner_email = $_POST['owner_email'];
            $datetime = new DateTime($_POST['datetime']);
            $duration = $_POST['duration'];
            $units = $_POST['units'];
            $message = 'Email sent to '. $owner_email. ' requesting charging point access on '. $datetime->format('d-m-Y h:i:s A') . ' for ';
            if ($duration) {
                $message .= $duration . ' mins';
            }
            else {
                $message .= $units . ' kWh';
            }
            $view->success = $message;
        }
    }

    // Load user data

    require_once('Models/ChargePointDataSet.php');
    $chargePointDataSet = new ChargePointDataSet();

    $view->maxPages = $chargePointDataSet->getMaxPages();
    $response = json_decode($chargePointDataSet->getChargePoints());
    $view->data = null;

    if ($response->status_code != 200) {
        $view->error = $response->message;
    } else {
        $view->data = $response->data;
        $view->max_pages = $response->max_pages;
    }
    require_once('Views/index.phtml');
} else {
    setcookie('token', '', time() - 1, '/');
    header('Location: login.php');
    exit;
}

<?php
$view = new stdClass();

$view->pageTitle = 'Login';

require_once('Models/UserDataSet.php');
$userDataSet = new UserDataSet();

// Redirect if logged in
if (isset($_COOKIE['token'])) {
    if($userDataSet->verifyAuthToken($_COOKIE['token'])) {
        header('Location: index.php');
        exit;
    }
}

// Login is not already logged in
if (isset($_POST['login'])) {
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        $view->error = 'Please fill all the fields.';
    }
    else {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $response = json_decode($userDataSet->login($email, $password));
    
        if ($response->status_code != 200) {
            $view->error = $response->message;
        }
        else {
            setcookie('token', $response->token, time() + 60 * 60 * 24, '/');
            header('Location: index.php');
            exit;
        }
    }
}

require_once('Views/login.phtml');
?>
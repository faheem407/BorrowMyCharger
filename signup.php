<?php
$view = new stdClass();

$view->pageTitle = 'SignUp';

// Sign up if form is submitted, and go to login page if signup is successful
if (isset($_POST['signUp'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $confirmPassword = $_POST['confirmPassword'];
    $picture = null;

    if ($_FILES['profile_picture']['tmp_name']) {
        $picture = $_FILES['profile_picture'];
    }

    if ($password != $confirmPassword) {
        $view->error = 'Passwords do not match';
    } else {
        require_once('Models/UserDataSet.php');
        $userDataSet = new UserDataSet();
        $response = json_decode($userDataSet->register($email, $fullname, $password, $phone));

        if ($response->status_code != 200) {
            $view->error = $response->message;
        } else {
            if ($picture != null) {
                $ext = strtolower(pathinfo($picture['name'], PATHINFO_EXTENSION));
                try {
                    $filename = $response->new_id . '.' . $ext;
                    move_uploaded_file($picture["tmp_name"], 'images/' . $response->new_id . '.' . $ext);
                    $response = json_decode($userDataSet->update($response->new_id, array('img_url' => $filename)));
                    if ($response->status_code!= 200) {
                        throw new Exception($response->message);
                    }
                }
                catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
            header('Location: login.php');
            exit;
        }
    }
}

require_once('Views/signup.phtml');

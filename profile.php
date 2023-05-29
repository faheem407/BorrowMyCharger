<?php

$view = new stdClass();

$view->pageTitle = 'Profile';

require_once('Models/User.php');
require_once('Models/UserDataSet.php');
$userDataSet = new UserDataSet();

// If not logged in, redirect to login page
if (!(isset($_COOKIE['token']) && $userDataSet->verifyAuthToken($_COOKIE['token']))) {
    setcookie('token', '', time() - 1, '/');
    header('Location: login.php');
    exit;
}

// Update details if any form is submitted
$active_user = UserDataSet::getActiveUser();

if (isset($_POST['updateProfileDetails'])) {
    try {
        $form = $_POST;
        $active_user_details = $active_user->getDetails();
        unset($form['updateProfileDetails']);
        $dataForm = $form;
        unset($dataForm['password']);
        unset($dataForm['confirmPassword']);

        $updateArray = array();

        foreach ($dataForm as $key => $val) {
            if ($dataForm[$key] != $active_user_details[$key]) {
                $updateArray[$key] = $val;
            }
        }

        if ($form['password']) {
            if ($form['password'] != $form['confirmPassword']) {
                $view->error = 'Passwords do not match!';
            } else {
                $updateArray['pass'] = $form['password'];
            }
        }

        $response = $userDataSet->update($active_user->getUserId(), $updateArray);
        $response = json_decode($response);
        if ($response->status_code !== 200) {
            throw new Exception($response->message);
        }
        $userDataSet::updateActiveUserDetails($updateArray);
    } catch (Exception $e) {
        $view->error = $e->getMessage();
    }
} else if (isset($_POST['updateProfilePicture'])) {
    $picture = $_FILES['new_dp'] ?? null;
    if ($picture != null) {
        $ext = strtolower(pathinfo($picture['name'], PATHINFO_EXTENSION));
        $user_id = $_SESSION['active_user']->getUserId();
        try {
            $filename = $user_id . '.' . $ext;
            move_uploaded_file($picture["tmp_name"], 'images/' . $user_id . '.' . $ext);
            $response = json_decode($userDataSet->update($user_id, array('img_url' => $filename)));
            if ($response->status_code != 200) {
                throw new Exception($response->message);
            }
            $userDataSet::updateActiveUserDetails(array('img_url' => $filename));
            header('Location: profile.php');
            exit;
        } catch (Exception $e) {
            $view->error = $e->getMessage();
        }
    }
} else if(isset($_POST['method'])){
    require_once('Models/ChargePointDataSet.php');
    $chargePointDataSet = new ChargePointDataSet();

    if ($_POST['method'] == 'add') {
        try {
            $response = json_decode($chargePointDataSet->add($active_user->getUserId(), $_POST['address1'], $_POST['address2'], $_POST['post_code'], (float) $_POST['lat'], (float) $_POST['lng'], (float) $_POST['cost']));
            if ($response->status_code !== 200) {
                throw new Exception($response->message);
            }
            $active_user->setIsOwner();
            $active_user->setChargePoint($chargePointDataSet->getChargePoint($active_user->getUserId()));
        } catch (Exception $e) {
            $view->error = $e->getMessage();
        }
    } else if ($_POST['method'] == 'update') {
        try {
            $to_change = array();
            $current_data = $active_user->getChargePoint()->getFormData();
            foreach ($_POST as $key => $val) {
                if (array_key_exists($key, $current_data)) {
                    if ($current_data[$key] != $val) {
                        if ($key == 'lat' || $key == 'lng' || $key == 'cost') {
                            $val = (float) $val;
                        }
                        $to_change[$key] = $val;
                    }
                }
            }
            if ($_POST['address1'] == $current_data['address1'] && $_POST['address2'] == $current_data['address2'] && $_POST['post_code'] == $current_data['post_code']) {
                unset($to_change['lat']);
                unset($to_change['lng']);
            }
            $response = $chargePointDataSet->update($active_user->getUserId(), $to_change);

            $response = json_decode($response);
            if ($response->status_code !== 200) {
                throw new Exception($response->message);
            }

            $active_user->setChargePoint($chargePointDataSet->getChargePoint($active_user->getUserId()));
        } catch (Exception $e) {
            $view->error = $e->getMessage();
        }
    } else {
        try {
            $cp_id = $active_user->getChargePoint()->getChargePointId();
            $response = $chargePointDataSet->removeChargePoint($cp_id);
            $response = json_decode($response);
            if ($response->status_code !== 200) {
                throw new Exception($response->message);
            }

            $active_user->setChargePoint(null);
            $active_user->setIsOwner(false);
        } catch (Exception $e) {
            $view->error = $e->getMessage();
        }
    }
}

$view->active_user_data = UserDataSet::getActiveUser();
require_once('Views/profile.phtml');

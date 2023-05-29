<?php

require_once('Models/UserDataSet.php');

// Logout user
UserDataSet::removeActiveUser();
setcookie('token', '', time() - 1, '/');
header('Location: login.php');
exit;

<?php

session_start();
require_once('Models/Database.php');
require_once('Models/User.php');
require_once('Models/ChargePoint.php');
require_once('Models/ChargePointDataSet.php');

class UserDataSet
{
    protected $_dbHandle, $_dbInstance;

    // Set the active user of the session
    public static function setActiveUser(User $user): void
    {
        $_SESSION['active_user'] = $user;
    }

    // Get current active user of the session
    public static function getActiveUser(): User
    {
        return $_SESSION['active_user'];
    }

    // Update the active user of the session
    public static function updateActiveUserDetails($update_details): void
    {
        $active_user = $_SESSION['active_user'];
        $user_details = $active_user->getDetails();
        $user_details['user_id'] = $active_user->getUserId();
        $user_details['img_url'] = $active_user->getImgUrl();
        $isOwner = $active_user->isOwner();
        $chargePoint = null;
        if ($isOwner) {
            $chargePoint = $active_user->getChargePoint();
        }

        foreach ($update_details as $key => $value) {
            if (array_key_exists($key, $user_details)) {
                $user_details[$key] = $value;
            }
        }

        $_SESSION['active_user'] = new User($user_details, $isOwner, $chargePoint);
    }

    // Remove the active user of the session
    public static function removeActiveUser(): void
    {
        unset($_SESSION['active_user']);
    }

    public function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }

    // Verify authentication token of the user
    public function verifyAuthToken(string $token): bool
    {
        $user_data = json_decode(base64_decode($token), true);
        try {
            $user_id = $user_data['user_id'];
            $sqlQuery = 'SELECT COUNT(*) AS num_of_users FROM user WHERE user_id=:user_id';
            $stmt = $this->_dbHandle->prepare($sqlQuery);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute(); // execute the PDO statement

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['num_of_users'] != 1) {
                return false;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Generates the auth token for the logged in user
    protected static function generateAuthToken(int $user_id): string
    {
        $user_data = array('user_id' => $user_id);
        return base64_encode(json_encode($user_data));
    }

    // Login the user
    public function login(string $email, string $password): string
    {
        try {
            $sql = 'SELECT * FROM user WHERE email=:email';
            $stmt = $this->_dbHandle->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result)
            {
                return json_encode(array('status_code' => 401, 'message' => 'User doesn\'t exist'));
            }
            if (!password_verify($password, $result['pass'])) {
                return json_encode(array('status_code' => 401, 'message' => 'Wrong email or password'));
            }

            $chargePointDataSet = new ChargePointDataSet();
            $chargePoint = $chargePointDataSet->getChargePoint($result['user_id']);
            $isOwner = False;
            if ($chargePoint) {
                $isOwner = True;
            }

            self::setActiveUser(new User($result, $isOwner, $chargePoint));
            return json_encode(array('status_code' => 200, 'message' => 'Logged in', 'token' => self::generateAuthToken($result['user_id'])));
        } catch (Exception $e) {
            return json_encode(array('status_code' => 404, 'message' => 'User not found'));
        }
    }

    // Register a new user
    public function register(string $email, string $fullname, string $password, string $phone = null, string $img_url = 'default.jpg'): string
    {
        try {
            $sql = 'INSERT INTO user (email, fullname, pass, phone, img_url) VALUES (:email, :fullname, :pass, :phone, :img_url)';
            $stmt = $this->_dbHandle->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':fullname', $fullname);
            $stmt->bindValue(':pass', password_hash($password, PASSWORD_DEFAULT));
            $stmt->bindValue(':phone', $phone);
            $stmt->bindValue(':img_url', $img_url);
            $stmt->execute();
            return json_encode(array('status_code' => 200, 'new_id' => $this->_dbHandle->lastInsertId()));
        } catch (Exception $e) {
            return json_encode(array('status_code' => 500, 'message' => $e->getMessage()));
        }
    }

    // Updated the user details
    public function update(string $user_id, array $to_change): string
    {
        if (count($to_change) == 0) {
            return json_encode(array('status_code' => 404, 'message' => 'No parameters to change'));
        }

        try {
            $sql = 'UPDATE user SET ';
            foreach (array_keys($to_change) as $idx => $key) {
                if ($idx != 0) {
                    $sql .= ', ';
                }
                $sql .= $key . '=:' . $key;
            }
            $sql .= ' WHERE user_id=:user_id';

            $stmt = $this->_dbHandle->prepare($sql);
            $stmt->bindValue(':user_id', $user_id);
            foreach ($to_change as $key => $value) {
                if (strtolower($key) == 'pass') {
                    $value = password_hash($value, PASSWORD_DEFAULT);
                }
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();
            return json_encode(array('status_code' => 200, 'message' => 'Updated'));
        } catch (Exception $e) {
            return json_encode(array('status_code' => 500, 'message' => $e->getMessage()));
        }
    }
}

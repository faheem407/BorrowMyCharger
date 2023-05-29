<?php

require_once('ChargePoint.php');

class User {
    protected $_id, $_fullName, $_phoneNumber, $_imgUrl, $_email, $_isOwner, $_charge_point;
    
    public function __construct($user_details, $is_owner=false, $charge_point=null) {
        $this->_id = $user_details['user_id'];
        $this->_fullName = $user_details['fullname'];
        $this->_phoneNumber = $user_details['phone'];
        $this->_imgUrl = $user_details['img_url'];
        $this->_email = $user_details['email'];
        $this->_isOwner = $is_owner;
        $this->_charge_point = $charge_point;
    }

    // Getters
    public function getUserId(): int {
        return $this->_id;
    }
   
    public function getFullName(): string {
       return $this->_fullName;
    }
    
    public function getPhoneNumber(): string {
       return $this->_phoneNumber;
    }
    
    public function getImgUrl(): string {
       return $this->_imgUrl;
    }
    
    public function getEmail(): string {
       return $this->_email;
    }

    public function isOwner(): bool {
        return $this->_isOwner;
    }

    // Get only the public details of the user
    public function getDetails(): array {
        return array(
            'fullname' => $this->getFullName(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhoneNumber()
        );
    }

    // Get charge Point owned by the user
    public function getChargePoint(): ChargePoint|Exception {
        if (!$this->_isOwner) {
            throw new Exception("User is not an owner of any charging point");
        }
        return $this->_charge_point;
    }

    // Set charge point for the user
    public function setChargePoint(ChargePoint|null $charge_point): void {
        $this->_charge_point = $charge_point;
    }

    // Set if the user owns a charge point
    public function setIsOwner(bool $is_owner=True): void {
        $this->_isOwner = $is_owner;
    }
}

<?php

class ChargePoint {
    protected $_id, $_address1, $_address2, $_post_code, $_latitude, $_longitude, $_cost;
    
    public function __construct(array $charge_point_details) {
        $this->_id = $charge_point_details['cp_id'];
        $this->_address1 = $charge_point_details['address1'];
        $this->_address2 = $charge_point_details['address2'];
        $this->_post_code = $charge_point_details['post_code'];
        $this->_cost = $charge_point_details['cost'];
    }

    // Getters
    public function getChargePointId(): int {
        return $this->_id;
    }
   
    public function getAddress1(): string {
       return $this->_address1;
    }
    
    public function getAddress2(): string {
       return $this->_address2;
    }
    
    public function getPostCode(): string {
       return $this->_post_code;
    }
    
    public function getLatitude(): float {
       return $this->_latitude;
    }

    public function getLongitude(): float {
        return $this->_longitude;
    }

    public function getCost(): float {
        return $this->_cost;
    }

    // Full address attribute
    public function getFullAddress(string $return_type="html"): string {
        $sep = '<br>';
        if(strtolower($return_type)  != "html") {
            $sep = "\n";
        }
        return $this->getAddress1() . $sep . $this->getAddress2() . $sep. $this->getPostCode();
    }

    // Charging Point Details in the form of form data
    public function getFormData(): array {
        return [
            'address1' => $this->_address1,
            'address2' => $this->_address2,
            'post_code' => $this->_post_code,
            'lat' => $this->_latitude,
            'lng' => $this->_longitude,
            'cost' => $this->_cost,
        ];
    }
}

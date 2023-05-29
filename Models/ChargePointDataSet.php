<?php

require_once('Models/Database.php');
require_once('Models/ChargePoint.php');

class ChargePointDataSet
{
    protected $_dbHandle, $_dbInstance;

    public function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }

    // Get one charge point from the database based on owner'd ID
    public function getChargePoint(int $user_id): ChargePoint|false
    {
        $sql = "SELECT * FROM charge_point WHERE owner_id = :user_id";
        $stmt = $this->_dbHandle->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($result)) {
            return false;
        }

        return new ChargePoint($result);
    }

    // Get all charge points from the database
    public function getAllChargePoints(): array
    {
        $sql = "SELECT * FROM charge_point";
        $stmt = $this->_dbHandle->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $allChargePoints = array();

        foreach ($result as $row) {
            $allChargePoints[] = new ChargePoint($row);
        }

        return $allChargePoints;
    }

    // Add one charge point to the database
    public function add(int $user_id, string $address1, string $address2, string $postcode, float $lat, float $lng, float $cost)
    {
        try {

            $sql = "INSERT INTO charge_point (address1, address2, post_code, lat, lng, cost, owner_id) VALUES (:address1, :address2, :postcode, :lat, :lng, :cost, :user_id)";
            $stmt = $this->_dbHandle->prepare($sql);

            $stmt->bindValue(':address1', $address1);
            $stmt->bindValue(':address2', $address2);
            $stmt->bindValue(':postcode', $postcode);
            $stmt->bindValue(':lat', $lat);
            $stmt->bindValue(':lng', $lng);
            $stmt->bindValue(':cost', $cost);
            $stmt->bindValue(':user_id', $user_id);

            $stmt->execute();
            return json_encode(array('status_code' => 200, 'new_id' => $this->_dbHandle->lastInsertId()));
        } catch (Exception $e) {
            return json_encode(array('status_code' => 500, 'message' => $e->getMessage()));
        }
    }

    // Update charge point
    public function update(int $owner_id, array $to_change): string
    {
        if (count($to_change) == 0) {
            return json_encode(array('status_code' => 404, 'message' => 'No parameters to change'));
        }

        try {
            $sql = 'UPDATE charge_point SET ';

            foreach (array_keys($to_change) as $idx => $key) {
                if ($idx != 0) {
                    $sql .= ', ';
                }

                $sql .= $key . '=:' . $key;
            }

            $sql .= ' WHERE owner_id=:owner_id';

            $stmt = $this->_dbHandle->prepare($sql);
            $stmt->bindValue(':owner_id', $owner_id);

            foreach ($to_change as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();
            return json_encode(array('status_code' => 200, 'message' => 'Updated'));
        } catch (Exception $e) {
            return json_encode(array('status_code' => 500, 'message' => $e->getMessage()));
        }
    }

    // Retrieve all charging points from the database and their owner's details
    public function getAllChargingPointAndTheirUsers(): string
    {
        try {
            $sql = 'SELECT u.fullname AS fullname, u.phone AS phone, u.img_url AS img_url, u.email AS email, CONCAT(c.address1, ", ",  c.address2, ", ", c.post_code) AS address, c.lat AS lat, c.lng AS lng, c.cost AS cost FROM user u RIGHT JOIN charge_point c ON c.owner_id=u.user_id;';
            $stmt = $this->_dbHandle->prepare($sql);

            $stmt->execute();
            return json_encode(array('status_code' => 200, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)));
        } catch (Exception $e) {
            return json_encode(array('status_code' => 500, 'message' => $e->getMessage()));
        }
    }

    // Get maximum number of pages for current search criteria
    public function getMaxPages(string $search_term = "", float $cost_min = 0.0, float $cost_max = 10.0): int
    {

        $search_term = '%' . trim($search_term) . '%';

        $sql = "SELECT COUNT(*) AS count FROM charge_point LEFT JOIN user ON user.user_id=charge_point.owner_id WHERE cost BETWEEN :cost_min AND :cost_max AND (LOWER(CONCAT(address1, ', ', address2, ', ', post_code)) LIKE :search_term OR LOWER(fullname) LIKE :search_term);";
        $stmt = $this->_dbHandle->prepare($sql);
        $stmt->bindValue(':search_term', $search_term);
        $stmt->bindValue(':cost_min', $cost_min);
        $stmt->bindValue(':cost_max', $cost_max);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (((int) $result['count']) / 10) + ((((int) $result['count']) % 10 == 0 ? 0 : 1));
    }

    // Get all charging points based on current search criteria
    public function getChargePoints(int $page = 1, string $search_term = "", float $cost_min = 0.0, float $cost_max = 10.0, string $order_by = 'fullname', string $order_type = 'ASC'): string
    {
        try {
            $search_term = strtolower($search_term);
            $max_pages = $this->getMaxPages($search_term, $cost_min, $cost_max);

            if ($max_pages == 0) {
                throw new Exception('No Results Found');
            }
            if ($page > $max_pages) {
                throw new Exception('Page out of range');
            }
            $offset = 10 * ($page - 1);
            if (!in_array(strtolower($order_by), array('fullname', 'cost'))) {
                throw new Exception('Invalid column to order by');
            }
            if (!in_array(strtoupper($order_type), array('ASC', 'DESC'))) {
                throw new Exception('Invalid column ordering');
            }

            $search_text = '%' . trim($search_term) . '%';

            $sql = "SELECT u.img_url AS img_url, u.fullname AS fullname, CONCAT(c.address1, '<br>', c.address2, '<br>', c.post_code) AS address, u.phone AS phone, u.email AS email, c.cost AS cost FROM user u RIGHT JOIN charge_point c ON c.owner_id=u.user_id WHERE cost BETWEEN :cost_min AND :cost_max AND (LOWER(CONCAT(address1, ', ', address2, ', ', post_code)) LIKE :search_term OR LOWER(fullname) LIKE :search_term) ORDER BY " . strtolower($order_by) . " " .  strtoupper($order_type) . " LIMIT 10 OFFSET :offset;";
            $stmt = $this->_dbHandle->prepare($sql);

            $stmt->bindValue(':search_term', $search_text);
            $stmt->bindValue(':cost_min', $cost_min);
            $stmt->bindValue(':cost_max', $cost_max);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return json_encode(array('status_code' => 200, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'max_pages' => $max_pages));
        } catch (Exception $e) {
            return json_encode(array('status_code' => 500, 'message' => $e->getMessage()));
        }
    }

    public function removeChargePoint(int $charge_point_id): string {
        try {
            $sql = "DELETE FROM charge_point WHERE cp_id = :charge_point_id;";
            $stmt = $this->_dbHandle->prepare($sql);
            $stmt->bindValue(':charge_point_id', $charge_point_id);
            $stmt->execute();
            return json_encode(array('status_code' => 200, 'message' => 'Deleted Successfully'));
        } catch (Exception $e) {
            return json_encode(array('status_code' => 500, 'message' => $e->getMessage()));
        }
    }
}

<?php

require_once('Models/ChargePointDataSet.php');
$chargePointDataSet = new ChargePointDataSet();

// Gets all the charge points based on the current search criteria
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'));
    $page = $data->page;
    $search_term = $data->search_term;
    $cost_min = $data->cost_min;
    $cost_max = $data->cost_max;
    $order_by = $data->order_by;
    $order_type = $data->order_type;

    $response = $chargePointDataSet->getChargePoints($page, $search_term, $cost_min, $cost_max, $order_by, $order_type);

    echo $response;
}
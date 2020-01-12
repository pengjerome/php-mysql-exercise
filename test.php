<?php
// header("Content-Type: application/json; chartset=utf-8");
$json = "[4,8,9,1,3,4]";
$data = json_decode(file_get_contents("php://input"), true);
// $data = json_decode($json);
print_r($data[0]);
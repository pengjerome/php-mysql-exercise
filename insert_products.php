<?php
require_once './pdo_config.php';

$sql_query = 'INSERT INTO `products`(`title`, `price`, `tag`, `unit`, `sTime`, `feature`, `classify`, `img`) VALUES (?,?,?,?,?,?,?,?)';
$pdo_stmt = $pdo->prepare($sql_query);

$json_string = file_get_contents("./tunlo.json"); // 從檔案中讀取資料到PHP變數
$data = json_decode($json_string, true); // 把JSON字串轉成PHP陣列

foreach ($data as $i => $pro) {
  $pdo_stmt->execute([$pro['title'], $pro['price'], $pro['tag'], $pro['unit'], $pro['sTime'], $pro['feature'], $pro['classify'], $pro['img']]);
}

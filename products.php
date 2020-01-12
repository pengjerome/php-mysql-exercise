<?php
require_once './pdo_config.php';
require_once './Img.php';

$img = new Img();

class product
{
  private $col_name = ['pid', 'title', 'tag', 'classify', 'unit', 'price', 'feature', 'sTime', 'img'];
  function data_url()
  {
    $data = [];
    foreach ($this->col_name as $value) {
      $data[urlencode($value)] = urlencode($this->$value);
    }
    return $data;
  }
  function data()
  {
    $data = [];
    foreach ($this->col_name as $value) {
      $data[$value] = $this->$value;
    }
    return $data;
  }
}

class Restful
{
  private $getFunc;
  private $postFunc;
  private $deleteFunc;
  function setGet($func)
  {
    $this->getFunc = $func;
    return $this;
  }
  function setPost($func)
  {
    $this->postFunc = $func;
    return $this;
  }
  function setDelete($func)
  {
    $this->deleteFunc = $func;
    return $this;
  }
  function receiveReq($reqMethod)
  {
    if ($reqMethod === 'GET') {
      return $this->getFunc;
    } else if ($reqMethod === 'POST') {
      return $this->postFunc;
    } elseif ($reqMethod === 'DELETE') {
      return $this->deleteFunc;
    } else {
      $msg = [
        'success' => false,
        'msg' => 'req method not allow'
      ];
      echo json_encode($msg);
    }
  }
}

function getResponse($pdo)
{
  $sql_getAll = "SELECT `pid`, `title`, `price`, `tag`, `classify`, `unit`, `sTime`, `img`, `feature` FROM `products`";
  if ($_GET['page'] === 'all') {
    $getAll_stmt = $pdo->query($sql_getAll);
    $products_url = [];
    while ($sql_result = $getAll_stmt->fetchAll(PDO::FETCH_CLASS, 'product')) {
      foreach ($sql_result as $product) {
        array_push($products_url, $product->data_url());
      }
    }
    $res = [
      'success' => true,
      'msg' => '',
      'data' => $products_url
    ];
    $res_json = urldecode(json_encode($res));
    echo $res_json;
  } else {
    $sqlTotalNum = "SELECT count(1) FROM `products`";
    $sqlGetLimit = $sql_getAll."ORDER BY `pid` ASC LIMIT ?, ?";
    $totalNum_stmt = $pdo->query($sqlTotalNum);
    $getLimit_stmt = $pdo->prepare($sqlGetLimit);
    $totalNum = $totalNum_stmt->fetch(PDO::FETCH_NUM)[0];
    $numPerPage = 6;
    $totalPage = ceil($totalNum / $numPerPage);
    $currentPage = (int) $_GET['page'];
    if ($currentPage > $totalPage) {
      $currentPage = $totalPage;
    } else if ($currentPage < 0) {
      $currentPage = 0;
    }
    $startNum = ($currentPage - 1) * $numPerPage;
    $getLimit_stmt->bindValue(1, $startNum, PDO::PARAM_INT);
    $getLimit_stmt->bindValue(2, $numPerPage, PDO::PARAM_INT);
    $getLimit_stmt->execute();
    $products_url = [];
    while ($sql_result = $getLimit_stmt->fetchAll(PDO::FETCH_CLASS, 'product')) {
      foreach ($sql_result as $product) {
        array_push($products_url, $product->data_url());
      }
    }
    $res = [
      'success' => true,
      'msg' => '',
      'data' => $products_url,
      'currentPage' => $currentPage,
      'totalPage' => $totalPage
    ];
    $res_json = urldecode(json_encode($res));
    echo $res_json;
  }
}

function postResponse($pdo)
{
  $img = new Img();
  if (!isset($_POST['pid'])) {
    $sql_query = 'INSERT INTO `products`(`title`, `price`, `tag`, `classify`, `unit`, `sTime`, `feature`, `img`) VALUES (?,?,?,?,?,?,?,?)';

    $sql_stmt = $pdo->prepare($sql_query);
    $img_name = '';

    if ($_POST['title'] && $_POST['tag'] && $_POST['price']) {
      if (!$_FILES['img']['error']) {
        $extention = preg_replace('/.+(\/)/', '.', $_FILES['img']['type']);
        $img_name = time() . $extention;
        $img->store($img_name, $_FILES['img']['tmp_name']);
      }
      $values = [$_POST['title'], $_POST['price'], $_POST['tag'], $_POST['classify'], $_POST['unit'], $_POST['sTime'], $_POST['feature'], $img_name];
      $sql_stmt->execute($values);
      $msg = [
        'success' => true,
        'msg' => '編輯成功'
      ];
      echo json_encode($msg);
    } else {
      $msg = [
        'success' => false,
        'msg' => '請填入正確資料'
      ];
      echo json_encode($msg);
    }
  } else {
    $sql_query = 'UPDATE `products` SET `title`=?,`price`=?,`tag`=?,`classify`=?,`unit`=?,`sTime`=?,`feature`=?';
    $img_name = '';
    $time = time();
    $pid = (int) $_POST['pid'];
    $values = [$_POST['title'], $_POST['price'], $_POST['tag'], $_POST['classify'], $_POST['unit'], $_POST['sTime'], $_POST['feature']];
    if ($_POST['title'] && $_POST['tag'] && $_POST['price']) {
      if (!$_FILES['img']['error']) {
        $time += 1;
        $extention = preg_replace('/.+(\/)/', '.', $_FILES['img']['type']);
        $img_name = "{$time}" . $extention;
        move_uploaded_file($_FILES['img']['tmp_name'], "./images/{$img_name}");
        $img->store($img_name, $_FILES['img']['tmp_name']);
        $sql_query = $sql_query . ',`img`=?';
        $values[] = $img_name;
        $sql_getImg = "SELECT `img` FROM `products` WHERE `pid` = ?";
        $getImg_stmt = $pdo->prepare($sql_getImg);
        $getImg_stmt->execute([$pid]);
        $origin_img = $getImg_stmt->fetchAll(PDO::FETCH_ASSOC)[0]['img'];
        $img->delete($origin_img);
      }
      $sql_query = $sql_query . ' WHERE `pid`=?';
      $values[] = $pid;
      $sql_stmt = $pdo->prepare($sql_query);
      $sql_stmt->execute($values);
      $msg = [
        'success' => true,
        'msg' => '新增成功'
      ];
      echo json_encode($msg);
    } else {
      $msg = [
        'success' => false,
        'msg' => '請填入正確資料'
      ];
      echo json_encode($msg);
    }
  }
}

function deleteResponse($pdo)
{
  $img = new Img();
  $queryGetImg = 'SELECT `img` FROM `products` WHERE `pid`=?';
  $query_delete = 'DELETE FROM `products` WHERE `pid` = ?';
  $stmt_select = $pdo->prepare($queryGetImg);
  $stmt_delete = $pdo->prepare($query_delete);
  $ids = json_decode(file_get_contents('php://input'));
  foreach ($ids as $id) {
    $stmt_select->execute([$id]);
    $img_name = $stmt_select->fetchAll(PDO::FETCH_ASSOC)[0]['img'];
    $img->delete($img_name);
    $stmt_delete->execute([$id]);
  }
  $msg = [
    'success' => true,
    'msg' => '刪除成功'
  ];
  echo json_encode($msg);
}

$restfulApi = new Restful();
$restfulApi->setGet('getResponse')->setPost('postResponse')->setDelete('deleteResponse')->receiveReq($_SERVER['REQUEST_METHOD'])($pdo);

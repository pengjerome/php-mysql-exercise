<?php
class Img {
  private $path = './images/';
  function delete($name) {
    if (!$name) {
      return;
    }
    $is_exists = file_exists($this->path.$name);
    if ($is_exists) {
      @unlink($this->path.$name);
    }
  }
  function store($name, $stmt) {
    move_uploaded_file($stmt, $this->path.$name);
  }
}

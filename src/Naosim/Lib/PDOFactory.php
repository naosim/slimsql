<?php
namespace Naosim\Lib;

use PDO;
use Naosim\Lib\PDOFactory;


class PDOFactoryImpl implements PDOFactory {
  public function create(): PDO {
    $fileName = 'my_sqlite_db.db';
    $pdo = new PDO("sqlite:$fileName");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
  }
}
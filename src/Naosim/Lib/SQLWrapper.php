<?php
namespace Naosim\Lib;

use PDO;

class SQLWrapper {
  private $pdo;
  function __construct(PDOFactory $pdoFactory) {
      $this->pdo = $pdoFactory->create();
  }
  
  function exec(string $sql) {
      return $this->pdo->prepare($sql)->execute();
  }
  function insert(string $sql) {
      $this->pdo->prepare($sql)->execute();
      return $this->pdo->lastInsertId();
  }
  function select(string $sql) {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll();
  }
  function showTables() {
      $stmt = $this->pdo->query("select name from sqlite_master where type='table'");
      return $stmt->fetchAll();
  }
}

interface PDOFactory {
  function create(): PDO;
}

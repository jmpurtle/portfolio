<?php
namespace Database\Models {
	use PDO;
	class MysqlInterface {
		
		public $conn;
		
		public function __construct(Array $dbConfig) {
			
			$connectionScheme = [
				'dbHost' => '',
				'dbPort' => '3306',
				'dbName' => '',
				'dbUser' => '',
				'dbPass' => ''	
			];
			
			$connInfo = array_merge($connectionScheme, $dbConfig);
			$connString = "mysql:dbname=" . $connInfo['dbName'] . ";host=" . $connInfo['dbHost'] . ";port=" . $connInfo['dbPort'] . ";";
			$pdoOpts = [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false
			];
			
			$dbConnection = new PDO($connString, $connInfo['dbUser'], $connInfo['dbPass'], $pdoOpts);
			
			$this->conn = $dbConnection;
			
			return true;
		}
		
		public function executeQuery($query, Array $params = []) {
			
			if (!empty($params)) {
				$stmt = $this->conn->prepare($query);
				$stmt->execute($params);
				return $stmt;
			}
			
			$stmt = $this->conn->query($query);
			return $stmt;
			
		}
		
	}
	
}
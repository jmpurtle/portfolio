<?php
namespace Database\Models {
	use PDO;
	class ContactModel {

		public  $record = null;
		private $hostID;
		private $counter;
		private $processID;
		private $idData;
		private $conn;

		public function __construct($dbConfig) {

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

			$this->hostID = substr(md5(gethostname()), 0, 6);
			$this->counter = mt_rand();
			$this->processID = getmypid() % 0xFFFF;
			$this->idData = $this->getIdentity();

		}

		public function getIdentity() {
			if (isset($this->idData)) {
				return $this->idData;
			}

			$userAgent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : 'None';
			$ipAddress = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : 'None';

			$this->idData = [
				'ua' => $userAgent,
				'ip' => $ipAddress
			];

			return $this->idData;

		}

		public function generateObjID() {
			return dechex(time()) . $this->hostID . dechex($this->processID) . dechex($this->counter++);
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

		public function create(array $postFields = array()) {
			
			$dbSchema = [
				'name'       => null,
				'org'        => null,
				'email'      => null,
				'project'    => null,
				'budget'     => null,
				'launchdate' => null,
				'referral'   => null
			];

			$this->record = array_merge($dbSchema, $postFields);

		}

		public function save() {

			$query = 'INSERT INTO contact (id, name, org, email, project, budget, launchdate, referral) VALUES (?,?,?,?,?,?,?,?)';

			$params = [$this->generateObjID(), $this->record['name'], $this->record['org'], $this->record['email'], $this->record['project'], $this->record['budget'], $this->record['launchdate'], $this->record['referral']];

			$this->executeQuery($query, $params);
		}

	}

}

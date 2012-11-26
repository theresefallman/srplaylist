<?php

class Database {
	
	private $_pdo = null;
	private $_host = null;
	private $_user = null;
	private $_pwd = null;
	private $_dbname = null;
	private $_dbdsn = null;
	private $_table = null;
	private $_tableUpdate = null;
	
	public function __construct($host, $user, $pwd, $dbname, $table, $tableUpdate) {
		$this->_host = $host;
		$this->_user = $user;
		$this->_pwd = $pwd;
		$this->_dbname = $dbname;
		$this->_table = $table;
		$this->_tableUpdate = $tableUpdate;
		$this->_dbdsn = "mysql:host=$this->_host;dbname=$this->_dbname;charset=utf8";
		
		$this->create();
		$this->_setup();
	}	
		
	/***************************
	Function for creating a new PDO setup - connects to database
	***************************/
	public function create() {
		try
		{
			$this->_pdo = new PDO(
				$this->_dbdsn,
				$this->_user,
				$this->_pwd
			);
			
			$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			throw new PDOException("Database error: " . $e->getMessage());
		}
	}
	
	/***************************
	Function for preparing statements
	@param string sql-query
	@return Statement object or false
	***************************/
	public function prepare($sql) {
		
		$stmt = $this->_pdo->prepare($sql);
		
		if ($stmt == false) {
			throw new Exception("Database error [PDO]: ". $e->getMessage());
		}
		
		return $stmt;
	}
	
	/***************************
	Execute statements
	@param string sql-query (required), array with parameters (optional)
	@return boolean
	***************************/
	public function runAndPrepare($sql, $params = null) {
		$stmt = $this->prepare($sql);
						
		if ($stmt !== false) {
			try
			{		
				return $stmt->execute($params);
			}
			catch(Exception $e) 
			{
				throw new Exception("Database error [PDO]: " . $e->getMessage());		
			}
		}
		else {
			throw new PDOException("Database error [PDO]: Statement returned false.");
		}
	}
	
	/***************************
	Execute statements and fetch result
	@param string sql-query
	@return array[] with selected values
	***************************/
	public function runAndFetch($sql) {
		$stmt = $this->prepare($sql);
		$result = array();
		
		if ($stmt !== false) {
			if ($stmt->execute()) {
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$result[] = $row;
				}
			}
		}
		else {
			throw new Exception("Database error [PDO]: Statement returned false.");
		}
		
		return $result;
	}
	

	/***************************
	Sets up a needed tables, if they doesn't exist.
	@return boolean value 
	***************************/
	private function _setup() {
		
		if ($this->tableExist($this->_table) == null) {
			
			$sql = "CREATE TABLE $this->_table (
				id int NOT NULL AUTO_INCREMENT,
				channel_id int NOT NULL,
				name varchar(255) NOT NULL,
				audio_url varchar(255) NOT NULL,
				channel_url varchar(255) NOT NULL,
				PRIMARY KEY (id),
				KEY name (name))";
				
			$this->runAndPrepare($sql);
		}
		
		if ($this->tableExist($this->_tableUpdate) == null) {
			$sql = "CREATE TABLE $this->_tableUpdate (
				id int NOT NULL AUTO_INCREMENT,
				ttl DATETIME NOT NULL,
				PRIMARY KEY (id))";
			$this->runAndPrepare($sql);
		}
	}
	
	/***************************
	Private function that checks if table already exists.
	@param string name on table to check
	@return array[]  
	***************************/
	private function tableExist($tableName) {
		$sql = "SHOW TABLES LIKE '$tableName';";
		return $this->runAndFetch($sql);
	}
	
}

?>
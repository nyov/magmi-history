<?php
/*
 * Database helper using pdo
 */
class DBHelper
{
	protected $_db;
	protected $_debug;

	/**
	 * Intializes database connection
	 * @param string $host : hostname
	 * @param string $dbname : database name
	 * @param string $user : username
	 * @param string $pass : password
	 * @param bool $debug : debug mode 
	 */
	public function initDb($host,$dbname,$user,$pass,$debug=false)
	{
		//intialize connection with PDO 
		//fix by Mr Lei for UTF8 special chars
		$this->_db=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		//use exception error mode
		$this->_db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		//use fetch assoc as default fetch mode
		$this->_db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
		//set database debug mode to trace if necessary
		$this->_debug=$debug;
		if($this->_debug)
		{
			$this->_db->query("SET GLOBAL general_log='ON' ")->execute();
		}
		$this->prepared=array();
	}

	/**
	 * releases database connection
	 */
	public function exitDb()
	{
		//unset database debug mode to trace if necessary
		if($this->_debug)
		{
			$this->_db->query("SET GLOBAL general_log='OFF' ")->execute();
		}
		//clear PDO resource
		$this->_db=NULL;
			
	}
	
	/**
	 * executes an sql statement
	 * @param string $sql : sql statement (may include ? placeholders)
	 * @param array $params : parameters to replace placeholders (can be null)
	 * @param boolean $close : auto close cursor after statement execution (defaults to true)
	 * @return PDOStatement : statement for further processing if needed
	 */
	public function exec_stmt($sql,$params=null,$close=true)
	{
		//if sql not in statement cache
		if(!isset($this->prepared[$sql]))
		{
			//create new prepared statement
			$stmt=$this->_db->prepare($sql);
			//cache prepare statement
			$this->prepared[$sql]=$stmt;
		}
		else
		{
			//get from statement cache
			$stmt=$this->prepared[$sql];
		}
		
		if($params!=null)
		{
			$params=is_array($params)?$params:array($params);
			$stmt->execute($params);
		}
		else
		{
			$stmt->execute();
		}
		if($close)
		{
			$stmt->closeCursor();
		}
		return $stmt;
	}
	
	/**
	 * Perform a delete statement, sql should be "DELETE"
	 * @param string $sql : DELETE statement sql (placeholders allowed)
	 * @param array $params : placeholder replacements (can be null)
	 */
	public function delete($sql,$params=null)
	{
		$this->exec_stmt($sql,$params);
	}
	
	public function update($sql,$params=null)
	{
		$this->exec_stmt($sql,$params);
	}
	/**
	 * Perform an insert , sql should be "INSERT"
	 * @param string $sql :INSERT statement SQL (placeholders allowed)
	 * @param array $params : placeholder replacements (can be null)
	 * @return mixed : last inserted id
	 */
	public function insert($sql,$params=null)
	{
		$this->exec_stmt($sql,$params);
		$liid=$this->_db->lastInsertId();
		return $liid;
	}
	
	/**
	 * Perform a select ,sql should be "SELECT"
	 * @param string $sql :SELECT statement SQL (placeholders allowed)
	 * @param array $params : placeholder replacements (can be null)
	 * @return PDOStatement : statement instance for further processing
	 */
	public function select($sql,$params=null)
	{
		return $this->exec_stmt($sql,$params,false);
	}
	
	/**
	 * Selects one unique value from one single row
	 * @param $sql : SELECT statement SQL (placeholders allowed)
	 * @param $params :placeholder replacements (can be null)
	 * @param $col : column value to retrieve
	 * @return mixed : null if not result , wanted column value if match
	 */
	public function selectone($sql,$params,$col)
	{
		$stmt=$this->select($sql,$params);
		$r=$stmt->fetch();
		$stmt->closeCursor();
		$v=(is_array($r)?$r[$col]:null);
		return $v;
	}
	
	/**
	 * Selects all values from a statement into a php array
	 * @param unknown_type $sql sql select to execute
	 * @param unknown_type $params placeholder replacements (can be null)
	 */
	public function selectAll($sql,$params=null)
	{
		$stmt=$this->select($sql,$params);
		$r=$stmt->fetchAll();
		$stmt->closeCursor();
		return $r;
	}
	
	/**
	 * test if value exists (test should be compatible with unique select)
	 * @param $sql : SELECT statement SQL (placeholders allowed)
	 * @param $params :placeholder replacements (can be null)
	 * @param $col : column value to retrieve
	 * @return boolean : true if value found, false otherwise
	 */
	public function testexists($sql,$params,$col)
	{
		return $this->selectone($sql,$params,$col)!=null;
	}
	
	/**
	 * begins a transaction
	 */
	public function beginTransaction()
	{
		$this->_db->beginTransaction();
	}
	
	/**
	 * commits the current transaction
	 */
	public function commitTransaction()
	{
		$this->_db->commit();
	}

	/**
	 * rollback the current transaction
	 */
	public function rollbackTransaction()
	{
		$this->_db->rollBack();
	}
}

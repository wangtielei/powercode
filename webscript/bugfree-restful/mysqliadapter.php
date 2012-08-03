<?php

define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/"); 
require_once(LOG4PHP_DIR.'/Logger.php'); 

/**
* prepare statement parameter.
*/
class StatementParameter
{
    private $parameters = array();

    public function __constructor()
    {
    }

    public function addParameter($name, $type, $value = NULL)
    {
        $this->parameters[$name] = array("type" => $type, "value" => $value);   
    }

    public function getTypeString()
    {
        $types = "";   

        foreach($this->parameters as $name => $la)
        $types .= $la['type'];

        return $types;
    }

    public function setParameter($name, $value)
    {
        if (isset($this->parameters[$name]))
        {
            $this->parameters[$name]["value"] = $value;
            return true;
        }
        return false;
    }

    public function bindParams(&$stmt)
    {
        if (count($this->parameters) == 0)
            return true;

        $ar = Array();

        $ar[] = $this->getTypeString();
        foreach($this->parameters as $name => $la)
            $ar[] = &$this->parameters[$name]['value'];
        return call_user_func_array(array($stmt, 'bind_param'),$ar);
    }
}

class StatementParameterType
{
    public static $STATEMENT_TYPE_INTEGER = 'i';
    public static $STATEMENT_TYPE_DOUBLE  = 'd';
    public static $STATEMENT_TYPE_STRING  = 's';
    public static $STATEMENT_TYPE_BLOB    = 'b';
} 


/**
 * 对mysqli的简单封装
 * @author guanjianjun
 *
 */
class MysqliAdapter
{
	private $logger = NULL;
    private $dbInstance = null;
    private $dbConfig = array();

    public function __construct($config)
    {
		$this->logger = Logger::getLogger(__CLASS__);
        $this->dbConfig = $config;
        $this->logger->info(print_r($this->dbConfig, true));
    }

    /**
    * constructor
    */
    public function connect()
    {
        $this->dbInstance = new mysqli($this->dbConfig['server'], $this->dbConfig['username'], $this->dbConfig['password'], $this->dbConfig['database']);
        
        /* check connection */
        if (mysqli_connect_errno()) 
        {
            $errorInfo = sprintf("Connect failed: %s\n", mysqli_connect_error());
            echo $errorInfo;
            $this->logger->error($errorInfo);
            exit();
        }
        //$this->dbInstance->set_charset("utf8");
        $this->dbInstance->query("SET NAMES 'utf8'");
        $this->logger->info("mysqli connect successful");
        return true;
    }

    /**
    * close mysqli instance;
    */
    public function close()
    {
        if ($this->dbInstance)
        {
            $this->dbInstance->close();
            $this->dbInstance = null;
        }
    }

    /**
    * switch current database.
    */
    public function usedb($dbname)
    {
        if ($this->dbInstance)
        {
            $this->dbInstance->select_db($dbname);
        }
    }

    /**
     * execute a query
     */
    private function query($sql)
    {
        if (!$this->dbInstance)
        {
            return null;
        }
        
        return $this->dbInstance->query($this->dbInstance->escape_string($sql));
    }

    /**
    * return current database.
    */
    public function currentdb()
    {
        if ($result = $this->query("select database()"))
        {
            $row = $result->fetch_row();
            return $row[0];
        }

        return null;
    }

    function escape($string)
    {
        //return mysql_escape_string($string);
        $result = $this->dbInstance->real_escape_string($string);
        return $result;
    }

    /**
     * Get the tables in a database.
     * @return resource A resultset resource
     */
    function getTables()
    {
        return $this->query('SHOW TABLES');
    }

    /**
     * Fetch a row from a query resultset.
     * @param resource resource A resultset resource
     * @return str[] An array of the fields and values from the next row in the resultset
     */
    function row($resource)
    {
        return $resource->fetch_assoc();
    }

    /**
     * Get the primary keys for the request table.
     * @return str[] The primary key field names
     */
    function getPrimaryKeys($table)
    {
        $resource = $this->getColumns($table);
        $primary = NULL;
        if ($resource)
        {
            while ($row = $this->row($resource))
            {
                if ($row['Key'] == 'PRI')
                {
                    //add into array
                    $primary[] = $row['Field'];
                }
            }
        }
        return $primary;
    }

    function getColumns($table)
    {
        $sqlStr = sprintf('SHOW COLUMNS FROM %s ', $table);
        $this->logger->debug("sqlStr = ".$sqlStr);
        return $this->query($sqlStr);
    }

    /**
     * The number of rows in a resultset.
     * @param resource resource A resultset resource
     * @return int The number of rows
     */
    function numRows($resource)
    {
        return $resource->num_rows();
    }

    /**
     * The number of rows affected by a query.
     * @return int The number of rows
     */
    function numAffected()
    {
        return $this->dbInstance->affected_rows();
    }

    /**
    * create a prepare statement instance by input sql command.
    */
    public function prepareStatement($sql)
    {
        if (!$this->dbInstance)
        {
            $this->logger->error("dbInstance is null");
            return null;
        }
        $this->logger->debug("prepare statement: $sql");
        return $this->dbInstance->prepare($sql);
    }

    /**
    * execute a prepare statement.
    */
    public function executeStatement($sql, $parameters)
    {
        $stmt = $this->prepareStatement($sql);

        if ($stmt == null)
        {
            $this->logger->error("stmt is null");
            return null;
        }

        $parameters->bindParams($stmt);
        //$stmt->bind_param('i', $v);
        //$v = 10;
        if (!$stmt->execute())
        {
            $this->logger->error("statement execute error");
            return null;
        }
        
        /* store result */
        $stmt->store_result();
            
        return $stmt;        
    }

    public function executeQuery($sql, $parameters)
    {
        $stmt = $this->prepareStatement($sql);

        if ($stmt == null)
        {
            $this->logger->error("stmt is null");
            return null;
        }

        $parameters->bindParams($stmt);
        
        if (!$stmt->execute())
        {
            $this->logger->error("statement execute error");
            return null;
        }

        /* store result */
        $stmt->store_result();
        
        return $stmt;
    }

    //将查询结果按照数组方式返回
    public function fetchArray($sql, $parameters)
    {
    	$rowsData = array();
    	
    	$stmt = $this->executeQuery($sql, $parameters);
    	if ($stmt)
    	{
    		$data = array();   //array that accepts the data.
    		$params = array(); //parameter array passed to 'bind_result()'.
    		$colList = array();//array that store the columns.
    		$colNum = $stmt->field_count;
    		$stmt_result = $stmt->result_metadata();
    		while ($col = $stmt_result->fetch_field())
    		{
    			array_push($colList, $col);
    			$params[] = & $data[$col->name];
    		}
    		$stmt_result->close();
    	
    		$res = call_user_func_array(array($stmt, "bind_result"), $params);
    		if (!$res)
    		{
    			$this->logger->error("bind_result() failed.");
    		}
    		else
    		{
    			while ($stmt->fetch())
    			{
    				$row = array();
    				foreach($colList as $col)
    				{
    					$row[$col->name] = $data[$col->name];
    				}
    				$rowsData[] = $row;
    			}
    		}
    		$stmt->free_result();
    		$stmt->close();
    	}
    	return $rowsData;
    }
    
    public function executeInsert($sql, $parameters)
    {
        $stmt = $this->prepareStatement($sql);

        if ($stmt == null)
        {
            $this->logger->error("stmt is null");
            return false;
        }

        $parameters->bindParams($stmt);
        //$stmt->bind_param('i', $v);
        //$v = 10;
        if (!$stmt->execute())
        {
            $this->logger->debug("statement execute error");
            return false;
        }

        return true;
    }

    /**
    * execute a sql command.
    */
    public function queryData($sql)
    {
        return $this->query($sql);
    } 

    public function execSql($sql)
    {
        return $this->dbInstance->real_query($sql);
    }
}

/*
 * Database类是不安全的mysql类，可以被sql注入攻击
 This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/



//require("config.inc.php");
//$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);


###################################################################################################
###################################################################################################
###################################################################################################
class Database
{


	var $server   = ""; //database server
	var $user     = ""; //database login name
	var $pass     = ""; //database login password
	var $database = ""; //database name
	var $pre      = ""; //table prefix


	#######################
	//internal info
	var $error = "";
	var $errno = 0;

	//number of rows affected by SQL query
	var $affected_rows = 0;

	var $link_id = 0;
	var $query_id = 0;


	#-#############################################
	# desc: constructor
	public function __construct($server, $user, $pass, $database, $pre=''){
	$this->server=$server;
	$this->user=$user;
	$this->pass=$pass;
	$this->database=$database;
	$this->pre=$pre;
	}#-#constructor()


	#-#############################################
	# desc: connect and select database using vars above
	# Param: $new_link can force connect() to open a new link, even if mysql_connect() was called before with the same parameters
	public function connect($new_link=false) {
	$this->link_id=@mysql_connect($this->server,$this->user,$this->pass,$new_link);

	if (!$this->link_id) {//open failed
		$this->oops("Could not connect to server: <b>$this->server</b>.");
	}

		if(!@mysql_select_db($this->database, $this->link_id)) {//no database
		$this->oops("Could not open database: <b>$this->database</b>.");
		}

		// unset the data so it can't be dumped
		$this->server='';
		$this->user='';
		$this->pass='';
		$this->database='';
	}#-#connect()


	#-#############################################
	# desc: close the connection
	public function close() {
	if(!@mysql_close($this->link_id)){
	$this->oops("Connection close failed.");
	}
	}#-#close()


	#-#############################################
	# Desc: escapes characters to be mysql ready
	# Param: string
	# returns: string
	public function escape($string) {
	if(get_magic_quotes_runtime()) $string = stripslashes($string);
	return @mysql_real_escape_string($string,$this->link_id);
	}#-#escape()


	#-#############################################
	# Desc: executes SQL query to an open connection
	# Param: (MySQL query) to execute
	# returns: (query_id) for fetching results etc
	public function query($sql) {
	// do query
	$this->query_id = @mysql_query($sql, $this->link_id);

	if (!$this->query_id) {
	$this->oops("<b>MySQL Query fail:</b> $sql");
	return 0;
	}

	$this->affected_rows = @mysql_affected_rows($this->link_id);

	return $this->query_id;
	}#-#query()


	#-#############################################
	# desc: fetches and returns results one line at a time
	# param: query_id for mysql run. if none specified, last used
	# return: (array) fetched record(s)
	public function fetch_array($query_id=-1) {
	// retrieve row
	if ($query_id!=-1) {
		$this->query_id=$query_id;
	}

		if (isset($this->query_id)) {
		$record = @mysql_fetch_assoc($this->query_id);
		}else{
		$this->oops("Invalid query_id: <b>$this->query_id</b>. Records could not be fetched.");
		}

		return $record;
		}#-#fetch_array()


		#-#############################################
		# desc: returns all the results (not one row)
				# param: (MySQL query) the query to run on server
				# returns: assoc array of ALL fetched results
				public function fetch_all_array($sql) {
				$query_id = $this->query($sql);
			$out = array();

			while ($row = $this->fetch_array($query_id)){
			$out[] = $row;
		}

		$this->free_result($query_id);
		return $out;
	}#-#fetch_all_array()


	#-#############################################
		# desc: frees the resultset
		# param: query_id for mysql run. if none specified, last used
		public function free_result($query_id=-1) {
		if ($query_id!=-1) {
		$this->query_id=$query_id;
		}
			if($this->query_id!=0 && !@mysql_free_result($this->query_id)) {
			$this->oops("Result ID: <b>$this->query_id</b> could not be freed.");
			}
			}#-#free_result()


			#-#############################################
			# desc: does a query, fetches the first row only, frees resultset
			# param: (MySQL query) the query to run on server
			# returns: array of fetched results
			public function query_first($query_string) {
			$query_id = $this->query($query_string);
			$out = $this->fetch_array($query_id);
			$this->free_result($query_id);
			return $out;
			}#-#query_first()


			#-#############################################
			# desc: does an update query with an array
			# param: table (no prefix), assoc array with data (doesn't need escaped), where condition
			# returns: (query_id) for fetching results etc
			public function query_update($table, $data, $where='1') {
			$q="UPDATE `".$this->pre.$table."` SET ";

			foreach($data as $key=>$val) {
			if(strtolower($val)=='null') $q.= "`$key` = NULL, ";
			elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
			elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $q.= "`$key` = `$key` + $m[1], ";
					else $q.= "`$key`='".$this->escape($val)."', ";
			}

			$q = rtrim($q, ', ') . ' WHERE '.$where.';';

			return $this->query($q);
			}#-#query_update()


			#-#############################################
			# desc: does an insert query with an array
			# param: table (no prefix), assoc array with data
			# returns: id of inserted record, false if error
			public function query_insert($table, $data) {
			$q="INSERT INTO `".$this->pre.$table."` ";
			$v=''; $n='';

			foreach($data as $key=>$val) {
				$n.="`$key`, ";
				if(strtolower($val)=='null') $v.="NULL, ";
				elseif(strtolower($val)=='now()') $v.="NOW(), ";
				else $v.= "'".$this->escape($val)."', ";
			}

			$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";

			if($this->query($q)){
				//$this->free_result();
				return mysql_insert_id($this->link_id);
			}
			else return false;

			}#-#query_insert()


			#-#############################################
			# desc: throw an error message
			# param: [optional] any custom error to display
			public function oops($msg='') {
				if($this->link_id>0){
					$this->error=mysql_error($this->link_id);
					$this->errno=mysql_errno($this->link_id);
				}
				else{
					$this->error=mysql_error();
					$this->errno=mysql_errno();
				}
				?>
			<table align="center" border="1" cellspacing="0" style="background:white;color:black;width:80%;">
			<tr><th colspan=2>Database Error</th></tr>
			<tr><td align="right" valign="top">Message:</td><td><?php echo $msg; ?></td></tr>
			<?php if(!empty($this->error)) echo '<tr><td align="right" valign="top" nowrap>MySQL Error:</td><td>'.$this->error.'</td></tr>'; ?>
			<tr><td align="right">Date:</td><td><?php echo date("l, F j, Y \a\\t g:i:s A"); ?></td></tr>
			<?php if(!empty($_SERVER['REQUEST_URI'])) echo '<tr><td align="right">Script:</td><td><a href="'.$_SERVER['REQUEST_URI'].'">'.$_SERVER['REQUEST_URI'].'</a></td></tr>'; ?>
			<?php if(!empty($_SERVER['HTTP_REFERER'])) echo '<tr><td align="right">Referer:</td><td><a href="'.$_SERVER['HTTP_REFERER'].'">'.$_SERVER['HTTP_REFERER'].'</a></td></tr>'; ?>
			</table>
		<?php
	}#-#oops()


}//CLASS Database

?>

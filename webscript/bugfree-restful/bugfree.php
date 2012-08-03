<?php
define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/"); 
require_once(LOG4PHP_DIR.'/Logger.php'); 
require_once('util.php');
require_once('mysqliadapter.php');


class Bugfree
{
    //logger对象
    private $logger = NULL;

    //数据库适配对象
    private $db = null;
    
    //当前的request方法
    private $method = 'GET';

    //request的body部分带的数据
    private $requestData = NULL;

    //
    private $dataPairs = array();

    private $stmtName = '';

    private $parameters = array();

    private $userURI = '';
	
    private $configs = array();

	public function __construct($configReader, $paramContent)
	{
		$this->logger = Logger::getLogger(__CLASS__);
		
        $this->method = $_SERVER['REQUEST_METHOD'];

		$this->userURI = translateChars($paramContent);
        $this->configs = $configReader->getConfigs();
        $this->logger->info("method =".$this->method);

		if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['REQUEST_METHOD'])) 
        {
            if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) 
            {
                $this->logger->debug("CONTENT_LENGTH = ".$_SERVER['CONTENT_LENGTH']);
                
                $this->requestData = '';
                $httpContent = fopen('php://input', 'r');
                while ($data = fread($httpContent, 4096)) 
                {
                    $this->requestData .= $data;
                }
                fclose($httpContent);

                $this->logger->debug("requestData-length: ".strlen($this->requestData));
                $this->logger->debug("requestData: ".$this->requestData);
            }
        }
	}
	
	/**
     * Execute the request.
     */
    function exec() 
    {
        $this->connect();
        
        switch ($this->method) 
        {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            case 'PUT':
                $this->handlePut();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
        }
   
        $this->db->close();        
    }
	
	/**
     * Connect to the database.
     */
    function connect()
    {
        $sectionName = "bugfree";
        $this->db = new MysqliAdapter($this->configs[$sectionName]);

        if (isset($this->configs[$sectionName]['username']) && isset($this->configs[$sectionName]['password']))
        {
            if (!$this->db->connect())
            {
                $this->logger->debug('Could not connect to server', E_USER_ERROR);
                exit;
            }
            $this->logger->debug("mysqli connect successful");
        }
        else
        {
            $this->logger->debug("no username or password");
            $this->unauthorized();
            exit;
        }
    }

    private function getQueryStatement($name)
    {
        $stmtContent = "";
        if (isset($this->configs["query"][$name])) 
        {
            $stmtContent = $this->configs["query"][$name];
        }

        $this->logger->info(sprintf("stmt code: %s=%s", $name, $stmtContent));
        return $stmtContent;
    }

    private function queryFailed($status, $tableName = 'bugfree')
    {
        $response = array();
        $response[] =sprintf("<MetaData name='%s' status='%s'>", $tableName, $status);
        $response[] ="</MetaData>";

        $this->logger->debug("queryFailed:\n".print_r($response, true));

        $this->printResponse($response);
    }

    private function querySuccess($status, $tableName, &$rowData) {
        $response = array();
        $response[] =sprintf("<MetaData name='%s' status='%s'>", $tableName, $status);
        $response = array_merge($response, $rowData);

        $response[] ="</MetaData>";

        $this->printResponse($response);
    }

    private function operateFailed($status, $tableName = 'bugfree')
    {
        $response = array();
        $response[] =sprintf("<MetaData name='%s' status='%s'>", $tableName, $status);
        $response[] ="</MetaData>";

        $this->logger->debug("operateFailed:\n".print_r($response, true));

        $this->printResponse($response);
    }

    private function operateSuccess($status, $tableName = 'bugfree')
    {
        $response = array();
        $response[] =sprintf("<MetaData name='%s' status='%s'>", $tableName, $status);
        $response[] ="</MetaData>";

        $this->logger->debug("operateSuccess:\n".print_r($response, true));

        $this->printResponse($response);
    }

    function toXmlFromStatement($stmt)
    {
        $xmlResult = array();

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
                echo "bind_result() failed.";
                exit;
            }

            while ($stmt->fetch())
            {
                array_push($xmlResult, "<row>");
                foreach($colList as $col)
                {
                    $itemKey = htmlspecialchars($col->name);
                    $itemValue = htmlspecialchars($data[$col->name]);
                    //$this->logger->debug("$itemKey = $itemValue");
                    $outString = sprintf("<%s>%s</%s>", $itemKey, xmlEncode($itemValue),$itemKey);
                    //$this->logger->debug($outString);
                    array_push($xmlResult, $outString);
                }
                array_push($xmlResult, "</row>");
            }
        } 
        else
        {
            $this->logger->debug("stmt is null");
        }

        $stmt->free_result();
        $stmt->close();

        return $xmlResult;
    }

    function toXmlFromResultObject($resultObj)
    {
        $xmlResult = array();

        $colList = array();//array that store the columns.
        $finfo = $resultObj->fetch_fields();
        foreach ($finfo as $val)
        {
            array_push($colList, $val->name);
            $this->logger->debug("Name: ".$val->name);
        }
        
        while ($row = $resultObj->fetch_row())
        {
            array_push($xmlResult, "<row>");
            for ($k = 0; $k<count($colList); $k++)
            {
                $itemValue = htmlspecialchars($row[$k]); 
                //$itemValue = htmlentities($row[$k], ENT_QUOTES);
                //$this->logger->debug("$colList[$k] = ".$itemValue);
                $outString = sprintf("<%s>%s</%s>", $colList[$k], $itemValue, $colList[$k]);
                //$this->logger->debug("outString: ".$outString);
                array_push($xmlResult, $outString);
            }
            array_push($xmlResult, "</row>");
        }

        /* free result set */
        $resultObj->close();

        return $xmlResult;
    }

    /**
     * Get the primary keys for the request table.
     * @return str[] The primary key field names
     */
    function getPrimaryKeys() {
    	return $this->db->getPrimaryKeys($this->table);

        #$resource = $this->db->getColumns($this->table);
        #$primary = NULL;
        #if ($resource) {
        #    while ($row = $this->db->row($resource)) {
        #        if ($row['Key'] == 'PRI') {
        #            $primary[] = $row['Field'];
        #        }
        #    }
        #}
        #return $primary;
    }
    
    /**
     * Parse params from userURI.
     */     
    function parseParams()
    {
        $this->logger->debug("enter parseParams(), userURI: $this->userURI");
        $uriParts = explode('&', $this->userURI);
        $itemCount = count($uriParts);
        if ($itemCount == 0)
        {
            $this->logger->debug("Error(1), There is no get parameter.");
            return false;
        }

        //remove last item if it's empty
        if (isset($uriParts[$itemCount - 1]) && $uriParts[$itemCount - 1] == '')
        {
            array_pop($uriParts);
        }
        
        //remove first item if it's empty
        if (isset($uriParts[0]) && $uriParts[0] == '')
        {
            array_shift($uriParts);
        }

        //if has no valid item then return
        if (count($uriParts) == 0)
        {
            $this->logger->debug("Error(1), There is no valid parameter.");
            return false;
        }
        
        //get statement name
        if (isset($uriParts[0]))
        {
            $this->stmtName = strtolower($uriParts[0]);
            array_shift($uriParts);
        }

        //get input parameters
        foreach($uriParts as $data)
        {
            $pos = strpos($data, '=');
            if ($pos === false)
            {
                $this->logger->debug("$data is not a valid parameter");
            }
            else
            {
                $key = substr($data, 0, $pos);
                $value = substr($data, $pos+1);
                $value = htmlspecialchars(urldecode($value));
                $this->logger->debug("after decode: $value");
                
                $encodeType = getEncodeType($value);
                if (isset($encodeType) && $encodeType == "gb2312")
                {
                	$value = mb_convert_encoding($value, "utf8", $encodeType);
                }
                $this->logger->debug("final value: $value");
                
                $this->parameters[strtolower($key)] = $value;
				$this->logger->debug("param: $key = $value");
            }
        }

        if ($this->stmtName != null)
            $this->logger->debug("statement name = ".$this->stmtName);

        $this->logger->debug("leave parseParams(), all parameters: ".print_r($this->parameters, true));
        return true;
    }

    /**
     * Execute a GET request. A GET request fetches a list of tables when no table name is given, a list of rows
     * when a table name is given, or a table row when a table and primary key(s) are given. It does not change the
     * database contents.
     */
    function handleGet() 
    {
        //parse params
        if ($this->parseParams() == false)
            exit;

        if ($this->stmtName == "showtables")
        {
            $this->getShowTables();
        }
        else if ($this->stmtName == "showcolumns")
        {
            $this->getShowColumns();
        }
		else
		{
			//如果没参数就说明是查询整个表
			if (count($this->parameters) == 0)
			{
				$this->getWholeTable();
			}
			else if ($this->stmtName == "bf_caseview")
			{
				$this->getCaseView();
			}
		}
		/*
        else if ($this->stmtName == "widgeturi")
        {
            $this->get_widgeturi();
        }
        else if ($this->stmtName == "testlist")
        {
            $this->logger->debug("step1");
            $this->get_testlist();
        }
        else if ($this->stmtName == "goldenhtml")
        {
            $this->get_goldenhtml();
        }
        else if ($this->stmtName == "testlistwidgetmap")
        {
            $this->get_testlistwidgetmap();
        }
        else if ($this->stmtName == "get_widgeturi_by_testlist_id")
        {
            $this->get_widgeturi_by_testlist_id();
        }
        else if ($this->stmtName == "get_widgeturi_by_testlist_id_valid_class")
        {
            $this->get_widgeturi_by_testlist_id_valid_class();
        }        
        else if ($this->stmtName == "get_max_widget_id")
        {
            $this->get_max_widget_id();
        }
        else if ($this->stmtName == "get_max_testlist_id")
        {
            $this->get_max_testlist_id();
        }
		*/
    }

    //list all tables by 'show tables'
    function getShowTables()
    {
        $stmtContent = $this->getQueryStatement($this->stmtName);
        if (strlen($stmtContent) == 0)
        {
            $status = "1: No prepare statement [$this->stmtName].";
            return $this->queryFailed($status);
        }

        $status = "0: Success";

        $metaData = array();

        $resultObj = $this->db->queryData($stmtContent);
        if (!$resultObj)
        {
            $status = "2: execute 'show tables' failed.";
            return $this->queryFailed($status);
        }

        $metaData = $this->toXmlFromResultObject($resultObj);

        $this->querySuccess($status, 'ShowTables', $metaData);
    }

    //list all columns by 'show columns from table-name'
    function getShowColumns()
    {
        $stmtContent = $this->getQueryStatement($this->stmtName);
        if (strlen($stmtContent) == 0)
        {
            $status = "1: No prepare statement [$this->stmtName].";
            return $this->queryFailed($status);
        }

        $status = "0: Success";

        if (!isset($this->parameters["table"]))
        {
            $status = "1: No input table name.";
            return $this->queryFailed($status);
        }

        $tableName = $this->parameters["table"];
        $this->logger->debug("tableName: ".$tableName);
        $metaData = array();
        

        $resultObj = $this->db->queryData("$stmtContent ".$tableName);
        if (!$resultObj)
        {
            $status = "2: No columns in table -- $tableName";
            return $this->queryFailed($status);
        }

        $metaData = $this->toXmlFromResultObject($resultObj);

        $this->querySuccess($status, $tableName, $metaData);
    }

	function getWholeTable()
	{
		$status = "0: Success";
		$tableName = $this->stmtName;

        $resultObj = $this->db->queryData("select * from ".$tableName);
        if (!$resultObj)
        {
            $status = "1: Notable -- $tableName";
            return $this->queryFailed($status);
        }

		$metaData = array(); 
        $metaData = $this->toXmlFromResultObject($resultObj);

        $this->querySuccess($status, $tableName, $metaData);
	}
	
    function getCaseView()
    {
/*
目前仅支持有限的几个条件查询
1.title;
2.product_name;
3.module_name;
mysql> describe bf_caseview;
+------------------+---------------+------+-----+---------+-------+
| Field            | Type          | Null | Key | Default | Extra |
+------------------+---------------+------+-----+---------+-------+
| id               | int(11)       | NO   |     | 0       |       |
| created_at       | datetime      | NO   |     | NULL    |       |
| created_by       | int(11)       | NO   |     | NULL    |       |
| updated_at       | datetime      | NO   |     | NULL    |       |
| updated_by       | int(11)       | NO   |     | NULL    |       |
| case_status      | varchar(45)   | NO   |     | NULL    |       |
| assign_to        | int(11)       | YES  |     | NULL    |       |
| title            | varchar(255)  | NO   |     | NULL    |       |
| mail_to          | text          | YES  |     | NULL    |       |
| case_step        | text          | YES  |     | NULL    |       |
| lock_version     | smallint(6)   | NO   |     | NULL    |       |
| related_bug      | varchar(255)  | YES  |     | NULL    |       |
| related_case     | varchar(255)  | YES  |     | NULL    |       |
| related_result   | varchar(255)  | YES  |     | NULL    |       |
| productmodule_id | int(11)       | YES  |     | NULL    |       |
| modified_by      | text          | NO   |     | NULL    |       |
| delete_flag      | enum('0','1') | NO   |     | NULL    |       |
| product_id       | int(11)       | YES  |     | NULL    |       |
| priority         | tinyint(4)    | YES  |     | NULL    |       |
| product_name     | varchar(255)  | YES  |     | NULL    |       |
| module_name      | mediumtext    | YES  |     | NULL    |       |
| created_by_name  | varchar(45)   | YES  |     | NULL    |       |
| updated_by_name  | varchar(45)   | YES  |     | NULL    |       |
| assign_to_name   | varchar(45)   | YES  |     | NULL    |       |
+------------------+---------------+------+-----+---------+-------+
24 rows in set (0.00 sec)
*/
        $this->logger->debug("enter getCaseInfo()");
        $status = "0: Success";

        $metaData = array();
        $stmtParams = new StatementParameter();
        $conditions = null;

        if (array_key_exists("title", $this->parameters))
        {
            $conditions = " title=? ";
            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_STRING, $this->parameters["title"]);
        }

        if (array_key_exists("product_name", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " product_name=? ";
            else
                $conditions = $conditions." and product_name=? ";
            
            $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, $this->parameters["product_name"]);
        }

        if (array_key_exists("module_name", $this->parameters))
        {
            if ($conditions == null)
                $conditions = sprintf(" module_name like '%s%%' ", $this->parameters["module_name"]);
            else
                $conditions = $conditions.sprintf(" and module_name like '%s%%' ", $this->parameters["module_name"]);
        }

        $stmtContent = "";
        if ($conditions == null)
        {
            $stmtContent = 'select * from bf_caseview order by id';
        }
        else
        {
            $stmtContent = sprintf("select * from bf_caseview where %s order by id", $conditions);
        }

        $this->logger->debug("stmtContent: ".$stmtContent);
        $stmt = $this->db->executeQuery($stmtContent, $stmtParams);

        if ($stmt == null)
        {
            $status = "2: create Prepare statment error, bf_caseview";
            return $this->queryFailed($status, "bf_caseview");
        }

        if($stmt->num_rows == 0)
        {
            $status = "1: No bf_caseview table is empty";
            return $this->queryFailed($status, "bf_caseview");
        }

        $metaData = $this->toXmlFromStatement($stmt);
        $this->querySuccess($status, "bf_caseview", $metaData);
    }

    function get_testlist()
    {
        $this->logger->debug("enter get_testlist()");
        $status = "0: Success";

        $metaData = array();
        $stmtParams = new StatementParameter();
        $conditions = null;

        if (array_key_exists("id", $this->parameters))
        {
            $conditions = " id=? ";
            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->parameters["id"]);
        }

        if (array_key_exists("name", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " name=? ";
            else
                $conditions = $conditions." and name=? ";

            $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, $this->parameters["name"]);
        }

        if (array_key_exists("version", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " version=? ";
            else
                $conditions = $conditions." and version=? ";

            $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_STRING, $this->parameters["version"]);
        }

        if (array_key_exists("diid", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " diid=? ";
            else
                $conditions = $conditions." and diid=? ";

            $stmtParams->addParameter('4', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->parameters["diid"]);
        }

        if (array_key_exists("dynamic", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " dynamic=? ";
            else
                $conditions = $conditions." and dynamic=? ";

            $stmtParams->addParameter('5', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->parameters["dynamic"]);
        }

        if (array_key_exists("active", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " active=? ";
            else
                $conditions = $conditions." and active=? ";

            $stmtParams->addParameter('6', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->parameters["active"]);
        }

        $offset = 0;
        if (array_key_exists('offset', $this->parameters))
        {
            $offset = $this->parameters['offset'];
        }
        $stmtParams->addParameter(':offset', StatementParameterType::$STATEMENT_TYPE_INTEGER, $offset);
        $this->logger->debug("offset: ".$offset);

        $max = 241591910;
        if (array_key_exists('max', $this->parameters))
        {
            $max = $this->parameters['max'];
        }
        $stmtParams->addParameter(':max', StatementParameterType::$STATEMENT_TYPE_INTEGER, $max);
        $this->logger->debug("max: ".$max);

        $stmtContent = "";
        if ($conditions == null)
        {
            $stmtContent = 'select * from testlist order by id limit ?, ?';
        }
        else
        {
            $stmtContent = sprintf("select * from testlist where %s order by id limit ?, ?", $conditions);
        }

        $this->logger->debug("stmtContent: ".$stmtContent);
        $stmt = $this->db->executeQuery($stmtContent, $stmtParams);

        if ($stmt == null)
        {
            $status = "2: create Prepare statment error, testlist";
            return $this->queryFailed($status, "testlist");
        }

        if($stmt->num_rows == 0)
        {
            $status = "1: No testlist table is empty";
            return $this->queryFailed($status, "testlist");
        }

        $metaData = $this->toXmlFromStatement($stmt);
        $this->querySuccess($status, "testlist", $metaData);
    }

    function get_goldenhtml()
    {
        $this->logger->debug("enter get_goldenhtml()");
        $status = "0: Success";

        $metaData = array();
        $stmtParams = new StatementParameter();
        $conditions = null;

        if (array_key_exists("widgetid", $this->parameters))
        {
            $conditions = " widgetid=? ";
            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->parameters["widgetid"]);
        }

        if (array_key_exists("version", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " version<=? ";
            else
                $conditions = $conditions." and version<=? ";

            $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, $this->parameters["version"]);
        }

        if (array_key_exists("configid", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " configid=? ";
            else
                $conditions = $conditions." and configid=? ";

            $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->parameters["configid"]);
        }

        if (array_key_exists("result", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " result=? ";
            else
                $conditions = $conditions." and result=? ";

            $stmtParams->addParameter('4', StatementParameterType::$STATEMENT_TYPE_STRING, $this->parameters["result"]);
        }

        $offset = 0;
        if (array_key_exists('offset', $this->parameters))
        {
            $offset = $this->parameters['offset'];
        }
        $stmtParams->addParameter(':offset', StatementParameterType::$STATEMENT_TYPE_INTEGER, $offset);
        $this->logger->debug("offset: ".$offset);

        $max = 1;
        if (array_key_exists('max', $this->parameters))
        {
            $max = $this->parameters['max'];
        }
        $stmtParams->addParameter(':max', StatementParameterType::$STATEMENT_TYPE_INTEGER, $max);
        $this->logger->debug("max: ".$max);

        $stmtContent = "";
        if ($conditions == null)
        {
            $stmtContent = 'select * from goldenhtml order by widgetid,version desc limit ?, ?';
        }
        else
        {
            $stmtContent = sprintf("select * from goldenhtml where %s order by widgetid,version desc limit ?, ?", $conditions);
        }

        $this->logger->debug("stmtContent: ".$stmtContent);
        $stmt = $this->db->executeQuery($stmtContent, $stmtParams);

        if ($stmt == null)
        {
            $status = "1: create Prepare statment error, goldenhtml";
            return $this->queryFailed($status, "goldenhtml");
        }

        if($stmt->num_rows == 0)
        {
            $status = "2: No goldenhtml table is empty";
            return $this->queryFailed($status, "goldenhtml");
        }

        $metaData = $this->toXmlFromStatement($stmt);
        $this->querySuccess($status, "goldenhtml", $metaData);
    }

    function get_testlistwidgetmap()
    {
        $this->logger->debug("enter get_testlistwidgetmap()");
        $status = "0: Success";

        $metaData = array();
        $stmtParams = new StatementParameter();
        $conditions = null;

        if (array_key_exists("widgetid", $this->parameters))
        {
            $conditions = " widgetid=? ";
            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->parameters["widgetid"]);
        }

        if (array_key_exists("testlistid", $this->parameters))
        {
            if ($conditions == null)
                $conditions = " testlistid=? ";
            else
                $conditions = $conditions." and testlistid=? ";

            $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->parameters["testlistid"]);
        }

        $offset = 0;
        if (array_key_exists('offset', $this->parameters))
        {
            $offset = $this->parameters['offset'];
        }
        $stmtParams->addParameter(':offset', StatementParameterType::$STATEMENT_TYPE_INTEGER, $offset);
        $this->logger->debug("offset: ".$offset);

        $max = 241591910;
        if (array_key_exists('max', $this->parameters))
        {
            $max = $this->parameters['max'];
        }
        $stmtParams->addParameter(':max', StatementParameterType::$STATEMENT_TYPE_INTEGER, $max);
        $this->logger->debug("max: ".$max);

        $stmtContent = "";
        if ($conditions == null)
        {
            $stmtContent = 'select * from testlistwidgetmap order by testlistid limit ?, ?';
        }
        else
        {
            $stmtContent = sprintf("select * from testlistwidgetmap where %s order by testlistid limit ?, ?", $conditions);
        }

        $this->logger->debug("stmtContent: ".$stmtContent);
        $stmt = $this->db->executeQuery($stmtContent, $stmtParams);
        if ($stmt == null)
        {
            $status = "1: create Prepare statment error, testlistwidgetmap";
            return $this->queryFailed($status, "testlistwidgetmap");
        }

        if($stmt->num_rows == 0)
        {
            $status = "2: No testlistwidgetmap table is empty";
            return $this->queryFailed($status, "testlistwidgetmap");
        }

        $metaData = $this->toXmlFromStatement($stmt);
        $this->querySuccess($status, "testlistwidgetmap", $metaData);
    }

    function get_max_widget_id()
    {
        $stmtContent = $this->getQueryStatement($this->stmtName);
        if (strlen($stmtContent) == 0)
        {
            $status = "2: No prepare statement [$this->stmtName].";
            return $this->queryFailed($status);
        }

        $status = "0: Success";
        $metaData = array();

        $stmtParams = new StatementParameter();
        $resultObj = $this->db->queryData($stmtContent);
        if (!$resultObj)
        {
            $status = "1: query max id from widgeturi table failed.";
            return $this->queryFailed($status);
        }

        $metaData = $this->toXmlFromResultObject($resultObj);

        $this->querySuccess($status, "widgeturi", $metaData);
    }

    function get_max_testlist_id()
    {
        $stmtContent = $this->getQueryStatement($this->stmtName);
        if (strlen($stmtContent) == 0)
        {
            $status = "2: No prepare statement [$this->stmtName].";
            return $this->queryFailed($status);
        }

        $status = "0: Success";
        $metaData = array();

        $stmtParams = new StatementParameter();
        $resultObj = $this->db->queryData($stmtContent);
        if (!$resultObj)
        {
            $status = "1: query max id from testlist table failed.";
            return $this->queryFailed($status);
        }

        $metaData = $this->toXmlFromResultObject($resultObj);

        $this->querySuccess($status, "testlist", $metaData);
    }

    function get_widgeturi_by_testlist_id()
    {
        $stmtContent = $this->getQueryStatement($this->stmtName);
        if (strlen($stmtContent) == 0)
        {
            $status = "2: No prepare statement [$this->stmtName].";
            return $this->queryFailed($status);
        }

        $status = "0: Success";

        if (!isset($this->parameters["testlist_id"]))
        {
            $status = "1: No input testlist id.";
            return $this->queryFailed($status, "widgeturi");
        }

        $testlist_id = $this->parameters["testlist_id"];
        $this->logger->debug("testlist_id: ".$testlist_id);
        $metaData = array();

        $stmtParams = new StatementParameter();
        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $testlist_id);
        $stmt = $this->db->executeQuery($stmtContent, $stmtParams);

        if($stmt->num_rows == 0)
        {
            $status = "2: No widgeturi by, $testlist_id";
            return $this->queryFailed($status, "widgeturi");
        }

        $metaData = $this->toXmlFromStatement($stmt);
        $this->querySuccess($status, "widgeturi", $metaData);
    }
    
    function get_widgeturi_by_testlist_id_valid_class()
    {
        $stmtContent = $this->getQueryStatement($this->stmtName);
        if (strlen($stmtContent) == 0)
        {
            $status = "2: No prepare statement [$this->stmtName].";
            return $this->queryFailed($status);
        }

        $status = "0: Success";

        if (!isset($this->parameters["testlist_id"]))
        {
            $status = "1: No input testlist id.";
            return $this->queryFailed($status, "widgeturi");
        }

        if (!isset($this->parameters["class"]))
        {
            $status = "2: No input device valid class.";
            return $this->queryFailed($status, "widgeturi");
        }


        $testlist_id = $this->parameters["testlist_id"];
        $valid_class = $this->parameters["class"];

        $metaData = array();

        $stmtParams = new StatementParameter();
        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $valid_class);
        $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_INTEGER, $valid_class);
        $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_INTEGER, $testlist_id);
        $stmt = $this->db->executeQuery($stmtContent, $stmtParams);

        if($stmt->num_rows == 0)
        {
            $status = "3: No widgeturi by, $testlist_id and $valid_class";
            return $this->queryFailed($status, "widgeturi");
        }

        $metaData = $this->toXmlFromStatement($stmt);
        $this->querySuccess($status, "widgeturi", $metaData);
    }
    
    function parsePostParams()
    {
        //parse statement and parameters
        if ($this->parseParams() == false)
            return false;

        //parse data
        return $this->parseRequestData();
    }
    
    /**
     * Execute a POST request.
     */
    function handlePost() 
    {
        //parse params
        if ($this->parsePostParams() == false)
            exit;

        if ($this->stmtName == "goldenhtml")
        {
            $this->insert_golden();
        }
        else if ($this->stmtName == "testlist")
        {
            $this->insert_testlist();
        }
        else if ($this->stmtName == "widgeturi")
        {
            $this->insert_widgeturi();
        }
        else if ($this->stmtName == "testlistwidgetmap")
        {
            $this->insert_testlistwidgetmap();
        }
    }

    function insert_golden()
    {
        $this->logger->debug("enter insert_golden()");
        $status = "0: Success";

        $stmtParams = new StatementParameter();

        if (array_key_exists("widgetid", $this->dataPairs))
        {
            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->dataPairs["widgetid"]);
        }
        else
        {
            $status = "1: No input widget id.";
            return $this->operateFailed($status, "goldenhtml");
        }

        if (array_key_exists("configid", $this->dataPairs))
        {
            $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->dataPairs["configid"]);
        }
        else
        {
            $status = "2: No input config id.";
            return $this->operateFailed($status, "goldenhtml");
        }

        if (array_key_exists("version", $this->dataPairs))
        {
            $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["version"]);
        }
        else
        {
            $status = "3: No input version.";
            return $this->operateFailed($status, "goldenhtml");
        }

        if (array_key_exists("goldenoutput", $this->dataPairs))
        {
            $stmtParams->addParameter('4', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["goldenoutput"]);
        }
        else
        {
            $status = "4: No input goldenoutput.";
            return $this->operateFailed($status, "goldenhtml");
        }

        if (array_key_exists("result", $this->dataPairs))
        {
            $stmtParams->addParameter('5', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["result"]);
        }
        else
        {
            $status = "5: No input result.";
            return $this->operateFailed($status, "goldenhtml");
        }
        
        $stmtContent = 'insert into goldenhtml (WidgetID, configid, Version, GoldenOutput, Result) values(?,?,?,?,?)';

        if ($this->db->executeInsert($stmtContent, $stmtParams) == false)
        {
            $status = "6: update golden failed.";
            return $this->operateFailed($status, "goldenhtml");
        }

        $this->operateSuccess($status, "goldenhtml");
    }

    function insert_testlist()
    {
        $status = "0: Success";

        $stmtParams = new StatementParameter();

        if (!array_key_exists("name", $this->dataPairs))
        {
            $status = "1: No input name.";
            return $this->operateFailed($status, "testlist");
        }
        else
        {
            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["name"]);

        }

        if (!array_key_exists("version", $this->dataPairs))
        {
            $status = "2: No input version.";
            return $this->operateFailed($status, "testlist");
        }
        else
        {
            $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["version"]);
        }

        if (!array_key_exists("diid", $this->dataPairs))
        {
            $status = "3: No input diid.";
            return $this->operateFailed($status, "testlist");
        }
        else
        {
            $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->dataPairs["diid"]);
        }

        if (!array_key_exists("dynamic", $this->dataPairs))
        {
            $status = "4: No input dynamic.";
            return $this->operateFailed($status, "testlist");
        }
        else
        {
            $stmtParams->addParameter('4', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->dataPairs["dynamic"]);
        }

        if (!array_key_exists("active", $this->dataPairs))
        {
            $status = "5: No input active.";
            return $this->operateFailed($status, "testlist");
        }
        else
        {
            $stmtParams->addParameter('5', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->dataPairs["active"]);
        }

        $stmtContent = 'insert into testlist (Name, Version, DIID, Dynamic, Active) values(?,?,?,?,?)';

        if ($this->db->executeInsert($stmtContent, $stmtParams) == false)
        {
            $status = "6: update testlist failed.";
            return $this->operateFailed($status, "testlist");
        }

        $this->operateSuccess($status, "testlist");
    }

    function insert_widgeturi()
    {
        $status = "0: Success";

        $stmtParams = new StatementParameter();

        if (!array_key_exists("uri", $this->dataPairs))
        {
            $status = "1: No input uri.";
            return $this->operateFailed($status, "widgeturi");
        }
        else
        {
            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["uri"]);

        }

        if (!array_key_exists("note", $this->dataPairs))
        {
            $status = "2: No input note.";
            return $this->operateFailed($status, "widgeturi");
        }
        else
        {
            $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["note"]);
        }

        if (!array_key_exists("validdeviceclass", $this->dataPairs))
        {
            $status = "3: No input validdeviceclass.";
            return $this->operateFailed($status, "widgeturi");
        }
        else
        {
            $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->dataPairs["validdeviceclass"]);
        }

        if (!array_key_exists("active", $this->dataPairs))
        {
            $status = "4: No input active.";
            return $this->operateFailed($status, "widgeturi");
        }
        else
        {
            $stmtParams->addParameter('4', StatementParameterType::$STATEMENT_TYPE_INTEGER, $this->dataPairs["active"]);
        }

        $stmtContent = 'insert into widgeturi (uri, note, validdeviceclass, active) values(?,?,?,?)';

        if ($this->db->executeInsert($stmtContent, $stmtParams) == false)
        {
            $status = "6: update widgeturi failed.";
            return $this->operateFailed($status, "widgeturi");
        }

        $this->operateSuccess($status, "widgeturi");
    }

    function insert_testlistwidgetmap()
    {
        $status = "0: Success";

        $stmtParams = new StatementParameter();

        if (!array_key_exists("testlistid", $this->dataPairs))
        {
            $status = "1: No input testlistid.";
            return $this->operateFailed($status, "testlistwidgetmap");
        }
        else
        {
            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["testlistid"]);

        }

        if (!array_key_exists("widgetid", $this->dataPairs))
        {
            $status = "2: No input widgetid.";
            return $this->operateFailed($status, "testlistwidgetmap");
        }
        else
        {
            $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["widgetid"]);
        }

        $stmtContent = 'insert into testlistwidgetmap (testlistid, widgetid) values(?,?)';

        if ($this->db->executeInsert($stmtContent, $stmtParams) == false)
        {
            $status = "3: update testlistwidgetmap failed.";
            return $this->operateFailed($status, "testlistwidgetmap");
        }

        $this->operateSuccess($status, "testlistwidgetmap");
    }
    
    function parsePutParams()
    {
        //parse statement and parameters
        if ($this->parseParams() == false)
            return false;

        //parse data
        return $this->parseRequestData();
    }

    /**
     * Execute a PUT request. A PUT request adds a new row to a table given a table and name=value pairs in the
     * request body.
     */
    function handlePut() 
    {
        //parse params
        if ($this->parsePutParams() == false)
            exit;

        $this->logger->debug("parse put params successfully.");
        if ($this->stmtName == "goldenhtml")
        {
            $this->update_golden();
        }
        else if($this->stmtName == "testlist")
        {
            $this->update_testlist();
        }
    }

    function update_golden()
    {
        $status = "0: Success";

        $metaData = array();

        if (!array_key_exists("widgetid", $this->parameters))
        {
            $status = "1: No input widget id.";
            return $this->operateFailed($status, "goldenhtml");
        }

        if (!array_key_exists("configid", $this->parameters))
        {
            $status = "2: No input config id.";
            return $this->operateFailed($status, "goldenhtml");
        }

        if (!array_key_exists("version", $this->parameters))
        {
            $status = "3: No input version.";
            return $this->operateFailed($status, "goldenhtml");
        }

        $stmtParams = new StatementParameter();
        $prepare_sql = "update goldenhtml set ";
        $isFirstItem = true;
        if (array_key_exists("goldenoutput", $this->dataPairs))
        {
            $prepare_sql = $prepare_sql."GoldenOutput=? ";
            $stmtParams->addParameter(':output', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["goldenoutput"]);
            $isFirstItem = false;
        }

        if (array_key_exists("result", $this->dataPairs))
        {
            if ($isFirstItem == true)
            {
                $prepare_sql = $prepare_sql." Result=? ";
            }
            else
            {
                $prepare_sql = $prepare_sql.", Result=? ";
            }
            $stmtParams->addParameter(':result', StatementParameterType::$STATEMENT_TYPE_STRING, $this->dataPairs["result"]);
        }

        $widgetid = $this->parameters["widgetid"];
        $configid = $this->parameters["configid"];
        $version  = $this->parameters["version"];
        
        $query_sql = sprintf("select WidgetID from goldenhtml where WidgetID=%d and Version='%s' and configid=%d", $widgetid, $version, $configid);
        $this->logger->debug("query_sql: ".$query_sql);
        $stmtQueryParams = new StatementParameter();
        $stmt = $this->db->executeQuery($query_sql, $stmtQueryParams);

        if ($stmt != null && $stmt->num_rows == 1)
        {
            $prepare_sql = $prepare_sql." where WidgetID=? and Version=? and configid=?";

            $metaData = array();

            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $widgetid);
            $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, $version);
            $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_INTEGER, $configid);
            if ($this->db->executeInsert($prepare_sql, $stmtParams) == false)
            {
                $status = "5: update golden failed.";
                return $this->operateFailed($status, "goldenhtml");
            }
        }
        else
        {//insert new golden data
            $goldendata = "";
            $result = "pass";
            if (array_key_exists("goldenoutput", $this->dataPairs))
            {
                $goldendata = $this->dataPairs["goldenoutput"];

                $stmtContent = 'insert into goldenhtml (WidgetID, configid, Version, GoldenOutput, Result) values(?,?,?,?,?)';
                $stmtParams = new StatementParameter();

                $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $widgetid);
                $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_INTEGER, $configid);
                $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_STRING, $version);
                $stmtParams->addParameter('4', StatementParameterType::$STATEMENT_TYPE_STRING, $goldendata);
                $stmtParams->addParameter('5', StatementParameterType::$STATEMENT_TYPE_STRING, $result);

                if ($this->db->executeInsert($stmtContent, $stmtParams) == false)
                {
                    $status = "6: insert golden failed.";
                    return $this->operateFailed($status, "goldenhtml");
                }
            }
        }
        
        $this->operateSuccess($status, "goldenhtml");
    }

    function update_testlist()
    {
        $status = "0: Success";

        $metaData = array();


        if (!array_key_exists("id", $this->parameters))
        {
            $status = "1: No input testlist id.";
            return $this->operateFailed($status, "testlist");
        }

        $stmtParams = new StatementParameter();
        $prepare_sql = null;

        if (array_key_exists("name", $this->dataPairs))
        {
            $prepare_sql = "name=".$this->dataPairs["name"];
        }

        if (array_key_exists("version", $this->dataPairs))
        {
            if ($prepare_sql == null)
            {
                $prepare_sql = "version=".$this->dataPairs["version"];
            }
            else
            {
                $prepare_sql = $prepare_sql.", version=".$this->dataPairs["result"];
            }
        }

        if (array_key_exists("diid", $this->dataPairs))
        {
            if ($prepare_sql == null)
            {
                $prepare_sql = "diid=".$this->dataPairs["diid"];
            }
            else
            {
                $prepare_sql = $prepare_sql.", diid=".$this->dataPairs["result"];
            }
        }

        if (array_key_exists("dynamic", $this->dataPairs))
        {
            if ($prepare_sql == null)
            {
                $prepare_sql = "dynamic=".$this->dataPairs["dynamic"];
            }
            else
            {
                $prepare_sql = $prepare_sql.", dynamic=".$this->dataPairs["dynamic"];
            }
        }

        if (array_key_exists("active", $this->dataPairs))
        {
            if ($prepare_sql == null)
            {
                $prepare_sql = "active=".$this->dataPairs["active"];
            }
            else
            {
                $prepare_sql = $prepare_sql.", active=".$this->dataPairs["active"];
            }
        }

        if ($prepare_sql == null)
        {
            $status = "4: No any input data.";
            return $this->operateFailed($status, "testlist");
        }

        $prepare_sql = sprintf("update testlist set %s where id=?", $prepare_sql);
        $testlistid = $this->parameters["id"];

        $metaData = array();

        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $testlistid);
        if ($this->db->executeInsert($prepare_sql, $stmtParams) == false)
        {
            $status = "5: update testlist failed.";
            return $this->operateFailed($status, "testlist");
        }

        $this->operateSuccess($status, "testlist");
    }
    
    /**
     * parse delete params
     */
    function parseDeleteParams()
    {
        //parse statement and parameters
        return $this->parseParams();

        //parse data
        //return $this->parseRequestData();
    }

    /**
    * Execute a DELETE request. A DELETE request removes a row from the database given a table and primary key(s).
    */
    function handleDelete() 
    {
        $this->logger->debug("enter delete");
        //parse params
        if ($this->parseDeleteParams() == false)
            exit;
        $this->logger->debug("stmt name: $this->stmtName");
        if ($this->stmtName == "goldenhtml")
        {
            $this->delete_golden();
        }
        else if ($this->stmtName == "testlist")
        {
            $this->delete_testlist();
        }
        else if ($this->stmtName == "testlistwidgetmap")
        {
            $this->delete_testlistwidgetmap();
        }
    }

    function delete_golden()
    {
        $this->logger->debug("enter delete_golden");
        $status = "0: Success";

        if (!array_key_exists("widgetid", $this->parameters))
        {
            $status = "1: No input widget id.";
            return $this->operateFailed($status, "goldenhtml");
        }

        if (!array_key_exists("configid", $this->parameters))
        {
            $status = "2: No input config id.";
            return $this->operateFailed($status, "goldenhtml");
        }

        if (!array_key_exists("version", $this->parameters))
        {
            $status = "3: No input version.";
            return $this->operateFailed($status, "goldenhtml");
        }

        $stmtParams = new StatementParameter();
        $prepare_sql = "delete from goldenhtml where WidgetID=? and Version=? and configid=?";
        $widgetid = $this->parameters["widgetid"];
        $configid = $this->parameters["configid"];
        $version  = $this->parameters["version"];

        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $widgetid);
        $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, $version);
        $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_INTEGER, $configid);
        if ($this->db->executeInsert($prepare_sql, $stmtParams) == false)
        {
            $status = "4: delete golden failed.";
            return $this->operateFailed($status, "goldenhtml");
        }

        $this->operateSuccess($status, "goldenhtml");
    }

    function delete_testlist()
    {
        $status = "0: Success";

        if (!array_key_exists("id", $this->parameters))
        {
            $status = "1: No input testlist id.";
            return $this->operateFailed($status, "testlist");
        }

        $prepare_sql = "delete from testlist where id=?";
        $testlistid = $this->parameters["id"];

        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $testlistid);
        if ($this->db->executeInsert($prepare_sql, $stmtParams) == false)
        {
            $status = "2: delete testlist failed.";
            return $this->operateFailed($status, "testlist");
        }

        $this->operateSuccess($status, "testlist");
    }
    
    function delete_testlistwidgetmap()
    {
        $status = "0: Success";

        if (!array_key_exists("testlistid", $this->parameters))
        {
            $status = "1: No input testlist id.";
            return $this->operateFailed($status, "testlistwidgetmap");
        }

        $prepare_sql = "delete from testlistwidgetmap where testlistid=?";
        $testlistid = $this->parameters["id"];

        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $testlistid);
        if ($this->db->executeInsert($prepare_sql, $stmtParams) == false)
        {
            $status = "2: delete testlist failed.";
            return $this->operateFailed($status, "testlistwidgetmap");
        }

        $this->operateSuccess($status, "testlistwidgetmap");
    }
    /**
     * Parse the HTTP request data.
     * @return str[] Array of name value pairs
     */
    function parseRequestData() {
        $this->logger->debug("enter parseRequestData");
        $pairs = explode("\n\r\n", $this->requestData);
        $flag = '=';
        $hasData = false;
        foreach ($pairs as $pair) 
        {
            $pos = strpos($pair, $flag);
            if ($pos !== false)
            {
                $key = substr($pair, 0, $pos);
                $value = substr($pair, $pos+1);

                if (isset($key) && isset($value))
                {
                    $key = strtolower($key);
                    $this->dataPairs[$key] = $value;
                    $hasData = true;
                    $this->logger->debug("key: ".$key);
                }
            }
        }
        $this->logger->debug("leave parseRequestData".print_r($this->dataPairs, true));
        return $hasData;
    }    
    
    /**
     * Send a HTTP 201 response header.
     */
    function created($url = FALSE) {
        header('HTTP/1.0 201 Created');
        if ($url) 
        {
            header('Location: '.$url);   
        }
    }
    
    /**
     * Send a HTTP 204 response header.
     */
    function noContent() {
        header('HTTP/1.0 204 No Content');
    }
    
    /**
     * Send a HTTP 400 response header.
     */
    function badRequest() {
        header('HTTP/1.0 400 Bad Request');
    }
    
    /**
     * Send a HTTP 401 response header.
     */
    function unauthorized($realm = 'PHPRestSQL') {
        header('WWW-Authenticate: Basic realm="'.$realm.'"');
        header('HTTP/1.0 401 Unauthorized');
    }
    
    /**
     * Send a HTTP 404 response header.
     */
    function notFound() {
        header('HTTP/1.0 404 Not Found');
    }
    
    /**
     * Send a HTTP 405 response header.
     */
    function methodNotAllowed($allowed = 'GET, HEAD') {
        header('HTTP/1.0 405 Method Not Allowed');
        header('Allow: '.$allowed);
    }
    
    /**
     * Send a HTTP 406 response header.
     */
    function notAcceptable() {
        header('HTTP/1.0 406 Not Acceptable');
        echo join(', ', array_keys($this->config['renderers']));
    }
    
    /**
     * Send a HTTP 411 response header.
     */
    function lengthRequired() {
        header('HTTP/1.0 411 Length Required');
    }
    
    /**
     * Send a HTTP 500 response header.
     */
    function internalServerError() {
        header('HTTP/1.0 500 Internal Server Error');
    }
 
    /**
     * Create XML data set by mysql result set which is returned by mysql_query() function.
     */
    function generateXML($resource)
    {
        if ($resource)
        {
            /* get column metadata */
            $columnList = array();
            $columnNum = mysql_num_fields($resource);
            $i = 0;
            while ($i < $columnNum)
            {
                $meta = mysql_fetch_field($resource, $i);
                array_push($columnList, $meta);
                $i++;
            }

            $rowNum = $this->db->numRows($resource);

            header('Content-Type: text/xml');
            echo '<?xml version="1.0" encoding="gb2312" standalone="yes"?>';
            $outString = sprintf("<%s>", $columnList[0]->table);
            echo $outString;

            $i = 0;
            while ($row = $this->db->row($resource))
            {
                //echo '<Row index='.$i.'>';
                echo "<Row>";
                $k = 0;
                for (; $k < $columnNum; $k++)
                {
                    $itemKey = xmlEncode($columnList[$k]->name);
                    $itemValue = xmlEncode($row[$columnList[$k]->name]);
                    $outString = sprintf("<%s> %s </%s>", $itemKey, $itemValue, $itemKey);
                    echo $outString;
                }
                echo '</Row>';
                $i++;
            }
            $outString = sprintf("</%s>", $columnList[0]->table);
            echo $outString;
        }
    }

    function printResponse($response)
    {
        header('Content-Type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

        //write data
        foreach($response as $rowtxt)
        {
            echo $rowtxt;
        }
    }
    //test
}
?>

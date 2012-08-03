<?php
/**
 * CaseWriter用于把数组里的case写到bugfree里;
 * @var unknown_type
 */
define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/");
require_once(LOG4PHP_DIR.'/Logger.php');
require_once('util.php');
require_once("configreader.php");
require_once('mysqliadapter.php');

class CaseWriter
{
	//logger对象
	private $logger = NULL;
	
	//保存解析后的数据数组
	private $productResult = array();
	
	//保存环境设置，某些值可能会用于生成测试结果
	private $envSetting = array();
	
	//数据库适配对象
	private $casedb = null;

	//bugzilla数据库连接
	private $bugdb = null;
	
	//配置文件
	private $configs = array();
	
	private $qihooMail = "@360.cn";
	
	/**
	 * 构造函数，
	 * @param unknown_type $envSetting -- 环境配置数组
	 * @param unknown_type $result -- 测试结果数组
	 */
	public function __construct(&$envSetting, &$result)
	{
		$this->logger = Logger::getLogger(__CLASS__);
		$this->productResult = $result;
		$this->envSetting = $envSetting;
	}
	
    public function getEnvSetting()
    {
        return $this->envSetting;
    }

    public function getProductResult()
    {
        return $this->productResult;
    }

	/**
	 * 连接数据库
	 */
	private function connectDB()
	{
		//config reader
		$configReader = new ConfigReader();
		$this->configs = $configReader->getConfigs();
		
        return $this->connectCaseDB() & $this->connectBugDB();
	}
	
	/**
	 * 连接case数据库
	 */
	private function connectCaseDB()
	{
		$this->logger->debug("enter connectCaseDB()");
		//config reader		
		$sectionName = "bugfree";
		$this->logger->debug("mysql ini:\n".print_r($this->configs[$sectionName], true));
		$this->casedb = new MysqliAdapter($this->configs[$sectionName]);
		
		if (isset($this->configs[$sectionName]['username']) && isset($this->configs[$sectionName]['password']))
		{
			if (!$this->casedb->connect())
			{
				$this->logger->error('Could not connect to bugfree server', E_USER_ERROR);
				return 0;
			}
			return 1;
		}
		else
		{
			$this->logger->error("no username or password");
		
			return 0;
		}
	}
	
	/**
	 * 连接bugzilla数据库
	 */
	private function connectBugDB()
	{
		$this->logger->debug("enter connectBugDB()");
		//config reader		
		$sectionName = "bugzilla";
		$this->logger->debug("mysql ini:\n".print_r($this->configs[$sectionName], true));
		$this->bugdb = new MysqliAdapter($this->configs[$sectionName]);
		
		if (isset($this->configs[$sectionName]['username']) && isset($this->configs[$sectionName]['password']))
		{
			if (!$this->bugdb->connect())
			{
				$this->logger->error('Could not connect to bugzilla server', E_USER_ERROR);
				return 0;
			}
			return 1;
		}
		else
		{
			$this->logger->error("no username or password");
		
			return 0;
		}
	}
	
	/**
	 * 同步测试结果到bugfree
	 */
	public function syncTestResult()
	{
        $this->logger->debug("enter syncTestResult()");

		if (!$this->connectDB())
		{
			$this->logger->error("don't sync any data with bugfree.");
			return 0;
		}
		$this->logger->debug("begin handle product");

		//循环每个产品
		foreach ($this->productResult as &$oneProduct)
		{
			$this->syncOneProduct($oneProduct);
		}
	    
		return 1;
	}
	
	/**
	 * 同步一个产品节点
	 * @param unknown_type $oneProduct
	 */
	private function syncOneProduct(&$oneProduct)
	{
        $this->logger->debug("enter syncOneProduct()");

		//首先把属性提取出来，在产品数组里，非数组成员就是属性
		$proAttriList = array();
		
		foreach($oneProduct as $key => $value)
		{
			if (gettype($value) != "array")
			{
				$proAttriList[$key] = $value;
			}
		}
		
		//如果有产品名字则根据产品名字获得ID
		if (!array_key_exists("id", $proAttriList))//如果没有ID则需要查找ID
		{
			if (array_key_exists("name", $proAttriList))
			{
				$productID = $this->getProductIDByName($proAttriList["name"]);
				
				if (isset($productID))
				{
					$proAttriList["id"] = $productID;
				}
				else
				{
					$this->logger->error(sprintf("can't find product id by name[%s]", $proAttriList["name"]));
					return;
				}
			}
			else
			{
				$this->logger->error("can't find product id and name");
				return;
			}
		}
		
		//获得user id
		$this->fillUserID($proAttriList);		
		
		//循环查找每个模块，并同步每个模块
		foreach($oneProduct as $key => &$value)
		{
			if (gettype($value) == "array")
			{
				$this->syncOneModule($proAttriList, $value);
			}
		}
	}
	
	/**
	 * 如果有user属性，则把user的id查询出来，否则查找环境里是否有user属性，如果都没设置user，则默认使用1的userid
	 * @param unknown_type $proAttriList
	 */
	private function fillUserID(&$proAttriList)
	{
		//如果用户设置了userid，则直接返回
		if (array_key_exists("userid", $proAttriList))
		{
			return;
		}
		
		//如果产品节点里设置了username
		if (array_key_exists("username", $proAttriList))
		{
			$userID = $this->getUserIDbyName($proAttriList["username"]);
			
			if (isset($userID))
			{
				$proAttriList["userid"] = $userID;
				return;
			}
		}
		
		//如果环境里设置了username
		if (isset($this->envSetting) && array_key_exists("username", $this->envSetting))
		{
            $this->logger->debug("username: ".$this->envSetting["username"]);

			$userID = $this->getUserIDbyName($this->envSetting["username"]);
				
			if (isset($userID))
			{
				$proAttriList["userid"] = $userID;
				return;
			}
		}
		
        $this->logger->debug("user default user id");
		//如果都没设置则使用默认的user id
		$proAttriList["userid"] = 1;
	}
	
	private function getUserIDbyName($userName)
	{
		$sqlCmd = "select id from bf_test_user where trim(lower(username)) = ? ";
		$stmtParams = new StatementParameter();
		$stmtParams->addParameter('title', StatementParameterType::$STATEMENT_TYPE_STRING, trim(strtolower($userName)));
		
		$userID = NULL;
		$this->logger->debug("sqlCmd: $sqlCmd");
		
		$rows = $this->casedb->fetchArray($sqlCmd, $stmtParams);
		if (count($rows) == 1)
		{
			$userID = $rows[0]["id"];			
		}	
		
		return $userID;
	}
	
	/**
	 * 同步一个模块
	 * @param unknown_type $proAttriList
	 * @param unknown_type $oneModule
	 */
	private function syncOneModule($proAttriList, &$oneModule)
	{
        $this->logger->debug("enter syncOneModule()");

		//首先把属性提取出来，在产品数组里，非数组成员就是属性
		$moduleAttriList = array();
		
		foreach($oneModule as $key => $value)
		{
			if (gettype($value) != "array")
			{
				$moduleAttriList[$key] = $value;
			}
		}
		
        $this->logger->debug("module attr:\n".print_r($moduleAttriList, true));

		//如果有产品名字则根据产品名字获得ID
		if (!array_key_exists("id", $moduleAttriList))//如果不存在ID则需要根据名称查找
		{
			if (array_key_exists("name", $moduleAttriList))
			{
				$moduleID = $this->getModuleIDByName($proAttriList, $moduleAttriList["name"]);
					
				if (isset($moduleID))
				{
					$moduleAttriList["id"] = $moduleID;
				}
				else
				{
					$this->logger->error(sprintf("can't find module id by name[%s]", $moduleAttriList["name"]));
					return;
				}
			}
			else
			{
				$this->logger->error("can't find module id and name");
				return;
			}
		}
		
		//循环查找每个Case，并同步每个case
		foreach($oneModule as $key => &$value)
		{
			if (gettype($value) == "array")
			{
				$this->syncOneCase($proAttriList, $moduleAttriList, $value);
			}
		}
	}
	
	/**
	 * 根据产品名称查询产品ID
	 * @param unknown_type $proName
	 */
	private function getProductIDByName($proName)
	{
		$proID = null;
        $this->logger->debug("enter getProductIDByName()");
        
        $stmtParams = new StatementParameter();
        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_STRING, trim(strtolower($proName)));
        
		$sqlCmd = "select id from bf_product where trim(lower(name)) = ?;";
		$rows = $this->casedb->fetchArray($sqlCmd, $stmtParams);
		if (count($rows) > 0)
		{
			$proID = $rows[0]["id"];
		}
		else
		{
			$this->logger->error("can't find product id");
		}
		
		$this->logger->debug("leave getProductIDByName(), with [$proID]");
		return $proID;		
	}
	
	/**
	 * 根据产品ID和模块路径查找模块ID,因为模块是可以有多层的，每层以‘/’隔开，所以需要
	 * 递归查找
	 * @param unknown_type $productID
	 * @param unknown_type $moduleName
	 */
	private function getModuleIDByName($proAttriList, $modulePath)
	{
		//首先把path两头多余的斜线去掉
		$len = strlen($modulePath);
		if (strcmp(substr($modulePath, $len-1, 1), "/") == 0)
		{
			$modulePath = substr($modulePath, 0, $len - 1);
		}
		
		$len = strlen($modulePath);
		if (strcmp(substr($modulePath, 0, 1), "/") == 0)
		{
			$modulePath = substr($modulePath, 1, $len - 1);
		}
		
		//逐级查找模块ID
		$modules = explode("/", $modulePath);
		$parentid = NULL;
		$curPath = "";
		for ($i=0; $i<count($modules); $i++)
		{
            if (strlen($modules[$i]) == 0)
                continue;

			if (strlen($curPath) == 0)
                $curPath = $modules[$i];
			else
				$curPath = $curPath."/".$modules[$i];
			
			$parentid = $this->queryOrCreateModule($proAttriList["id"], $proAttriList["userid"], $parentid, $curPath, $modules[$i], $i+1);
		}
		
		return $parentid;
	}
	
	/**
	 * 查询一个模块，如果不存在则插入新的模块。
	 * @param unknown_type $productID
	 * @param unknown_type $parentid
	 * @param unknown_type $fullPathName
	 * @param unknown_type $moduleName
	 * @param unknown_type $grade
	 * mysql> describe bf_product_module;
	+----------------+-------------+------+-----+---------+----------------+
	| Field          | Type        | Null | Key | Default | Extra          |
	+----------------+-------------+------+-----+---------+----------------+
	| id             | int(11)     | NO   | PRI | NULL    | auto_increment |
	| name           | varchar(45) | NO   |     | NULL    |                |
	| grade          | smallint(6) | NO   |     | NULL    |                |
	| owner          | int(11)     | YES  |     | NULL    |                |
	| display_order  | smallint(6) | NO   |     | NULL    |                |
	| created_at     | datetime    | NO   |     | NULL    |                |
	| created_by     | int(11)     | NO   |     | NULL    |                |
	| updated_at     | datetime    | NO   |     | NULL    |                |
	| updated_by     | int(11)     | NO   |     | NULL    |                |
	| full_path_name | text        | NO   |     | NULL    |                |
	| product_id     | int(11)     | NO   | MUL | NULL    |                |
	| parent_id      | int(11)     | YES  | MUL | NULL    |                |
	| lock_version   | smallint(6) | NO   |     | NULL    |                |
	+----------------+-------------+------+-----+---------+----------------+
	13 rows in set (0.00 sec)
	 */
	private function queryOrCreateModule($productID, $userID, $parentid, $fullPathName, $moduleName, $grade)
	{
		$fullPathName = trim($fullPathName);
		$moduleName = trim($moduleName);
		
        $this->logger->debug("enter queryOrCreateModule()");
        $this->logger->debug(sprintf("pro id:%d, user id:%d, parent id:%d, full path:%s, module name:%s, grade:%d", $productID, $userID, $parentid, trim(strtolower($fullPathName)), trim(strtolower($moduleName)), $grade));

        $first = 0;

		BEGIN:
		$sqlCmd = "";
		
        $stmtParams = new StatementParameter();
        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_STRING, strtolower($moduleName));
        $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_INTEGER, $grade);
        $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_STRING, strtolower($fullPathName));
        $stmtParams->addParameter('4', StatementParameterType::$STATEMENT_TYPE_INTEGER, $productID);
		if (isset($parentid))
		{
			$sqlCmd = "select id from bf_product_module where trim(lower(name)) = ? and grade=? and trim(lower(full_path_name))=? and product_id=? and parent_id=? order by id";
            $stmtParams->addParameter('5', StatementParameterType::$STATEMENT_TYPE_INTEGER, $parentid);
		}
		else 
		{
			$sqlCmd = "select id from bf_product_module where trim(lower(name)) = ? and grade=? and trim(lower(full_path_name))=? and product_id=? and parent_id is NULL order by id";
		}

		$moduleID = NULL;
        $this->logger->debug("sqlCmd: $sqlCmd");
        
        $rows = $this->casedb->fetchArray($sqlCmd, $stmtParams);
        $this->logger->debug("rows:\n".print_r($rows, true));
        if (count($rows) > 0)
        {
        	$moduleID = $rows[0]["id"];
            $this->logger->debug("has exists module:$moduleID");
        }
        
        if (!isset($moduleID) && $first == 0)
		{
            $this->logger->debug("insert new module");
            if (isset($parentid) && $parentid > 0)
            {
				$sqlCmd = sprintf("insert into bf_product_module (name, grade, display_order, created_at, created_by, 
					           updated_at, updated_by, full_path_name, product_id, parent_id, 
					           lock_version) value ('%s', %d, %d, '%s', %d, '%s', %d, '%s', %d, %d, %d);",$moduleName, $grade, 0, getCurDateTime(), $userID,
					           getCurDateTime(), $userID, $fullPathName, $productID, $parentid, 1);
            }
            else
            {
                $sqlCmd = sprintf("insert into bf_product_module (name, grade, display_order, created_at, created_by, 
                                   updated_at, updated_by, full_path_name, product_id, parent_id, 
                                   lock_version) value ('%s', %d, %d, '%s', %d, '%s', %d, '%s', %d, NULL, %d);",$moduleName, $grade, 0, getCurDateTime(), $userID,
                                   getCurDateTime(), $userID, $fullPathName, $productID, 1);
            }

			if ($this->casedb->execSql($sqlCmd))
			{           
				$first = 1;
				goto BEGIN;
			}
			else
			{
				$this->logger->error(sprintf("insert data failed: %s", $sqlCmd));
			}
		}
        $this->logger->debug(sprintf("leave with module id [%d]",$moduleID));
		return $moduleID;
	}
	
	/**
	 * 同步一个case
	 * @param unknown_type $proAttriList
	 * @param unknown_type $moduleAttriList
	 * @param unknown_type $oneCase
	 * mysql> describe bf_case_info;
	+------------------+---------------+------+-----+---------+----------------+
	| Field            | Type          | Null | Key | Default | Extra          |
	+------------------+---------------+------+-----+---------+----------------+
	| id               | int(11)       | NO   | PRI | NULL    | auto_increment |
	| created_at       | datetime      | NO   |     | NULL    |                |
	| created_by       | int(11)       | NO   | MUL | NULL    |                |
	| updated_at       | datetime      | NO   |     | NULL    |                |
	| updated_by       | int(11)       | NO   | MUL | NULL    |                |
	| case_status      | varchar(45)   | NO   |     | NULL    |                |
	| assign_to        | int(11)       | YES  | MUL | NULL    |                |
	| title            | varchar(255)  | NO   | MUL | NULL    |                |
	| mail_to          | text          | YES  |     | NULL    |                |
	| case_step        | text          | YES  |     | NULL    |                |
	| lock_version     | smallint(6)   | NO   |     | NULL    |                |
	| related_bug      | varchar(255)  | YES  |     | NULL    |                |
	| related_case     | varchar(255)  | YES  |     | NULL    |                |
	| related_result   | varchar(255)  | YES  |     | NULL    |                |
	| productmodule_id | int(11)       | YES  |     | NULL    |                |
	| modified_by      | text          | NO   |     | NULL    |                |
	| delete_flag      | enum('0','1') | NO   |     | NULL    |                |
	| product_id       | int(11)       | YES  | MUL | NULL    |                |
	| priority         | tinyint(4)    | YES  |     | NULL    |                |
	+------------------+---------------+------+-----+---------+----------------+
	19 rows in set (0.00 sec)
	 */
	private function syncOneCase($proAttriList, $moduleAttriList, &$oneCase)
	{
	    $this->logger->info("pro attr:\n".print_r($proAttriList, true));
        $this->logger->info("module attr:\n".print_r($moduleAttriList, true));
        $this->logger->info("case attr:\n".print_r($oneCase, true));
        
        //set case id
        if (!array_key_exists("id", $oneCase))
        {
        	$this->queryOrCreateCase($proAttriList, $moduleAttriList, $oneCase);
        }
        $this->logger->info("case attr:\n".print_r($oneCase, true));
        
        //根据case id重新获得steps
        if (array_key_exists("id", $oneCase))
        {
        	$this->queryCaseSteps($oneCase);
        }
        
        //如果结果是fail，在bugzilla里开bug，并把bug号存到$oneCase["bugid"]里;
        if (!$this->isPassed($oneCase) && $this->isCreateBug())
        {
        	$this->createBug($proAttriList, $moduleAttriList, $oneCase);
        }
        
        //生成测试结果记录
        $this->createCaseResult($proAttriList, $moduleAttriList, $oneCase);
	}
	
	/**
	 * 根据产品，模块，case的属性查询case，如果case不存在则创建一个.
	 * @param unknown_type $proAttriList
	 * @param unknown_type $moduleAttriList
	 * @param unknown_type $oneCase
	 */
	private function queryOrCreateCase($proAttriList, $moduleAttriList, &$oneCase)
	{
		$this->logger->debug("enter queryOrCreateCase()");
		$this->logger->debug(sprintf("prod id:%d,module id:%d, case title:%s", $proAttriList["id"], $moduleAttriList["id"], trim(strtolower($oneCase["name"]))));

        $first = 0;
		
		BEGIN:
		$sqlCmd = "select id from bf_case_info where product_id=? and productmodule_id=? and trim(lower(title)) = ? ";
		$stmtParams = new StatementParameter();
		$stmtParams->addParameter('proid', StatementParameterType::$STATEMENT_TYPE_INTEGER, $proAttriList["id"]);
		$stmtParams->addParameter('moduleid', StatementParameterType::$STATEMENT_TYPE_INTEGER, $moduleAttriList["id"]);		
		$stmtParams->addParameter('title', StatementParameterType::$STATEMENT_TYPE_STRING, strtolower($oneCase["title"]));
		
		$caseID = NULL;
		$this->logger->debug("sqlCmd: $sqlCmd");
		$rows = $this->casedb->fetchArray($sqlCmd, $stmtParams);
		if (count($rows) > 0)
		{
			$caseID = $rows[0]["id"];
            $this->logger->debug("exist case: $caseID");
		}
				
		if (!isset($caseID) && $first == 0)
		{
            $this->logger->debug("insert new case");

			$sqlCmd = sprintf("insert into bf_case_info (created_at, created_by, updated_at, updated_by, case_status, assign_to, title, mail_to,case_step, lock_version, related_bug, related_case, related_result,productmodule_id,modified_by,delete_flag,product_id,priority) values ('%s', %d, '%s', %d, 'Active',%d,'%s',NULL,'%s',1,NULL,NULL,NULL,%d,'%d',0,%d,1);",
			                   getCurDateTime(), $proAttriList['userid'], getCurDateTime(), $proAttriList['userid'], $proAttriList['userid'], $this->casedb->escape($oneCase['title']),
					           $this->casedb->escape($oneCase['steps']), $moduleAttriList['id'],$proAttriList['userid'], $proAttriList["id"]);
		
			if ($this->casedb->execSql($sqlCmd))
			{
                $this->logger->debug("insert case successfully.");
				$first = 1;
				goto BEGIN;
			}
			else
			{
				$this->logger->error(sprintf("insert data failed:\n%s", $sqlCmd));
			}
		}
		$this->logger->debug(sprintf("leave with case id [%d]",$caseID));
		
		//如果是新插入的case，则需要在bf_ettoncase_xx表里加上关联关系
		if (isset($caseID) && $first == 1)
		{
			$sqlCmd = sprintf("insert into bf_ettoncase_%d (case_id, Automated) values (%d, '是');", $proAttriList["id"], $caseID);
			if (!$this->casedb->execSql($sqlCmd))
			{
				$this->logger->error(sprintf("insert data failed:\n%s", $sqlCmd));
			}
		}
		
		$oneCase["id"] = $caseID;
	}
	
	/**
	 * 根据case id查询case的步骤
	 * @param unknown_type $oneCase
	 */
	private function queryCaseSteps(&$oneCase)
	{
		$sqlCmd = "select case_step from bf_case_info where id = ? ";
		$stmtParams = new StatementParameter();
		$stmtParams->addParameter('id', StatementParameterType::$STATEMENT_TYPE_INTEGER, $oneCase["id"]);
		
		$this->logger->debug("sqlCmd: $sqlCmd");
		$rows = $this->casedb->fetchArray($sqlCmd, $stmtParams);
		if (count($rows) == 1)
		{
			$oneCase["steps"] = $rows[0]["case_step"];
		}
	}
	
	/**
	 * 创建结果记录
	 * mysql> describe bf_result_info;
	+------------------+--------------+------+-----+---------+----------------+
	| Field            | Type         | Null | Key | Default | Extra          |
	+------------------+--------------+------+-----+---------+----------------+
	| id               | int(11)      | NO   | PRI | NULL    | auto_increment |
	| created_at       | datetime     | NO   |     | NULL    |                |
	| created_by       | int(11)      | NO   | MUL | NULL    |                |
	| updated_at       | datetime     | NO   |     | NULL    |                |
	| updated_by       | int(11)      | NO   | MUL | NULL    |                |
	| result_status    | varchar(45)  | NO   |     | NULL    |                |
	| assign_to        | int(11)      | YES  |     | NULL    |                |
	| result_value     | varchar(45)  | NO   |     | NULL    |                |
	| mail_to          | text         | YES  |     | NULL    |                |
	| result_step      | text         | YES  |     | NULL    |                |
	| lock_version     | smallint(6)  | NO   |     | NULL    |                |
	| related_bug      | varchar(255) | YES  |     | NULL    |                |
	| productmodule_id | int(11)      | YES  |     | NULL    |                |
	| modified_by      | text         | NO   |     | NULL    |                |
	| title            | varchar(255) | NO   |     | NULL    |                |
	| related_case_id  | int(11)      | NO   | MUL | NULL    |                |
	| product_id       | int(11)      | YES  | MUL | NULL    |                |
	+------------------+--------------+------+-----+---------+----------------+
	17 rows in set (0.00 sec)

	 * @param unknown_type $proAttriList
	 * @param unknown_type $moduleAttriList
	 * @param unknown_type $oneCase
	 */
	private function createCaseResult($proAttriList, $moduleAttriList, &$oneCase)
	{
		$this->logger->debug("enter createCaseResult()");
		
		$caseResult = "Failed";		
		if ($this->isPassed($oneCase))
		{
			$caseResult = "Passed";
		}
        $this->logger->debug("result: $caseResult");

        $caseSteps = $this->casedb->escape($oneCase["steps"]);
        if (array_key_exists("reason",$oneCase))
        {
        	$caseSteps .= $this->casedb->escape("<br />[实际结果]<br />".$oneCase["reason"]);
        }
        
		//insert data into bf_result_info
		$sqlCmd = sprintf("insert into bf_result_info (created_at, created_by, updated_at, updated_by, result_status,assign_to, result_value, mail_to, result_step, lock_version,related_bug, productmodule_id, modified_by, title, related_case_id, product_id) values ('%s',%d,'%s',%d,'Completed',%d,'%s', NULL,'%s',1,'%s',%d,'%d','%s',%d,%d);",
				getCurDateTime(), $proAttriList['userid'], getCurDateTime(), $proAttriList['userid'],
				$proAttriList['userid'],$caseResult,$caseSteps,
				$this->casedb->escape($oneCase["bugid"]),$moduleAttriList['id'],$proAttriList['userid'], $this->casedb->escape($oneCase["title"]), $oneCase["id"], $proAttriList["id"]);
		$this->logger->debug("sqlCmd:\n $sqlCmd");

		if (!$this->casedb->execSql($sqlCmd))
		{
			$this->logger->error(sprintf("insert data failed:\n%s", $sqlCmd));
            return;
		}

        //select new result id
        $sqlCmd = "select max(id) as id from bf_result_info where related_case_id=? and product_id=? and productmodule_id=?;";
        $stmtParams = new StatementParameter();
        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $oneCase["id"]);
        $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_INTEGER, $proAttriList["id"]);
        $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_INTEGER, $moduleAttriList['id']);
        $rows = $this->casedb->fetchArray($sqlCmd, $stmtParams);
        if (count($rows) == 1)
        {
            $oneCase["resultid"] = $rows[0]["id"];
        }
        else
        {
            $this->logger->error("can't find result id");
            return;
        }

        //insert result tag
        /**
        mysql> describe bf_ettonresult_12;
        +-----------+--------------+------+-----+---------+----------------+
        | Field     | Type         | Null | Key | Default | Extra          |
        +-----------+--------------+------+-----+---------+----------------+
        | id        | int(11)      | NO   | PRI | NULL    | auto_increment |
        | result_id | int(11)      | NO   | MUL | NULL    |                |
        | exectag   | varchar(255) | YES  |     | NULL    |                |
        +-----------+--------------+------+-----+---------+----------------+
        3 rows in set (0.00 sec)
        */
        $execTag = "";

        if (array_key_exists("executeid", $this->envSetting))
        {
            $execTag = $this->envSetting["executeid"];
        }
        else
        {
            if (array_key_exists("build", $this->envSetting))
            {
                $execTag = $this->envSetting["build"]."-";
            }

            $execTag .= sprintf("[%s]",$this->envSetting["executetime"]);
            $this->envSetting["executeid"] = $execTag;
        }
        
        $sqlCmd = sprintf("insert into bf_ettonresult_%d (result_id, exectag) values (%d, '%s');", $proAttriList["id"], $oneCase["resultid"], $execTag);
        if ($this->casedb->execSql($sqlCmd))
        {
            $this->logger->info("insert exectag successfully");
        }
        else
        {
            $this->logger->error(sprintf("insert exectag failed: %s", $sqlCmd));
        }
	}
	
	/**
	 * 判断一个用例是pass还是fail，pass返回1，非pass返回0
	 * @param unknown_type $oneCase
	 */
	private function isPassed(&$oneCase)
	{
		$this->logger->debug("enter isPassed()");
	
		if (array_key_exists("result",$oneCase))
		{
			$result = trim(strtolower($oneCase["result"]));
			$pos = strpos($result, "pass");
			if (!($pos===false))
			{
				return 1;
			}
		}
		return 0;
	}
	
    /**
     *判断是否自动开bug
     */
    private function isCreateBug()
    {
        if (isset($this->envSetting) && array_key_exists("createbug", $this->envSetting))
        {
            if (trim(strtolower($this->envSetting["createbug"])) == "true" || 
                trim(strtolower($this->envSetting["createbug"])) == "yes" || 
                trim($this->envSetting["createbug"]) == "1")
            {
                return 1;
            }
        }

        return 0;
    }

	/**
	 * 如果有<bug-product>，则以它的值作为bugzilla库里的产品名字，如果没有则以<product>下的name属性值为产品名字
	 * @param unknown_type $proAttriList
	 */
	private function getBugProductName($proAttriList)
	{
		if (array_key_exists("bug-product", $proAttriList))
		{
			return $proAttriList["bug-product"];
		}
		
		if (array_key_exists("name", $proAttriList))
		{
			return $proAttriList["name"];
		}
		
		return null;
	}
	
	/**
	 * 如果有<bug-component>，则以它的值作为bugzilla库里的产品名字，如果没有则以<module>下的name属性值为产品名字
	 * @param unknown_type $proAttriList
	 */
	private function getBugComponentName($moduleAttriList)
	{
		if (array_key_exists("bug-component", $moduleAttriList))
		{
			return $moduleAttriList["bug-component"];
		}
		
		if (!array_key_exists("name", $moduleAttriList))
	    {
	    	$this->logger->error("can't find component name, so has no way to get component id from bugzilla.");
	    	return null;
	    }
	    $modules = explode("/", $moduleAttriList["name"]);
	    for ($i=0; $i<count($modules); $i++)
	    {
		    if (strlen($modules[$i]) == 0)
		    	continue;
		    return $modules[$i];
	    }
	
		return null;
	}
	
	/**
	 * 查找reporter name
	 * @param unknown_type $proAttriList
	 */
	private function getBugReportorName($proAttriList)
	{
		if (array_key_exists("bug-reporter", $proAttriList))
		{
			return $proAttriList["bug-reporter"];
		}
		else if (array_key_exists("username", $proAttriList))
		{
			return $proAttriList["username"];
		}
		else if (isset($this->envSetting) && array_key_exists("username", $this->envSetting))
		{
			return $this->envSetting["username"];
		}
		
		return null;
	}
	
	/**
	 * 查找bug的assign to name
	 * @param unknown_type $proAttriList
	 */
	private function getBugAssigntoName($proAttriList)
	{	
		if (array_key_exists("bug-assignto", $proAttriList))
		{
			return $proAttriList["bug-assignto"];
		}
		else if (array_key_exists("username", $proAttriList))
		{
			return $proAttriList["username"];
		}
		else if (isset($this->envSetting) && array_key_exists("username", $this->envSetting))
		{
			return $this->envSetting["username"];
		}
	
		return null;
	}
	
	/**
	 * 查找bug的版本，如果没设置则取数据库里的最大版本号
	 * @param unknown_type $proAttriList
	 * @param unknown_type $productID
	 */
	private function getBugVersion($proAttriList, $productID)
	{
		if (array_key_exists("bug-version", $proAttriList))
		{
			return $proAttriList["bug-version"];
		}
		/*
	    else if (array_key_exists("version", $proAttriList))
	    {
	    	return $proAttriList["version"];
	    }
	    else if (isset($this->envSetting) && array_key_exists("version", $this->envSetting))
	    {
	    	return $this->envSetting["version"];
	    }
		*/
		//如果没设置bug-version,则直接查找最大的bug号
		$sqlCmd = "select value from versions where product_id=? order by id desc limit 1;";
		$stmtParams = new StatementParameter();
	    $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $productID);
	    $rows = $this->bugdb->fetchArray($sqlCmd, $stmtParams);
	    if (count($rows) == 1)
	    {
	    	return $rows[0]["value"];
	    }
	    
		return "unspecified";
	}
	
	/**
	 * 在bugzilla里开个bug
	 * @param unknown_type $proAttriList
	 * @param unknown_type $moduleAttriList
	 * @param unknown_type $oneCase
	 */
	private function createBug($proAttriList, $moduleAttriList, &$oneCase)
	{
	    $oneCase["bugid"] = "";
		//首先获得产品ID	   
	    $productID = $this->getBugProductIDbyName($this->getBugProductName($proAttriList));
	    if (!isset($productID))
	    {
	    	$this->logger->error("can't find product id from bugzilla");
	    	return;
	    }
	    
	    //获得component名称，因为bugfree可以有任意多级的component，所以只取第一级的模块名来查询component 
        $componentName = $this->getBugComponentName($moduleAttriList);
	    $moduleID = $this->getBugComponentIDbyName($productID, $componentName);
	    if (!isset($moduleID))
	    {
	    	$this->logger->error(sprintf("can't find component id by name[%s] and product id[%d] from bugzilla.", $componentName, $productID));
	    	return;
	    }
	    
	    //获得reporter id
	    $reporter = $this->getBugUserIDbyName($this->getBugReportorName($proAttriList));	    
	    if (!isset($reporter))
	    {
	    	$this->logger->error(sprintf("can't find user id from bugzilla."));
	    	return;
	    }
	    
	    //获得assign to id
	    $assignto = $this->getBugUserIDbyName($this->getBugAssigntoName($proAttriList));
	    if (!isset($assignto))
	    {
            //从component设置里去找owner
            $sqlCmd = "select initialowner as id from components where product_id=? and trim(lower(name))=?;";
            $stmtParams = new StatementParameter();
            $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $productID);
            $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, trim(strtolower($componentName)));
            $rows = $this->bugdb->fetchArray($sqlCmd, $stmtParams);
            if (count($rows)==1)
            {
                $assignto = $rows[0]["id"];
            }
            else
            {
                $assignto = $reporter;
            }
	    }
	    $this->logger->info("assignto:".$assignto);
	    
	    //查找版本号
	    $version = $this->getBugVersion($proAttriList, $productID);
	    
        //查找是否有存在的bug还没关
        $sqlCmd = sprintf("select bug_id as id, bug_status from bugs where product_id=? and component_id=? and (trim(lower(bug_status))='new' or trim(lower(bug_status))='assigned' or trim(lower(bug_status))='reopened') and lower(short_desc) like '%%]%s' and version='%s';", trim(strtolower($oneCase["title"])), $version);
        $stmtParams = new StatementParameter();
        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $productID);
        $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_INTEGER, $moduleID);
        $this->logger->info(sprintf("pro id:%d, module id:%d, sqlcmd:%s", $productID, $moduleID, $sqlCmd));
        $rows = $this->bugdb->fetchArray($sqlCmd, $stmtParams);
        if (count($rows) >= 1)
        {
            $oneCase["bugid"] = $rows[0]["id"];
            $this->logger->info("find exist bug ".$oneCase["bugid"]);
            goto INSERT_COMMENTS;
        }
	    
	    //开始插入一个新bug
	    /**
	     * mysql> describe bugs;
		+---------------------+--------------+------+-----+---------+----------------+
		| Field               | Type         | Null | Key | Default | Extra          |
		+---------------------+--------------+------+-----+---------+----------------+
		| bug_id              | mediumint(9) | NO   | PRI | NULL    | auto_increment |
		| assigned_to         | mediumint(9) | NO   | MUL | NULL    |                |
		| bug_file_loc        | mediumtext   | YES  |     | NULL    |                |
		| bug_severity        | varchar(64)  | NO   | MUL | NULL    |                |
		| bug_status          | varchar(64)  | NO   | MUL | NULL    |                |
		| creation_ts         | datetime     | YES  | MUL | NULL    |                |
		| delta_ts            | datetime     | NO   | MUL | NULL    |                |
		| short_desc          | varchar(255) | NO   |     | NULL    |                |
		| op_sys              | varchar(64)  | NO   | MUL | NULL    |                |
		| priority            | varchar(64)  | NO   | MUL | NULL    |                |
		| product_id          | smallint(6)  | NO   | MUL | NULL    |                |
		| rep_platform        | varchar(64)  | NO   |     | NULL    |                |
		| reporter            | mediumint(9) | NO   | MUL | NULL    |                |
		| version             | varchar(64)  | NO   | MUL | NULL    |                |
		| component_id        | smallint(6)  | NO   | MUL | NULL    |                |
		| resolution          | varchar(64)  | NO   | MUL |         |                |
		| target_milestone    | varchar(20)  | NO   | MUL | ---     |                |
		| qa_contact          | mediumint(9) | YES  | MUL | NULL    |                |
		| status_whiteboard   | mediumtext   | NO   |     | NULL    |                |
		| votes               | mediumint(9) | NO   | MUL | 0       |                |
		| keywords            | mediumtext   | NO   |     | NULL    |                |
		| lastdiffed          | datetime     | YES  |     | NULL    |                |
		| everconfirmed       | tinyint(4)   | NO   |     | NULL    |                |
		| reporter_accessible | tinyint(4)   | NO   |     | 1       |                |
		| cclist_accessible   | tinyint(4)   | NO   |     | 1       |                |
		| estimated_time      | decimal(7,2) | NO   |     | 0.00    |                |
		| remaining_time      | decimal(7,2) | NO   |     | 0.00    |                |
		| deadline            | datetime     | YES  |     | NULL    |                |
		| alias               | varchar(20)  | YES  | UNI | NULL    |                |
		| cf_type             | varchar(64)  | NO   |     | ---     |                |
		| cf_bugfrom          | varchar(64)  | NO   |     | ---     |                |
		| cf_devicetype       | varchar(255) | YES  |     | NULL    |                |
		| cf_occurrence       | varchar(255) | YES  |     | NULL    |                |
		| cf_buildnum         | varchar(255) | YES  |     | NULL    |                |
		| cf_osversion        | varchar(64)  | NO   |     | ---     |                |
		| cf_kouxinversion    | varchar(64)  | NO   |     | ---     |                |
		| cf_checkinversion   | varchar(255) | YES  |     | NULL    |                |
		+---------------------+--------------+------+-----+---------+----------------+
		37 rows in set (0.00 sec)
	     */
        $bugTitle = "";
        if (isset($this->envSetting) && array_key_exists("build", $this->envSetting))
        {
            $bugTitle = sprintf("[%s]", $this->envSetting["build"]);
        }
        $bugTitle .= sprintf("[%s]%s", $componentName, $oneCase["title"]);


	    
	    if ($this->configs["bugzilla"]["database"] == "bugs36")
	    {
	    	$sqlCmd = sprintf("insert into bugs (assigned_to, bug_file_loc, bug_severity, bug_status, creation_ts, delta_ts, ".
	    			"short_desc, op_sys, priority, product_id, rep_platform,".
	    			"reporter, version, component_id, resolution, target_milestone, ".
	    			"qa_contact, status_whiteboard, votes, keywords, lastdiffed, ".
	    			"everconfirmed, reporter_accessible, cclist_accessible, estimated_time, remaining_time, ".
	    			"deadline, alias, cf_type, cf_bugfrom,cf_osversion,cf_kouxinversion) values (%d, '', 'normal', 'NEW', '%s', '%s',".
	    			"'%s','Windows','Normal',%d,'PC',".
	    			"%d, '%s', %d, '', '---',".
	    			"NULL, '', 0, '', '%s', 1, 1, 1,0,0,".
	    			"NULL, NULL, '', '','','');", $assignto, getCurDateTime(), getCurDateTime(), $bugTitle, $productID, $reporter, $version, $moduleID, getCurDateTime());
	    }
	    else//bugs1库
	    {
	    	$sqlCmd = sprintf("insert into bugs (assigned_to, bug_file_loc, bug_severity, bug_status, creation_ts, delta_ts, ".
	    		          "short_desc, op_sys, priority, product_id, rep_platform,".
	    		          "reporter, version, component_id, resolution, target_milestone, ".
	    		          "qa_contact, status_whiteboard, votes, keywords, lastdiffed, ".
	    		          "everconfirmed, reporter_accessible, cclist_accessible, estimated_time, remaining_time, ".
	    		          "deadline, alias) values (%d, '', 'normal', 'NEW', '%s', '%s',".
	    		          "'%s','Windows','Normal',%d,'PC',".
	    		          "%d, '%s', %d, '', '---',".
	    		          "NULL, '', 0, '', '%s', 1, 1, 1,0,0,".
	    		          "NULL, NULL);", $assignto, getCurDateTime(), getCurDateTime(), $bugTitle, $productID, $reporter, $version, $moduleID, getCurDateTime());
	    }   
        $this->logger->debug("sqlCmd:\n $sqlCmd");
	    if (!$this->bugdb->execSql($sqlCmd))
	    {
	    	$this->logger->error(sprintf("insert bug failed:\n%s", $sqlCmd));
	    	return;
	    }
	
	    //select new result id        
	    $sqlCmd = "select max(bug_id) as id from bugs where product_id=? and component_id=? and assigned_to=? and reporter=?;";
	    $stmtParams = new StatementParameter();
	    $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $productID);
	    $stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_INTEGER, $moduleID);
	    $stmtParams->addParameter('3', StatementParameterType::$STATEMENT_TYPE_INTEGER, $assignto);
	    $stmtParams->addParameter('4', StatementParameterType::$STATEMENT_TYPE_INTEGER, $reporter);
	    $rows = $this->bugdb->fetchArray($sqlCmd, $stmtParams);
	    if (count($rows) == 1)
	    {
	    	$oneCase["bugurl"] = "";
	    	$dbName = $this->configs["bugzilla"]["database"];
	    	$bugUrl = $this->configs[$dbName]["bugurl"];
	    	if (isset($bugUrl))
	    	{
	    		$oneCase["bugurl"] = $bugUrl;
	    	}
	    	$oneCase["bugid"] = $rows[0]["id"];
	    	$oneCase["bugurl"] .= $rows[0]["id"];
	    }
	    else
	    {
	    	$this->logger->error("can't find bug id");
	    	return;
	    }
        
        INSERT_COMMENTS:

        $oneCase["bugurl"] = "";
        $dbName = $this->configs["bugzilla"]["database"];
        $bugUrl = $this->configs[$dbName]["bugurl"];
        if (isset($bugUrl))
        {
            $oneCase["bugurl"] = $bugUrl.$oneCase["bugid"];
        }

	    //插入comments
        /**
        mysql> describe longdescs;
        +-----------------+--------------+------+-----+---------+----------------+
        | Field           | Type         | Null | Key | Default | Extra          |
        +-----------------+--------------+------+-----+---------+----------------+
        | comment_id      | mediumint(9) | NO   | PRI | NULL    | auto_increment |
        | bug_id          | mediumint(9) | NO   | MUL | NULL    |                |
        | who             | mediumint(9) | NO   | MUL | NULL    |                |
        | bug_when        | datetime     | NO   | MUL | NULL    |                |
        | work_time       | decimal(7,2) | NO   |     | 0.00    |                |
        | thetext         | mediumtext   | NO   |     | NULL    |                |
        | isprivate       | tinyint(4)   | NO   |     | 0       |                |
        | already_wrapped | tinyint(4)   | NO   |     | 0       |                |
        | type            | smallint(6)  | NO   |     | 0       |                |
        | extra_data      | varchar(255) | YES  |     | NULL    |                |
        +-----------------+--------------+------+-----+---------+----------------+
        10 rows in set (0.00 sec)
        */
        $caseSteps = $this->bugdb->escape($oneCase["steps"]);
        if (array_key_exists("reason",$oneCase))
        { 
            $caseSteps .= $this->bugdb->escape("\r\n[实际结果]\r\n".$oneCase["reason"]);
        }
        $caseSteps = str_replace("<br />","\r\n",$caseSteps);
        $caseSteps = str_replace("<br/>","\r\n",$caseSteps);
        $caseSteps = str_replace("<br>","\r\n",$caseSteps);

        $sqlCmd = sprintf("insert into longdescs (bug_id, who, bug_when, work_time, thetext, isprivate, already_wrapped, type, extra_data) values(%d,%d,'%s',0,'%s',0,0,0,NULL)",
                           $oneCase["bugid"], $reporter, getCurDateTime(), $caseSteps);
        if (!$this->bugdb->execSql($sqlCmd))
        {
            $this->logger->error(sprintf("insert one comments failed:\n%s", $sqlCmd));
            return;
        }

        //把所有comments加起来放到bugs_fulltext里;
        $sqlCmd = "select thetext from longdescs where bug_id=? order by comment_id;";
        $stmtParams = new StatementParameter();
        $stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $oneCase["bugid"]);
        $rows = $this->bugdb->fetchArray($sqlCmd, $stmtParams);
        $fullComments = "";
        for ($i=0; $i<count($rows); $i++)
        {
            $fullComments .= $rows[$i]["thetext"];
        }
        if (strlen($fullComments) == 0)
        {
            $this->logger->error("full comments is empty.");
            return;
        }

        //
	    /**
	     * mysql> describe bugs_fulltext;
        +--------------------+--------------+------+-----+---------+-------+
        | Field              | Type         | Null | Key | Default | Extra |
        +--------------------+--------------+------+-----+---------+-------+
        | bug_id             | mediumint(9) | NO   | PRI | NULL    |       |
        | short_desc         | varchar(255) | NO   | MUL | NULL    |       |
        | comments           | mediumtext   | YES  | MUL | NULL    |       |
        | comments_noprivate | mediumtext   | YES  | MUL | NULL    |       |
        +--------------------+--------------+------+-----+---------+-------+
        4 rows in set (0.01 sec)
	    */
	    //delete fulltest
        $sqlCmd = sprintf("delete from bugs_fulltext where bug_id=%d;", $oneCase["bugid"]);
	    $this->bugdb->execSql($sqlCmd);

	    $sqlCmd = sprintf("insert into bugs_fulltext (bug_id, short_desc, comments, comments_noprivate) values(%d, '%s', '%s','%s');", 
                          $oneCase["bugid"], $oneCase["title"], $fullComments, $fullComments);
        
	    if (!$this->bugdb->execSql($sqlCmd))
	    {
	    	$this->logger->error(sprintf("insert comments failed:\n%s", $sqlCmd));
	    	return;
	    }
	    
	    $this->logger->info(sprintf("leave createBug(), with bug id[%s].", $oneCase["bugid"]));
	}
	
	/**
	 * 得到bugzilla库中产品的ID
	 * @param unknown_type $proName
	 */
	private function getBugProductIDbyName($proName)
	{
		$proID = null;
		$this->logger->debug("enter getBugProductIDbyName()");
		
		$stmtParams = new StatementParameter();
		$stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_STRING, trim(strtolower($proName)));
		
		$sqlCmd = "select id from products where trim(lower(name)) = ?;";
		$rows = $this->bugdb->fetchArray($sqlCmd, $stmtParams);
		if (count($rows) > 0)
		{
			$proID = $rows[0]["id"];
		}
		else
		{
			$this->logger->error("can't find product id by name [$proName]");
		}
		
		$this->logger->debug("leave getBugProductIDbyName(), with [$proID]");
		return $proID;
	}
	
	/**
	 * 得到bugzilla库中component的id
	 * @param unknown_type $proID
	 * @param unknown_type $moduleName
	 */
	private function getBugComponentIDbyName($proID, $moduleName)
	{
		$moduleID = null;
		
		$this->logger->debug("enter getBugComponentIDbyName(), proid=$proID, modulename=$moduleName");
		
		$stmtParams = new StatementParameter();
		$stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_INTEGER, $proID);
		$stmtParams->addParameter('2', StatementParameterType::$STATEMENT_TYPE_STRING, trim(strtolower($moduleName)));		
		
		$sqlCmd = "select id from components where product_id=? and trim(lower(name)) = ?;";
		$rows = $this->bugdb->fetchArray($sqlCmd, $stmtParams);
		if (count($rows) > 0)
		{
			$moduleID = $rows[0]["id"];
		}
		else
		{
			$this->logger->error("can't find component id");
		}
		
		$this->logger->debug("leave getBugComponentIDbyName(), with [$moduleID]");
		return $moduleID;
	}
	
	/**
	 * 根据bugzilla的登录名获得用户id
	 * @param unknown_type $userName
	 */
	private function getBugUserIDbyName($userName)
	{
		$userID = null;
		
		$this->logger->debug("enter getBugUserIDbyName()");
		
		$userName .= $this->qihooMail;
		$stmtParams = new StatementParameter();		
		$stmtParams->addParameter('1', StatementParameterType::$STATEMENT_TYPE_STRING, trim(strtolower($userName)));
		
		$sqlCmd = "select userid from profiles where trim(lower(login_name)) = ?;";
		$rows = $this->bugdb->fetchArray($sqlCmd, $stmtParams);
		if (count($rows) > 0)
		{
			$moduleID = $rows[0]["userid"];
		}
		else
		{
			$this->logger->error("can't find conponent id");
		}
		
		$this->logger->debug("leave getBugUserIDbyName(), with [$moduleID]");
		return $moduleID;
	}
}

?>

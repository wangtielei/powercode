<?php
/**
 * xmlparser用于把xml解析为数组;
 * @var unknown_type
 */
define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/");
require_once(LOG4PHP_DIR.'/Logger.php');
require_once('util.php');

class XmlParser
{
	//定义常量
	const ROOT_FLAG = "testresult";
	const MAIL_FLAG = "mail";
	const ENVIRONMENT_FLAG = "environment";
	const PRODUCT_FLAG = "product";
	const MODULE_FLAG = "module";
	const CASE_FLAG = "case";
	
	//logger对象
	private $logger = NULL;
	
	//DOM
	private $xmlDom = NULL;
	
	//保存解析后的产品数据数组
	private $productResult = array();
	
	//保存解析后的mail数据数组
	private $mailSetting = array();
	
	//保存解析后的环境数据数组
	private $envSetting = array();
	
	public function __construct()
	{
		$this->logger = Logger::getLogger(__CLASS__);
		
		$this->xmlDom = new DOMDocument('1.0', 'UTF-8');
	}
	
	/**
	 * 解析xml结果数据
	 * 解析成功返回1，否则返回0
	 */
	public function parseXml($xmlData)
	{
        $this->logger->debug("Enter parseXml()");

		if (!$this->xmlDom->loadXML($xmlData))
		{
			$this->logger->error("Parse xml failed.");
			return 0;
		}
		
		//find root
		$rootNodeList = $this->xmlDom->getElementsByTagName(self::ROOT_FLAG);		
		if (!isset($rootNodeList) || $rootNodeList->length ==0)
		{
			$this->logger->error(sprintf("can't find root node '%s'", self::ROOT_FLAG));
			return 0;
		}
		$rootNode = $rootNodeList->item(0);
		
        $this->logger->debug("step2, type:".$rootNode->nodeType.",name:".$rootNode->nodeName);

		//遍历所有子节点
        $childList = $rootNode->childNodes;
        $this->logger->debug("step3, child count: ".$childList->length);

		foreach ($childList as $node)
		{
			if (!$this->isLeafNode($node))
			{
                $this->logger->debug("cur item name: ".$node->nodeName);
				if (strtolower($node->nodeName) == self::MAIL_FLAG)
				{
					$this->parseNode($node, $this->mailSetting);
                    $this->logger->debug("mail settings:\n".print_r($this->mailSetting, true));
				}
				else if (strtolower($node->nodeName) == self::ENVIRONMENT_FLAG)
				{
					$this->parseNode($node, $this->envSetting);
                    if (!array_key_exists("executeid",$this->envSetting) && !array_key_exists("executetime",$this->envSetting))
                    {
                    	$this->envSetting["executetime"] = getCurDateTimeString();
                    }
					
					$this->logger->debug("env settings:\n".print_r($this->envSetting, true));                    
				}
				else if (strtolower($node->nodeName) == self::PRODUCT_FLAG)
				{
					array_push($this->productResult, $this->parseProduct($node));
                    $this->logger->debug("products:\n".print_r($this->productResult,true));
				}
			}
            $this->logger->debug("next item");
		}
		
        $this->logger->debug("Leave parseXml()");
		return 1;
	}
	
	/**
	 * 判断一个节点是否是叶子节点
	 * @param unknown_type $node
	 */
	private function isLeafNode($node)
    {
		if ($node->nodeType == XML_TEXT_NODE || $node->nodeType == XML_COMMENT_NODE)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * 返回叶子节点的值
	 * @param unknown_type $rootNode
	 */
	private function getNodeValue($rootNode)
	{
		$this->logger->debug("enter getNodeValue()");
		
		$childList = $rootNode->childNodes;
		foreach ($childList as $node)
		{
			if ($node->nodeType == XML_TEXT_NODE)
			{
                $this->logger->debug("node name: ".$node->name);
				return xmlDecode($node->nodeValue);
			}
		}
		
		return "";
	}
	
    private function parseAttributes($rootNode, &$result)
    {
        $this->logger->debug("enter parseAttributes()");
        //DOMAttr class list
        $attriList = $rootNode->attributes;

        foreach ($attriList as $attriNode)
        {
            $key = strtolower($attriNode->name);
            $value = $attriNode->value;

            $this->logger->debug("attri: $key = $value");
            $result[$key] = xmlDecode($value);
        }
        $this->logger->debug("leave parseAttributes()");
    }

	/**
	 * 解析一个节点的孩子，不会递归，仅解析到孩子级,并把结果以键值对的方式保存到$result
	 * 如果节点的孩子有和属性同名，则孩子的值覆盖属性值
	 * @param unknown_type $rootNode
	 * @param unknown_type $result
	 */
	private function parseNode($rootNode, &$result)
	{
		$this->logger->debug("enter parseNode()");
		//首先解析属性
		$this->parseAttributes($rootNode, $result);

		$childList = $rootNode->childNodes;
		foreach ($childList as $node)
		{
            if ($this->isLeafNode($node))
                continue;
            $key = trim(strtolower($node->nodeName));
            $value = trim($this->getNodeValue($node));
			$this->logger->debug(sprintf("leaf node, type:%s : $key = $value", $node->nodeType));
			$result[$key] = $value;
		}
		$this->logger->debug("leave parseNode()");
	}
	
	/**
	 * 解析product节点，函数里会递归的解析下去,并将该产品节点打包为一个数组
	 * product节点的属性以键值对方式存入数组，product节点下的模块节点直接放入数组，
	 * 所以在访问该产品时，请判断数组中每个item的类型，如果是array则表明该成员是模块，
	 * 如果是字符串，则表明是属性.
	 * @param unknown_type $rootNode
	 * @return multitype:
	 */
	private function parseProduct($rootNode)
	{
        $this->logger->debug("enter parseProduct()");

		$productData = array();
		
		//首先解析属性
        $this->parseAttributes($rootNode, $productData);
		
		//解析孩子节点
		//遍历所有子节点
		$childList = $rootNode->childNodes;
		foreach ($childList as $node)
		{
			if (!$this->isLeafNode($node))
			{
				if (strtolower($node->nodeName) == self::MODULE_FLAG)
				{
					array_push($productData, $this->parseModule($node));
				}
				else
				{
					$key = trim(strtolower($node->nodeName));
					$value = trim($this->getNodeValue($node));
					$this->logger->debug(sprintf("node, type:%s : $key = $value", $node->nodeType));
					$productData[$key] = $value;
				}
			}
		}
		
        $this->logger->debug("leave parseProduct()");
		return $productData;	
	}
	
	/**
	 * 解析一个模块节点
	 * @param unknown_type $rootNode
	 */
	private function parseModule($rootNode)
	{
        $this->logger->debug("enter parseModule()");

		$moduleData = array();
		
		//首先解析属性
		$this->parseAttributes($rootNode, $moduleData);

        //解析孩子节点
		//遍历所有子节点
		$childList = $rootNode->childNodes;
		foreach ($childList as $node)
		{
			if (!$this->isLeafNode($node))
			{
				if (strtolower($node->nodeName) == self::CASE_FLAG)
				{
					array_push($moduleData, $this->parseCase($node));
				}
				else
				{
					$key = trim(strtolower($node->nodeName));
					$value = trim($this->getNodeValue($node));
					$this->logger->debug(sprintf("node, type:%s : $key = $value", $node->nodeType));
					$moduleData[$key] = $value;
				}
			}
		}
	    
        $this->logger->debug("leave parseModule()");

		return $moduleData;
	}
	
	/**
	 * 解析一个case节点
	 * @param unknown_type $rootNode
	 */
	private function parseCase($rootNode)
	{
        $this->logger->debug("enter parseCase()");

		$caseData = array();
		
		$this->parseNode($rootNode, $caseData);
		
        $this->logger->debug("leave parseCase()");

        return $caseData;
	}
	
	/**
	 * 返回解析后的测试结果
	 */
	public function getProductResult()
	{
		return $this->productResult;
	}
	
	/**
	 * 返回邮件设置数组
	 */
	public function getMailSetting()
	{
		return $this->mailSetting;
	}
	
	/**
	 * 返回环境设置数组
	 */
	public function getEnvSetting()
	{
		return $this->envSetting;
	}
}

?>

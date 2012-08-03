<?php

/**
 * report用于把数组转化为xml的报表
 * @var unknown_type
 */
define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/");
require_once(LOG4PHP_DIR.'/Logger.php');
require_once('util.php');

class Reportor
{
	//logger对象
	private $logger = NULL;
	
	//保存环境设置
	private $envSetting = NULL;
	
	//保存解析后的数据数组
	private $productResult = NULL;
	
	//保存报告字符串
	private $testReport = "";
	
	//保存总case数量
	private $totalCount = 0;
	private $passCount = 0;
	private $failCount = 0;
	private $skipCount = 0;
	private $passRate = 0;
	
	public function __construct($env, $result)
	{
		$this->logger = Logger::getLogger(__CLASS__);
		$this->productResult = $result;
		$this->envSetting = $env;
	}
	
	/**
	 * 同步测试结果到bugfree
	 */
	public function getReport()
	{
        $this->logger->debug("enrer getReport()");
		$this->calcCaseCount();
        $this->logger->debug(print_r($this->productResult, true));
		$this->generateHtml();
        //$this->logger->debug("leave getReport(), with:\n".$this->testReport);
		return $this->testReport;
	}
	
	/**
	 * 根据case结果计算模块，产品和所有case的成功，失败信息
	 */
	private function calcCaseCount()
	{
		//循环每个产品
		foreach ($this->productResult as &$oneProduct)
		{
			$this->calcProduct($oneProduct);
			
			$this->totalCount += $oneProduct["totalcount"];
			$this->passCount += $oneProduct["passcount"];
			$this->failCount += $oneProduct["failcount"];
			$this->skipCount += $oneProduct["skipcount"];
		}
		
		if ($this->totalCount > 0)
		{
			$this->passRate = $this->passCount*100/$this->totalCount;
		}
	}
	
	/**
	 * 计算一个产品
	 * @param unknown_type $oneProduct
	 */
	private function calcProduct(&$oneProduct)
	{
		$oneProduct["totalcount"] = 0;
		$oneProduct["passcount"] = 0;
		$oneProduct["failcount"] = 0;
		$oneProduct["skipcount"] = 0;
		$oneProduct["passrate"] = 0;
		
		//循环查找每个模块，并同步每个模块
		foreach($oneProduct as $key => &$value)
		{
			if (gettype($value) == "array")
			{
				$this->calcModule($value);
				
				$oneProduct["totalcount"] += $value["totalcount"];
				$oneProduct["passcount"] += $value["passcount"];
				$oneProduct["failcount"] += $value["failcount"];
				$oneProduct["skipcount"] += $value["skipcount"];
			}
		}
		
		if ($oneProduct["totalcount"] > 0)
		{
			$oneProduct["passrate"] = $oneProduct["passcount"]*100/$oneProduct["totalcount"];
		}
	}
	
	private function calcModule(&$oneModule)
	{
		$oneModule["totalcount"] = 0;
		$oneModule["passcount"] = 0;
		$oneModule["failcount"] = 0;
		$oneModule["skipcount"] = 0;
		$oneModule["passrate"] = 0;
		
		//循环查找每个模块，并同步每个模块
		foreach($oneModule as $key => &$value)
		{
			if (gettype($value) == "array")
			{				
				//构造一个颜色标志
				$value["resultcolor"] = "red";
				
				$oneModule["totalcount"] += 1;
				if (array_key_exists("result", $value))
				{
					$result = strtolower($value["result"]);
					
					$pos = strpos($result, "pass");
					if (!($pos===false))
					{
						$oneModule["passcount"] += 1;
						$value["resultcolor"] = "green";
					}
					else if (!(strpos($result, "skip")===false))
					{
						$oneModule["skipcount"] += 1;
					}
					else
					{
						$oneModule["failcount"] += 1;
					}
				}
				else
				{
					$oneModule["skipcount"] += 1;
					//人工构造一个result
					$value["result"] = "skip";
				}
			}
		}
		
		if ($oneModule["totalcount"] > 0)
		{
			$oneModule["passrate"] = $oneModule["passcount"]*100/$oneModule["totalcount"];
		}
	}
	
	/**
	 * 生成html标记
	 */
	private function generateHtml()
	{
		$this->testReport = "<html>";
		$this->testReport .= "<head>";
		$this->testReport .= sprintf("<title>自动化测试报告[%s]</title>", $this->envSetting["executetime"]);
		$this->testReport .= sprintf("<h1>自动化测试报告[%s]</h1>", $this->envSetting["executetime"]);
		$this->testReport .= "</head><p><p>";
		
		$this->testReport .= "<body>";
		$this->testReport .= $this->generateEnvHtml();
        $this->testReport .= $this->generateTotalHtml();
		$this->testReport .= $this->generateProducts();
		$this->testReport .= "</body></html>";
	}
	
	
	/**
	 * 把环境转化为html
	 */
	private function generateEnvHtml()
	{
        $this->logger->debug("enter generateEnvHtml()");

		$resultHtml = "<div>";
		$resultHtml .= "<table border = \"2\" bordercolor=#660066 bordercolordark=#660066 cellpadding=2 cellspacing=3>";
		$resultHtml .= "<tr><td colspan=\"2\" align=\"center\"><b><font color=blue>测试环境</font></b></td></tr>";
		
		foreach ($this->envSetting as $key => $value)
		{
			$resultHtml .= sprintf("<tr><td align=right><b>%s</b></td><td>%s</td></tr>", $key, $value);
		}
		$resultHtml .= "</table>";
		
		//加分割线
		$resultHtml .= "<HR style=‘border:3 double #987cb9’ width=‘80%’ color=#987cb9 SIZE=3>";
		$resultHtml .= "</div><p><p><p>";
		
        $this->logger->debug("leave generateEnvHtml()");
		return $resultHtml;
	}
	
	/**
	 * 为整个测试生成汇总
	 */
	private function generateTotalHtml()
	{
        $this->logger->debug("enter generateTotalHtml()");

		$resultHtml = "<div>";
		$resultHtml .= "<table border = \"2\" bordercolor=#660066 bordercolordark=#660066 cellpadding=2 cellspacing=3>";
		$resultHtml .= "<tr><td colspan=\"2\" align=\"center\"><b><font color=blue>本次测试汇总</font></b></td></tr>";
		
		$colorFlag = "green";
		if (($this->failCount + $this->skipCount) > 0)
		{
			$colorFlag = "red";
		}
		$resultHtml .= sprintf("<tr><td align=right><b>总Case数</b></td><td>%d</td></tr>", $this->totalCount);
		$resultHtml .= sprintf("<tr><td align=right><b>成功Case数</b></td><td><font color=green>%d</font></td></tr>", $this->passCount);
		$resultHtml .= sprintf("<tr><td align=right><b>失败Case数</b></td><td><font color=%s>%d</font></td></tr>", $colorFlag, $this->failCount);
		$resultHtml .= sprintf("<tr><td align=right><b>跳过Case数</b></td><td><font color=%s>%d</font></td></tr>", $colorFlag, $this->skipCount);
		$resultHtml .= sprintf("<tr><td align=right><b>通过率(%%)</b></td><td><font color=%s>%d</font></td></tr>", $colorFlag, $this->passRate);
		
		
		$resultHtml .= "</table></div>";
		//加分割线
		$resultHtml .= "<HR style=‘border:3 double #987cb9’ width=‘80%’ color=#987cb9 SIZE=3>";
		$resultHtml .= "<p><p><p>";
		$this->logger->debug("leave generateTotalHtml()");
		return $resultHtml;
	}
	
	/**
	 * 把所有产品转化为html
	 */
	private function generateProducts()
	{
        $this->logger->debug("enter generateProducts()");

		$resultHtml = "<div>";
		$index = 0;
		
		//循环每个产品
		foreach ($this->productResult as &$oneProduct)
		{
			$index += 1;
				
			if (array_key_exists("name", $oneProduct))
			{
				$resultHtml .= sprintf("<h2>%d.[产品]%s</h2>", $index, $oneProduct["name"]);
			}
			else
			{
				$resultHtml .= sprintf("<h2>%d.[产品]-%d</h2>", $index, $index);
			}
				
			$resultHtml .= $this->generateProduct($oneProduct);
			$resultHtml .= "<HR style='border:1 dashed #987cb9' width='100%' color=#987cb9 SIZE=1>";
		}
		
		$resultHtml .= "</div><p><p>";
	    $this->logger->debug("leave generateProducts()");
		return $resultHtml;
	}
	
	/**
	 * 生成一个产品的html
	 * @param unknown_type $oneProduct
	 */
	private function generateProduct(&$oneProduct)
	{
		$resultHtml = "";
		
		$colorFlag = "green";
		if (($oneProduct["skipcount"] + $oneProduct["failcount"]) > 0)
		{
			$colorFlag = "red";
		}
		
		//先生成summary表
		$resultHtml .= "<table border = \"2\" bordercolor=#660066 bordercolordark=#660066 cellpadding=2 cellspacing=3>";
        $resultHtml .= "<tr><td colspan=\"2\" align=\"center\"><b><font color=blue>产品汇总</font></b></td></tr>";

		$resultHtml .= sprintf("<tr><td align=right><b>总Case数</b></td><td>%d</td></tr>", $oneProduct["totalcount"]);
		$resultHtml .= sprintf("<tr><td align=right><b>成功Case数</b></td><td><font color=green>%d</font></td></tr>", $oneProduct["passcount"]);
		$resultHtml .= sprintf("<tr><td align=right><b>失败Case数</b></td><td><font color=%s>%d</font></td></tr>", $colorFlag, $oneProduct["failcount"]);
		$resultHtml .= sprintf("<tr><td align=right><b>跳过Case数</b></td><td><font color=%s>%d</font></td></tr>", $colorFlag, $oneProduct["skipcount"]);
		$resultHtml .= sprintf("<tr><td align=right><b>通过率(%%)</b></td><td><font color=%s>%d</font></td></tr>", $colorFlag, $oneProduct["passrate"]);
		$resultHtml .= "</table><p>";
		
		//循环查找每个模块，并同步每个模块
		$index = 0;
		foreach($oneProduct as $key => &$value)
		{
			if (gettype($value) == "array")
			{
				$index++;
				if (array_key_exists("name", $value))
				{
					$resultHtml .= sprintf("<h3>%d.[模块]%s</h3>", $index, $value["name"]);
				}
				else
				{
					$resultHtml .= sprintf("<h3>%d.[模块]-%d</h3>", $index, $index);
				}
				
				$resultHtml .= $this->generateModule($value);
				$resultHtml .= "<p>";
			}
		}
		
		$resultHtml .= "<p><p>";
		
		return $resultHtml;
	}
	
	/**
	 * 生成模块的html表格
	 * @param unknown_type $oneModule
	 */
	private function generateModule(&$oneModule)
	{
		$resultHtml = "";
		
		$colorFlag = "green";
		if (($oneModule["failcount"]+$oneModule["skipcount"]) > 0)
		{
			$colorFlag = "red";
		}
		
		//先生成summary表
		$resultHtml = "<table border = \"1\" bordercolor=#660066 bordercolordark=#660066 cellpadding=2 cellspacing=3>";
        $resultHtml .= "<tr><td colspan=\"2\" align=\"center\"><b><font color=blue>模块汇总</font></b></td></tr>";

		
		$resultHtml .= sprintf("<tr><td align=right><b>总Case数</b></td><td>%d</td></tr>", $oneModule["totalcount"]);
		$resultHtml .= sprintf("<tr><td align=right><b>成功Case数</b></td><td><font color=green>%d</font></td></tr>", $oneModule["passcount"]);
		$resultHtml .= sprintf("<tr><td align=right><b>失败Case数</b></td><td><font color=%s>%d</font></td></tr>", $colorFlag, $oneModule["failcount"]);
		$resultHtml .= sprintf("<tr><td align=right><b>跳过Case数</b></td><td><font color=%s>%d</font></td></tr>", $colorFlag, $oneModule["skipcount"]);
		$resultHtml .= sprintf("<tr><td align=right><b>通过率(%%)</b></td><td><font color=%s>%d</font></td></tr>", $colorFlag, $oneModule["passrate"]);
		$resultHtml .= "</table>";
		
		
		//循环查找每个case
		$resultHtml .= "<table border = 1 bordercolor=#660066 bordercolordark=#660066 cellpadding=2 cellspacing=3>";
		$resultHtml .= "<tr><td colspan=6 align=\"center\"><b><font color=blue>Case详细结果</font></b></td></tr>";
		$resultHtml .= "<tr><th>序号</th><th>标题</th><th>步骤</th><th>结果</th><th>原因</th><th>Bug ID</th></tr>";
		$index = 0;
		
		foreach($oneModule as $key => &$value)
		{
			if (gettype($value) == "array")
			{
				$index += 1;
				
				$colorFlag = $value["resultcolor"];
				$title = "";
				$steps = "";
				$result = "";
				$reason = "";
				$bugurl = "";
				$bugid = "";
				
				//get title
				if (array_key_exists("title", $value))
				{
					$title = htmlspecialchars($value["title"]);
				}
					
				//steps
				if (array_key_exists("steps", $value))
				{
					//$steps = htmlspecialchars(str_replace("<br />","<br/>",$value["steps"]));
                    //$steps = str_replace("<br />","<br/>",$value["steps"]);
                    $steps = $value["steps"];
				}
				
				//steps
				if (array_key_exists("reason", $value))
				{
					$reason = htmlspecialchars($value["reason"]);
				}
				
				//bug id
                if (array_key_exists("bugid", $value))
                {
                    $bugid = $value["bugid"];
                }

                //bug url
				if (array_key_exists("bugurl", $value))
				{
					$bugurl = htmlspecialchars($value["bugurl"]);
				    $this->logger->info("bug id:".$value["bugid"].", bug url:".$value["bugurl"]);
				}
				
				//result
				$result = $value["result"];
				
				$resultHtml .= sprintf("<tr><td align=left>%d</td><td align=right><b>%s</b></td><td>%s</td><td><font color=%s>%s</font></td><td><font color=%s>%s</font></td><td><font color=blue><a href='%s'>%s</a></font></td></tr>", 
						               $index, $title, $steps, $colorFlag, $result, $colorFlag, $reason, $bugurl, $bugid);
			}
		}
		
		$resultHtml .= "</table><br>";
		
		return $resultHtml;
	}
}

?>

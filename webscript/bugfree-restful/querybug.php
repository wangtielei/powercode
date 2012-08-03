<?php
//http://192.168.100.86/test/querycase.php
/**
查询数据库有哪些表：http://192.168.100.86/test/querycase.php
查询某个表有哪些列: http://192.168.100.86/test/querycase.php?showcolumns&table=your_table_name
查询某个表的所有数据：http://192.168.100.86/test/querycase.php?your_table_name
查询测试用例：
a)查询所有的测试用例: http://192.168.100.86/test/querycase.php?bf_caseview
b)根据title查询用例: http://192.168.100.86/test/querycase.php?bf_caseview&title=苹果助手-意见反馈
c)根据产品，模块以及title查询: http://192.168.100.86/test/querycase.php?bf_caseview&title=苹果助手-意见反馈&product_name=助手-ios&module_name=助手-ios/URL自动扫描/红色版
*/

define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/"); 
require_once(LOG4PHP_DIR.'/Logger.php'); 
require_once("configreader.php");
require_once("bugzilla.php");

// Tell log4php to use our configuration file.
Logger::configure('querycase.xml'); 
// Fetch a logger, it will inherit settings from the root logger
$logger = Logger::getLogger('myLogger');

//phpinfo();
//config reader
$configReader = new ConfigReader();
$logger->debug("ini file:\n".print_r($configReader->getConfigs(),true));
$sectionName = "bugzilla";
//get user parameter string
$parameters = substr($_SERVER['REQUEST_URI'], strlen($configReader->getSectionKey($sectionName, 'baseURL')));
$parameters = trim(html_entity_decode($parameters));
$logger->debug("parameters: ".$parameters);
//has no input parameter, redirect to register page
if (empty ($parameters))
{
    $parameters = "showtables";
}
else
{
    $parameters = trim($parameters);

    if (strcmp(substr($parameters, 0, 1), "?") == 0)
    {
        $parameters = substr($parameters, 1);
    }
}

$logger->debug("parameters: ".$parameters);

//query data
$bugfree = new Bugfree($configReader, $parameters);
$bugfree->exec();

?>

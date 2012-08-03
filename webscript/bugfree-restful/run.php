<?php
define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/"); 
require_once(LOG4PHP_DIR.'/Logger.php'); 
require_once("configreader.php");

// Tell log4php to use our configuration file.
Logger::configure('log4php.xml'); 
// Fetch a logger, it will inherit settings from the root logger
$logger = Logger::getLogger('myLogger');


//config reader
$configReader = new ConfigReader("run.ini");
$logger->debug("ini file:\n".print_r($configReader->getConfigs(),true));


$sectionName = "run";
//get user parameter string
$jobName = substr($_SERVER['REQUEST_URI'], strlen($configReader->getSectionKey($sectionName, 'baseURL')));
$jobName = trim(html_entity_decode($jobName));
$logger->debug("jobName: ".$jobName);
//has no input parameter, redirect to register page
if (empty ($jobName))
{
    header("HTTP/1.0 404 Not Found job name");
    exit;
}

//get command list
$cmdList = $configReader->getSection($jobName);
if (!isset($cmdList))
{
	  header(sprintf("HTTP/1.0 404 Not Found command list for job [%s]", $jobName));
    exit;
}

//execute command
foreach ($cmdList as $key => $value)
{
    $logger->info("$key = $value");
    
    system($value);
}

header("HTTP/1.0 200 OK");

?>

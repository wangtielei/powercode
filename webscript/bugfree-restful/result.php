<?php
define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/"); 
require_once(LOG4PHP_DIR.'/Logger.php'); 
require("../php-class/PHPMailer_5.2.1/class.phpmailer.php");
require_once('xmlparser.php');
require_once('casewriter.php');
require_once('report.php');
require_once('util.php');

// Tell log4php to use our configuration file.
Logger::configure('result.xml');
 
// Fetch a logger, it will inherit settings from the root logger
$logger = Logger::getLogger('myLogger');

$allHeaders = getHttpHeaders();
$logger->info("headers:\n".print_r($allHeaders,true));
//read body
$bodyData = "";
$httpContent = fopen('php://input', 'r');
while ($data = fread($httpContent, 1024*1024)) 
{
    $bodyData .= $data;
}
fclose($httpContent);

$logger->debug("body:\n".$bodyData);

//解析body为数组
$xmlParser = new XmlParser();
if (!$xmlParser->parseXml($bodyData))
{
	header("HTTP/1.0 501 解析xml失败.");
	exit;
}

$envSetting = $xmlParser->getEnvSetting();
#$logger->info("env:\n".print_r($envSetting, true));
$productResult = $xmlParser->getProductResult();
#$logger->info("product result:\n".print_r($productResult, true));

//生成测试用例
$caseWriter = new CaseWriter($envSetting, $productResult);
if (!$caseWriter->syncTestResult())
{
	header("HTTP/1.0 501 与bugfree同步失败.");
}
$envSetting = $caseWriter->getEnvSetting();
$productResult = $caseWriter->getProductResult();
$logger->info("env:\n".print_r($envSetting, true));
$logger->info("product result:\n".print_r($productResult, true));

//生成测试报告
$reportor = new Reportor($envSetting, $productResult);
$reportContent = $reportor->getReport();

//发送邮件
$mailSetting = $xmlParser->getMailSetting();
$logger->info("mail:\n".print_r($mailSetting, true));

if (isset($mailSetting["mail-to"]))
{
	$from = "guanjianjun@360.cn";
	if (isset($mailSetting["mail-from"]))
	{
		$from = $mailSetting["mail-from"];
	}
	
	//send email
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->CharSet = "utf-8";
	$mail->Host = "mail.corp.qihoo.net";  // specify main and backup server
	$mail->From = $from;
	$mail->FromName = substr($from, 0, strpos($from, '@'));
	
	$toList = explode(";", $mailSetting["mail-to"]);
	foreach($toList as $toAddr)
	{
		$mail->AddAddress($toAddr);
	}
	
	//handle CC
	if (isset($mailSetting["mail-cc"]))
	{
		$ccList = explode(";", $mailSetting["mail-cc"]);
		foreach($ccList as $cc)
		{
			$mail->AddCC($cc);
		}
	}
	$mail->IsHTML(true);
	if (isset($mailSetting["mail-title"]))
	{
		$mail->Subject = $mailSetting["mail-title"];
	}
	else
	{
		$mail->Subject = sprintf("自动化测试报告[%s]", $envSetting["executetime"]);
	}
	
	$mail->Body = $reportContent;
	
	if(!$mail->Send())
	{
		header(sprintf("HTTP/1.0 400 Send mail failed:%s", $mail->ErrorInfo));		
	}
    else
    {
        $logger->debug("send email successful");
    }
}

header("HTTP/1.0 200 OK");

?>

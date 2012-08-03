<?php
require_once('log.php');
openLogFile("/tmp/upgrade.log");

writeLog("url: ".$_SERVER['REQUEST_URI']);

sleep(1);
$filePath = "upgrade.xml";
$fileData = "";
$fp = fopen($filePath, 'r');
//$fileData = fread($fp, filesize($filePath));
$fileData = file_get_contents($filePath);
//$fileData = system("cat upgrade.xml");
writeLog($fileData);

//write data to client
header('Content-Type: text/xml;charset=gbk');
//header('Content-Type: text/xml');
#header('Cache-Control: no-cache');
//header('Cache-Control: max-age=0');
echo $fileData;
fclose($fp);
closeLogFile();
?>

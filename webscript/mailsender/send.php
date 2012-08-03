<?php

require_once('../php-class/phplog.php');
require_once('mail.php');

openLogFile("sendmail.log");

writeLog("request data:\n".print_r($_SERVER,true));
$mailHandler = new Mail();
$mailHandler->handleRequest();

writeLog(sprintf("error code: %s, result msg: %s", $mailHandler->getErrorCode(), $mailHandler->getResultMsg()));
header(sprintf("HTTP/1.0 %s %s", $mailHandler->getErrorCode(), $mailHandler->getResultMsg()));
closeLogFile();
?>

<?php
define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/");
require_once(LOG4PHP_DIR.'/Logger.php');
require_once('mail.php');

Logger::configure('mail.xml');
$logger = Logger::getLogger('myLogger');

$mailHandler = new Mail();
$mailHandler->handleRequest();

$logger->debug(sprintf("error code: %s, result msg: %s", $mailHandler->getErrorCode(), $mailHandler->getResultMsg()));
header(sprintf("HTTP/1.0 %s %s", $mailHandler->getErrorCode(), $mailHandler->getResultMsg()));
?>

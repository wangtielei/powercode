<?php

function openLogFile($logfile)
{
    //$rootPath = $_SERVER['DOCUMENT_ROOT'];
    $fpLog = fopen("/opt/lampp/logs/$logfile", 'a+');
    $GLOBALS['logfile'] = $fpLog;
    //date_default_timezone_set('America/Los_Angeles');
    //将时区设置为中国
    date_default_timezone_set('PRC');
    fwrite($GLOBALS['logfile'], "\n========Enter Rest at ".date('l jS \of F Y h:i:s A')."============\n");
}
function writeLog($logString)
{
    fwrite($GLOBALS['logfile'],$logString."\n");
}

function closeLogFile()
{
    fwrite($GLOBALS['logfile'], '========Leave Rest at '.date('l jS \of F Y h:i:s A')."============\n");
    fclose($GLOBALS['logfile']);         
}
?>

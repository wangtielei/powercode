<?php
define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/"); 
require_once(LOG4PHP_DIR.'/Logger.php'); 

date_default_timezone_set('PRC');

Logger::configure('../php-class/apache-log4php-2.2.1/log4php.xml');
$logger = Logger::getLogger('myLogger');
$logger->debug("url: ".$_SERVER['REQUEST_URI']);

$form = "";

# no type parameter mean index page
if (!isset($_GET['type']))
{
    $form .= "<p><font size=10 color=red>请使用firefox浏览器，IE浏览不支持textarea里显示xml</font></p>";
    $form .= "<p><font size=10>生成升级配置文件</font></p>";
    $form .= "<form action='maker.php' method='get'>";

    $form .= "<label for='template-1'>请选择产品: </label>";
    $form .= "<select name='type' id='template-1'>".
            "<option>苹果助手PC版</option>".
            "<option>苹果助手IPhone版</option>".
            "<option>Android-browser</option></select>";
    $form .= "<p><p>";
    
    $form .= "<label for='date-1'>发布日期: </label>";
    $form .= sprintf("<input type='text' name='date' size='20' id='date-1' value='%s'></input>", date('Y-m-d'));
    $form .= "<label>   例如:   2012-07-05</label>";
    $form .= "<p><p>";

    $form .= "<label for='version-1'>版本号: </label>";
    $form .= "<input type='text' name='version' id='version-1' size='20'></input>";
    $form .= "<label>   例如:   1.2.1.2133</label>";
    $form .= "<p><p>";
    
    $form .= "<label for='md5-1'> MD5(用CalcuHash计算的):</label>";
    $form .= "<input type='text' name='md5' id='md5-1' size='60'></input>";
    $form .= "<p><p>";

    $form .= "<label for='filesize-1'> 文件大小(用CalcuHash计算的):</label>";
    $form .= "<input type='text' name='filesize' id='filesize-1' size='20'></input>";
    $form .= "<p><p>";

    $form .= "<label for='tip-1'>升级提:</label><br/>";
    $form .= "<textarea cols='6' rows='10'  size='15' name='tip' id='tip-1'></textarea>";
    $form .= "<p><p>";
    $form .= "<input type='submit' style='font-size:40px; background:#00ff00' size='40' value='生成升级配置'/>";
    $form .= "</form>";

    echo "<big>" . "$form" . "</big>";
}
else
{
    $logger->debug("type: ".$_GET['type']);
    $logger->debug("date: ".$_GET['date']);
    $logger->debug("version: ".$_GET['version']);
    $logger->debug("mdr:".$_GET["md5"]);
    $logger->debug("filesize:".$_GET["filesize"]);
    $logger->debug("tip: \n".$_GET['tip']);

    $filePath = "";
    $destFile = "";
    $response = "";
    if (strcmp($_GET['type'], "苹果助手PC版") == 0)
    {
        $filePath = "zhushou-pc-template.xml";
        $destFile = sprintf("zhushoupc-%s-upgrade.xml",date('Ymd'));
    }

    $isDataReady = 1;

    if (strlen($_GET['tip']) ==0)
    {
        $isDataReady = 0;
        $response = "升级提示为空！！！";
    }
    else if (strlen($_GET['version']) ==0)
    {
        $isDataReady = 0;
        $response = "版本号为空！！！";
    }
    else if (strlen($_GET['date']) == 0)
    {
        $isDataReady = 0;
        $response = "发布日期为空！！！";
    }
    else if (strlen($_GET['md5']) == 0)
    {
        $isDataReady = 0;
        $response = "MD5为空！！！";
    }
    else if (strlen($_GET['filesize']) == 0)
    {
        $isDateReady = 0;
        $response = "文件大小为空！！！";
    }

    if ($isDataReady == 1)
    {
        if (strlen($filePath) > 0)
        {
            $fp = fopen($filePath, 'r');
            $response = file_get_contents($filePath);
            
            //从上传的版本中分离不包含build号的版本号
            $shortVer = "1.2.1";
            $verNums = explode("/", $_GET['version']);
            if (count($verNums) >= 3)
            {
                $shortVer = sprintf("%s.%s.%s", $verNums[0], $verNums[1], $verNums[2]);
            }
            $logger->debug("short version:".$shortVer);

            //去除提示中的\r\n为\r
            $upgradeTips = $_GET['tip'];
            $upgradeTips = str_replace("\r", "", $upgradeTips);
            //$upgradeTips = htmlspecialchars($upgradeTips);
            $logger->debug("final tips:".$upgradeTips);

            $response = str_replace("date-is-here", $_GET['date'], $response);
            $response = str_replace("version-is-here", $_GET['version'], $response);
            $response = str_replace("tips-content-is-here", $upgradeTips, $response);
            $response = str_replace("md5-is-here", $_GET['md5'], $response);
            $response = str_replace("filesize-is-here", $_GET['filesize'], $response);
            $response = str_replace("short-version-here", $shortVer, $response);
            fclose($fp);

            #write file
            $fp = fopen($destFile, 'w');
            fwrite($fp, $response);
            fclose($fp);
        }
        else
        {
            $response = "选择的产品没有配置正确的升级文件模板！！！";
        }
    }

    #writeLog("response:\n".$response);

    $response = sprintf("<p><font size=10>配置文件生成结果</font></p></br><label for='tip-1'>请拷贝下面内容保存为[%s]文件即可: </label><br/><textarea cols='100' rows='15'  size='15' name='tip' id='tip-1'>%s</textarea>", $destFile, $response);
    
    $response .= "<br/><a href='http://192.168.100.86/zhushou/maker.php'>返回</a>";
    $logger->debug("response:\n".$response);
    echo $response;        
}

header('Content-Type: text/html;charset=gb2312');
header('Cache-Control: no-cache');

?>

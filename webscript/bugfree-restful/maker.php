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
    $form .= "<p><font size=10 color=red>��ʹ��firefox�������IE�����֧��textarea����ʾxml</font></p>";
    $form .= "<p><font size=10>�������������ļ�</font></p>";
    $form .= "<form action='maker.php' method='get'>";

    $form .= "<label for='template-1'>��ѡ���Ʒ: </label>";
    $form .= "<select name='type' id='template-1'>".
            "<option>ƻ������PC��</option>".
            "<option>ƻ������IPhone��</option>".
            "<option>Android-browser</option></select>";
    $form .= "<p><p>";
    
    $form .= "<label for='date-1'>��������: </label>";
    $form .= sprintf("<input type='text' name='date' size='20' id='date-1' value='%s'></input>", date('Y-m-d'));
    $form .= "<label>   ����:   2012-07-05</label>";
    $form .= "<p><p>";

    $form .= "<label for='version-1'>�汾��: </label>";
    $form .= "<input type='text' name='version' id='version-1' size='20'></input>";
    $form .= "<label>   ����:   1.2.1.2133</label>";
    $form .= "<p><p>";
    
    $form .= "<label for='md5-1'> MD5(��CalcuHash�����):</label>";
    $form .= "<input type='text' name='md5' id='md5-1' size='60'></input>";
    $form .= "<p><p>";

    $form .= "<label for='filesize-1'> �ļ���С(��CalcuHash�����):</label>";
    $form .= "<input type='text' name='filesize' id='filesize-1' size='20'></input>";
    $form .= "<p><p>";

    $form .= "<label for='tip-1'>������:</label><br/>";
    $form .= "<textarea cols='6' rows='10'  size='15' name='tip' id='tip-1'></textarea>";
    $form .= "<p><p>";
    $form .= "<input type='submit' style='font-size:40px; background:#00ff00' size='40' value='������������'/>";
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
    if (strcmp($_GET['type'], "ƻ������PC��") == 0)
    {
        $filePath = "zhushou-pc-template.xml";
        $destFile = sprintf("zhushoupc-%s-upgrade.xml",date('Ymd'));
    }

    $isDataReady = 1;

    if (strlen($_GET['tip']) ==0)
    {
        $isDataReady = 0;
        $response = "������ʾΪ�գ�����";
    }
    else if (strlen($_GET['version']) ==0)
    {
        $isDataReady = 0;
        $response = "�汾��Ϊ�գ�����";
    }
    else if (strlen($_GET['date']) == 0)
    {
        $isDataReady = 0;
        $response = "��������Ϊ�գ�����";
    }
    else if (strlen($_GET['md5']) == 0)
    {
        $isDataReady = 0;
        $response = "MD5Ϊ�գ�����";
    }
    else if (strlen($_GET['filesize']) == 0)
    {
        $isDateReady = 0;
        $response = "�ļ���СΪ�գ�����";
    }

    if ($isDataReady == 1)
    {
        if (strlen($filePath) > 0)
        {
            $fp = fopen($filePath, 'r');
            $response = file_get_contents($filePath);
            
            //���ϴ��İ汾�з��벻����build�ŵİ汾��
            $shortVer = "1.2.1";
            $verNums = explode("/", $_GET['version']);
            if (count($verNums) >= 3)
            {
                $shortVer = sprintf("%s.%s.%s", $verNums[0], $verNums[1], $verNums[2]);
            }
            $logger->debug("short version:".$shortVer);

            //ȥ����ʾ�е�\r\nΪ\r
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
            $response = "ѡ��Ĳ�Ʒû��������ȷ�������ļ�ģ�壡����";
        }
    }

    #writeLog("response:\n".$response);

    $response = sprintf("<p><font size=10>�����ļ����ɽ��</font></p></br><label for='tip-1'>�뿽���������ݱ���Ϊ[%s]�ļ�����: </label><br/><textarea cols='100' rows='15'  size='15' name='tip' id='tip-1'>%s</textarea>", $destFile, $response);
    
    $response .= "<br/><a href='http://192.168.100.86/zhushou/maker.php'>����</a>";
    $logger->debug("response:\n".$response);
    echo $response;        
}

header('Content-Type: text/html;charset=gb2312');
header('Cache-Control: no-cache');

?>

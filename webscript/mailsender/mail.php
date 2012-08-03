<?php
require_once('../php-class/phplog.php');
require("../php-class/PHPMailer_5.2.1/class.phpmailer.php");

class Mail
{
    private $method = 'GET';
    private $requestData = NULL;
    private $resultMsg = "only support Post method.";
    private $errorCode = "405";
    private $allHeaders = array();

    public function getResultMsg()
    {
        return $this->resultMsg;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    //constructor function
    public function __construct()
    {
        writeLog("enter Mail() constructor");
    }

    function handleRequest()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];   
        
        //get all headers
        //HTTP_开头的就是HTTP请求头
        foreach ($_SERVER as $key => $value) 
        {
            if ('HTTP_' == substr($key, 0, 5)) 
            {
                $this->allHeaders[strtolower(str_replace('_', '-', substr($key, 5)))] = $value;
            }
        }
        //不过并不是所有的HTTP请求头都是以HTTP_开头的的键的形式存在与$_SERVER里，
        //比如说Authorization，Content-Length，Content-Type就不是这样，所以说为了取得所有的HTTP请求头，
        //还需要加上下面这段代码：
        if (isset($_SERVER['PHP_AUTH_DIGEST'])) 
        {
            $this->allHeader['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST'];
        } 
        elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) 
        {
            $this->allHeader['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
        }

        if (isset($_SERVER['CONTENT_LENGTH'])) 
        {
            $this->allHeader['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH'];
        }
        if (isset($_SERVER['CONTENT_TYPE'])) 
        {
            $this->allHeader['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE'];
        }
        writeLog("header count: ".count($this->allHeaders));
        writeLog(print_r($this->allHeaders, true));

        switch ($this->method) 
        {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            case 'PUT':
                $this->handlePut();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
        }
    }

    function handleGet()
    {
    }

    function handlePost()
    {
        //get post data
        if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) 
        {
            writeLog("CONTENT_LENGTH = ".$_SERVER['CONTENT_LENGTH']);

            $this->requestData = '';
            $httpContent = fopen('php://input', 'r');
            while ($data = fread($httpContent, 1024*1024)) 
            {
                $this->requestData .= $data;
            }
            fclose($httpContent);

            writeLog("requestData-length: ".strlen($this->requestData));
            writeLog("requestData: ".$this->requestData);
        }
        else
        {
            writeLog("no any content data");
        }

        //check Mail-From
        if (!isset($this->allHeaders["mail-from"]))
        {
            $this->errorCode = "204";
            $this->resultMsg = "no 'Mail-From' header";
            return;
        }
        
        //check Mail-To
        if (!isset($this->allHeaders["mail-to"]))
        {
            $this->errorCode = "204";
            $this->resultMsg = "no 'Mail-To' header";
            return;
        }

        //check title
        if (!isset($this->allHeaders["mail-title"]))
        {
            $this->errorCode = "204";
            $this->resultMsg = "no 'Mail-Title' header";
            return;
        }
        writeLog("step1");
        //send email
        $mail = new PHPMailer();
        $mail->IsSMTP(); 
        $mail->CharSet = "utf-8";
        $mail->Host = "mail.corp.qihoo.net";  // specify main and backup server
        $mail->From = $this->allHeaders["mail-from"];
        $mail->FromName = substr($this->allHeaders["mail-from"], 0, strpos($this->allHeaders["mail-from"], '@'));
        
        $toList = explode(";", $this->allHeaders["mail-to"]);
        foreach($toList as $toAddr)
        {
            $mail->AddAddress($toAddr);
        }

        $mail->IsHTML(true);
        $mail->Subject = $this->allHeaders["mail-title"];
        $mail->Body = $this->requestData;
        if(!$mail->Send())
        {
            $this->resultMsg = "Message could not be sent. <p>";
            $this->resultMsg = "Mailer Error: ".$mail->ErrorInfo;
            $this->errorCode = "400";
        }
        else
        {
            $this->errorCode = "200";
            $this->resultMsg = "Send Successful";
        }
        writeLog("step end");
    }

    function handlePut()
    {
    }

    function handleDelete()
    {
    }
}
?>

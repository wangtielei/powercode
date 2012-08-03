<?php
define (LOG4PHP_DIR, "../php-class/apache-log4php-2.2.1/src/main/php/");
require_once(LOG4PHP_DIR.'/Logger.php');
require("../php-class/PHPMailer_5.2.1/class.phpmailer.php");
require("util.php");

class Mail
{
    private $method = 'GET';
    private $requestData = NULL;
    private $resultMsg = "only support Post method.";
    private $errorCode = "405";
    private $allHeaders = array();
    private $logger = NULL;

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
        $this->logger = Logger::getLogger(__CLASS__);
    }

    function handleRequest()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];   
        
        $this->allHeaders = getHttpHeaders();
        
        $this->logger->debug("header count: ".count($this->allHeaders));
        $this->logger->debug(print_r($this->allHeaders, true));

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
            $this->logger->debug("CONTENT_LENGTH = ".$_SERVER['CONTENT_LENGTH']);

            $this->requestData = '';
            $httpContent = fopen('php://input', 'r');
            while ($data = fread($httpContent, 1024*1024)) 
            {
                $this->requestData .= $data;
            }
            fclose($httpContent);

            $this->logger->debug("requestData-length: ".strlen($this->requestData));
            $this->logger->debug("requestData: ".$this->requestData);
        }
        else
        {
            $this->logger->debug("no any content data");
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
        
        //handle CC
        if (isset($this->allHeaders["mail-cc"]))
        {
            $ccList = explode(";", $this->allHeaders["mail-cc"]);
            foreach($ccList as $cc)
            {
                $mail->AddCC($cc);
            }
        }
        $mail->IsHTML(true);
        $mail->Subject = $this->allHeaders["mail-title"];
        $mail->Body = $this->requestData;
        $this->logger->debug("body:\n".$mail->Body);

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
    }

    function handlePut()
    {
    }

    function handleDelete()
    {
    }
}
?>

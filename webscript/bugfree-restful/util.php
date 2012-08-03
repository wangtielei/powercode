<?php


/**
 * Redirect function
 */
function translateChars($inputStr)
{
    $inputStr = str_replace("%21", "!", $inputStr);
    $inputStr = str_replace("%2A", "*", $inputStr);
    $inputStr = str_replace("%27", "'", $inputStr);
    $inputStr = str_replace("%28", "(", $inputStr);
    $inputStr = str_replace("%29", ")", $inputStr);
    $inputStr = str_replace("%3B", ";", $inputStr);
    $inputStr = str_replace("%3A", ":", $inputStr);
    $inputStr = str_replace("%40", "@", $inputStr);
    $inputStr = str_replace("%26", "&", $inputStr);
    $inputStr = str_replace("%3D", "=", $inputStr);
    $inputStr = str_replace("%2B", "+", $inputStr);
    $inputStr = str_replace("%24", "$", $inputStr);
    $inputStr = str_replace("%2C", ",", $inputStr);
    $inputStr = str_replace("%2F", "/", $inputStr);
    $inputStr = str_replace("%3F", "?", $inputStr);
    $inputStr = str_replace("%23", "#", $inputStr);
    $inputStr = str_replace("%5B", "[", $inputStr);
    $inputStr = str_replace("%5D", "]", $inputStr);

    $inputStr = str_replace("%3C", "<", $inputStr);
    $inputStr = str_replace("%3E", ">", $inputStr);
    $inputStr = str_replace("%7E", "~", $inputStr);
    $inputStr = str_replace("%2E", ".", $inputStr);
    $inputStr = str_replace("%22", "\"", $inputStr);
    $inputStr = str_replace("%7B", "{", $inputStr);
    $inputStr = str_replace("%7D", "}", $inputStr);
    $inputStr = str_replace("%7C", "|", $inputStr);
    $inputStr = str_replace("%5C", "\\", $inputStr);
    $inputStr = str_replace("%2D", "-", $inputStr);
    $inputStr = str_replace("%60", "`", $inputStr);
    $inputStr = str_replace("%5F", "_", $inputStr);
    $inputStr = str_replace("%5E", "^", $inputStr);
    $inputStr = str_replace("%25", "%", $inputStr);
    $inputStr = str_replace("%20", " ", $inputStr);
    return $inputStr;
}

function xmlEncode($inputStr)
{
    $inputStr = str_replace("<", "&lt;", $inputStr);
	$inputStr = str_replace(">", "&gt;", $inputStr);
	$inputStr = str_replace("&", "&amp;", $inputStr);
	$inputStr = str_replace("'", "&apos;", $inputStr);
	$inputStr = str_replace("\"", "&quot;", $inputStr);
	return $inputStr;
}

function xmlDecode($inputStr)
{
	$inputStr = str_replace("&lt;", "<", $inputStr);
	$inputStr = str_replace("&lt;", "<", $inputStr);
	$inputStr = str_replace("&amp;", "&", $inputStr);
	$inputStr = str_replace("&apos;", "'", $inputStr);
	$inputStr = str_replace("&quot;", "\"", $inputStr);
	return $inputStr;
}

function getHttpHeaders()
{
	$allHeader = array();

	//get all headers
	//HTTP_开头的就是HTTP请求头
	foreach ($_SERVER as $key => $value)
	{
		if ('HTTP_' == substr($key, 0, 5))
		{
			$allHeader[strtolower(str_replace('_', '-', substr($key, 5)))] = $value;
		}
	}
	//不过并不是所有的HTTP请求头都是以HTTP_开头的的键的形式存在与$_SERVER里，
	//比如说Authorization，Content-Length，Content-Type就不是这样，所以说为了取得所有的HTTP请求头，
	//还需要加上下面这段代码：
	if (isset($_SERVER['PHP_AUTH_DIGEST']))
	{
		$allHeader['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST'];
	}
	elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
	{
		$allHeader['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
	}

	if (isset($_SERVER['CONTENT_LENGTH']))
	{
		$allHeader['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH'];
	}
	if (isset($_SERVER['CONTENT_TYPE']))
	{
		$allHeader['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE'];
	}

	return $allHeader;
}

function getEncodeType($string)
{
    $code = array('ASCII', 'gb2312', 'GBK', 'UTF-8');
    foreach($code as $c)
    {
        if($string === iconv('UTF-8', $c, iconv($c, 'UTF-8', $string)))
        {
            return $c;
        }
    }
    return "";
}

function getCurDateTime()
{
    //将时区设置为中国
    date_default_timezone_set('PRC');
	return date('Y-m-d H:i:s');
}

function getCurDateTimeString()
{
    //将时区设置为中国
    date_default_timezone_set('PRC');
    //return date('Ymd-His');
    return date('Y-m-d H:i:s');
}

?>

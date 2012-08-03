#! /usr/bin/perl -w

=pod
/**
 * dependents:
 * 1)install Log4perl
 * 2)
**/
=cut

use strict;
use warnings;
use Cwd;
use yutil;
use yconfigreader;
use ylog4perl;
use LWP::UserAgent;
use MIME::Lite;
use HTTP::Headers;
use HTTP::Request;
use WWW::Curl::Easy;

#log printer
our $logger = undef;

#config reader
our $configReader = undef;

#global handled url set
our $handledUrlDict = {};

=pod
/**
 * @brief: init smart backend test framework
 * @note:
 * @author: jianjun guan
 * @date: 2011/07/24
 **/
=cut
sub init
{
    system("rm scanurl.log");
    #create logger
    # init log4perl
    ylog4perl->initLogger("log4perl.conf");
    # log4perl instance
    $logger = ylog4perl->getLogger("urls");
    
    #create config reader
    $configReader = yconfigreader->new();
    my $confPath = getcwd()."/urls.conf";
    $logger->info($confPath);
    $configReader->loadConfig($confPath);
            
    return 1;    
}

=pod
/**
 * @brief: send out email through smtp
 *
 * @return: 1-successful, 0-error
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/02/22
 **/
=cut
sub sendSMTPMail()
{
    my ($from, $to, $subject, $message) = @_;
    
    # create a new message
    my $msg = MIME::Lite->new(
      From => $from,
      To => $to,
      Subject => $subject,
      Data => $message,
      Type =>'text/html'
    );
    
    eval
    {
        #or 
        my $mailServer = "mail.corp.qihoo.net"; 

        MIME::Lite->send('smtp', $mailServer, Timeout => 60);
        $msg->send();
    };
    if($@)
    {
        $logger->error("send mail failed.");
        return 0;        
    }
    else
    {
        $logger->info("send mail successful.");
        return 1;
    }
}

sub sendEMail()
{
    my ($from, $to, $subject, $message) = @_;
    my $headers = HTTP::Headers->new;
    $headers->clear();
    $headers->header("mail-to" => $to);
    $headers->header("mail-from" => $from);
    $headers->header("mail-title" => $subject);
    $headers->header("mail-cc" => "shenxiangfeng\@360.cn");
    $headers->header("accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
    $headers->header("accept-language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3");
    $headers->header("accept-encoding" => "gzip, deflate");
    $headers->user_agent("Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0");
    $headers->content_type('text/html; charset=UTF-8');
    $headers->content_type_charset('utf-8');
    
    my $mailUrl = "http://192.168.100.86/test/send.php";
    
    #$message = yutil->gb2312_to_utf8($message);
    $logger->info("message: ".$message);

    my $request = HTTP::Request->new("POST", $mailUrl, $headers, $message);
    $logger->info("content type:".$request->header("content_type"));

    my $ua = LWP::UserAgent->new;
    $ua->timeout(10);
    $ua->request($request);
}


sub sendResult()
{
	my ($result) = @_;
	
	my $headers = HTTP::Headers->new;
    $headers->clear();
    $headers->header("accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
    $headers->header("accept-language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3");
    $headers->header("accept-encoding" => "gzip, deflate");
    $headers->user_agent("Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0");
    $headers->content_type('text/html; charset=UTF-8');
    $headers->content_type_charset('utf-8');
    
    my $mailUrl = "http://192.168.100.86/test/result.php";
    
    $logger->info("message: ".$result);

    my $request = HTTP::Request->new("POST", $mailUrl, $headers, $result);
    $logger->info("content type:".$request->header("content_type"));

    my $ua = LWP::UserAgent->new;
    $ua->timeout(10);
    $logger->info("result server response:\n".$ua->request($request));
}

sub getPathString()
{
    my ($parentUrls) = @_;
    my $result = "";
    foreach my $item(@{$parentUrls})
    {
        $result .= $item." || ";
    }

    return $result;
}

sub testRefrence()
{
    my ($parentUrls) = @_;
    push(@{$parentUrls}, "abc");
}

=pod
/**
 * @brief: 递归检查某个链接是否有问题
 * @note: 返回检查结果是pass还是fail，如果是fail返回原因
 * @author: jianjun guan
 * @date: 2012/08/02
 **/
=cut
sub checkRootUrl()
{
    my ($parentUrls, $rootUrl, $checkDeep) = @_;
    $logger->info("===============Enter checkRootUrl()==============");
    $logger->info("root url:$rootUrl, check deep:$checkDeep");
    
    my $checkResult = {};
    $checkResult->{"result"} = "pass";
    $checkResult->{"path"} = "";
    $checkResult->{"reason"} = "";

    if ($checkDeep <= 0)
    {
        $logger->info("===============Leave checkRootUrl() at step1==============\n");
        return $checkResult;
    }
    
    if (defined($handledUrlDict->{$rootUrl}))
    {
        $logger->info("===============Leave checkRootUrl() because of handled===============\n");
        return $checkResult;
    }
    $handledUrlDict->{$rootUrl} = 1;

    #create http client
    my $ua = LWP::UserAgent->new;
    $ua->timeout(10);
    $ua->agent("Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_0 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8A293 Safari/6531.22.7");
    my $response = $ua->get($rootUrl);
    #$logger->info("response: ".$response->content);
        
    my $code=$response->code;
    my $desc = sprintf("%d: %s", $code, HTTP::Status::status_message($code));
    $logger->info(sprintf("url: %s, desc:%s", $rootUrl, $desc));

    my $result = "fail";
          
    if (!$response->is_success)
    {
        $checkResult->{"result"} = "fail";
        $checkResult->{"reason"} = sprintf("[%s%s] ", &getPathString($parentUrls), $desc);
        $logger->info("===============Leave checkRootUrl() at step2==============\n");
        return $checkResult;
    }
    
    $logger->info("print headers");
    my $mimeType = "";
    foreach my $headerName($response->header_field_names)
    {
        if (yutil->hasSubString($headerName, "Content-Type"))
        {
            $mimeType = $response->header($headerName);
        }
        $logger->info("header: $headerName=".$response->header($headerName));
    }
    if (!yutil->isWebPage($mimeType))
    {
        if (yutil->isAndroidApp($mimeType))
        {
            $checkResult->{"result"} = "fail";
            $checkResult->{"reason"} = sprintf("[%sis android app]", &getPathString($parentUrls));
        }
        $logger->info("===============Leave checkRootUrl() at step3==============\n");
        return $checkResult;
    }

    #$logger->info("content:\n".$response->content);
    my $addedImgeDict = {};
    my $urlItems = ();
    my $content = $response->content;
    while ($content =~ /<a[^>]+href="(http:[^"'=+\[\]]+)"[^>]*>(.*?)<\/a>/igs)
    {
        my $item = {};

        $item->{"url"} = $1;
        $item->{"img"} = "";
        $item->{"name"} = "";

        #$logger->info("find url:$1");
        #$logger->info("name:$2");
        my $subItem = $2;
        if ($subItem =~ /<img[^>]+src="(http:[^"'=+\[\]]+)"[^>]*>([^<]*)/igs)
        {
            $item->{"img"} = $1;
            $subItem = $';
            $addedImgeDict->{$1} = 1;
        }
        $item->{"name"}=yutil->trim($subItem);
        push(@{$urlItems}, $item);
        $content = $';
    }

    #while ($content =~ /<a[^>]+href="(http:[^"'=+\[\]]+)"[^>]*>([^<]*)<\/a>/igs) 
    #{
        #$logger->info("find url:$1");
        #$logger->info("name:$2");
        #push(@{$subUrls}, $1);
        #$content = $';
    #}

    $content = $response->content;
    while ($content =~ /<img[^>]+src="(http:[^"'=+\[\]]+)"[^>]*>([^<]*)/igs)
    {
        $logger->info("find img:$1");
        if (!defined($addedImgeDict->{$1}))
        {
            $addedImgeDict->{$1} = 1;
            my $item = {}; 
            $item->{"url"} = "";
            $item->{"img"} = $1;
            $item->{"name"} = "";
            push(@{$urlItems}, $item);
        }
        $content = $';
    }

    #遍历数组
    foreach my $item(@{$urlItems})
    {
        $logger->info(sprintf("url:%s",$item->{"url"}));
        $logger->info(sprintf("img:%s",$item->{"img"}));
        $logger->info(sprintf("name:%s\n",$item->{"name"}));
        if (length($item->{"url"}) > 0)
        {
            my $tmpParentUrls = yutil->cloneArray($parentUrls);
            push(@{$tmpParentUrls}, $item->{"url"});
            my $temp = &checkRootUrl($tmpParentUrls, $item->{"url"}, $checkDeep-1);
            if ($temp->{"result"} eq "fail")
            {
                $checkResult->{"reason"} .= $temp->{"reason"};
                $checkResult->{"result"} = "fail";
            }
        }
        else
        {
            $logger->error("url is empty");
        }

        if (length($item->{"img"}) > 0)
        {
            my $tmpParentUrls = yutil->cloneArray($parentUrls);
            push(@{$tmpParentUrls}, $item->{"img"});
            my $temp = &checkRootUrl($tmpParentUrls, $item->{"img"}, $checkDeep-1);
            if ($temp->{"result"} eq "fail")
            {
                $checkResult->{"reason"} .= $temp->{"reason"};
                $checkResult->{"result"} = "fail";
            }
        }
        else
        {
            $logger->error("img url is empty");
        }
    }

    $logger->info("===============Leave checkRootUrl() at step4==============\n");
    return $checkResult;
}

sub getMimeType()
{
    my ($destUrl) = @_;
    
    #create http client
    my $ua = LWP::UserAgent->new;
    $ua->timeout(10);
    $ua->agent("Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_0 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8A293 Safari/6531.22.7");
    my $response = $ua->get($destUrl);
    #$logger->info("response: ".$response->content);
        
    my $code=$response->code;
    my $desc = sprintf("%d: %s", $code, HTTP::Status::status_message($code));
    
    if (!$response->is_success)
    {
        $logger->error($desc);
        return "";
    }
    
    my $mimeType = "";
    foreach my $headerName($response->header_field_names)
    {
        if (yutil->hasSubString($headerName, "Content-Type"))
        {
            $mimeType = $response->header($headerName);
        }
        $logger->info("header: $headerName=".$response->header($headerName));
    }
    
    return $mimeType;
}

=pod
/**
 * @brief: entry function
 * @note:
 * @author: jianjun guan
 * @date: 2011/07/24
 **/
=cut
sub main
{    
#save begin test timestamp
    my $beginTime = yutil->getTimeStamp();

    if(!(&init()))
    {
        $logger->error("Error: initialize error");
        return 0;
    }
    
    my $allConfigs = $configReader->getAllConfig();
    my $xmlResult = "";
    $xmlResult ="<?xml version='1.0' encoding='utf-8'?>\n";
    $xmlResult .= sprintf("<testresult execid='scanurl-%s' creator='%s'>\n", $beginTime, yutil->xmlEncode("官建军"));
    $xmlResult .= sprintf("<mail><mail-to>%s</mail-to><mail-from>guanjianjun\@360.cn</mail-from><mail-title>[%s]服务器URL扫描测试</mail-title></mail>\n", $allConfigs->{"mail"}->{"users"}, $beginTime);
#环境配置
    $xmlResult .= "<environment><OS>linux</OS><createbug>false</createbug></environment>\n";
	
    my @proList = @{$configReader->getSectionKeys("products")};
    
	my $totalCount = 0;
	my $passCount = 0;
	my $failCount = 0;
	my $passRate = 0;
	my $tempFile = "accessurl.log";
	
    $xmlResult .= sprintf("<product name='IOS助手-PC版'>\n");
    $xmlResult .= "<username>guanjianjun</username>\n";
    $xmlResult .= "<bug-product>TestProduct</bug-product>\n";
    $xmlResult .= "<bug-reporter>guanjianjun</bug-reporter>\n";
    $xmlResult .= "<bug-assignto>guanjianjun</bug-assignto>\n";

	foreach my $pro(@proList)
	{
		my $proValue = $allConfigs->{"products"}->{$pro};
		
		if ($proValue == 0)
		{
			next;
		}
		
	    $xmlResult .= sprintf("<module name='URL自动扫描/%s'>\n", yutil->xmlEncode($pro));
	    $xmlResult .= "<bug-component>URL自动扫描</bug-component>\n";

        $logger->info("product: $pro");
	    my @keyList = @{$configReader->getSectionKeys($pro)};
	    foreach my $key(@keyList)
	    {
            $logger->info("key: $key");
	        my $url = $allConfigs->{$pro}->{$key};
            $logger->info("url: $url");
	        $key = yutil->xmlEncode($key);
	
	        if (!yutil->hasSubString($url, "http://"))
	        {
	            $url = "http://".$url;
	        }
            $logger->info("root url: $url");
            my $parentUrls = ();
            push (@{$parentUrls}, $url);
	        my $checkResult = &checkRootUrl($parentUrls, $url, $proValue);
	        my $result = $checkResult->{"result"};
	        my $desc = $checkResult->{"reason"};    
	        
	        sleep(3);
	        
	        #添加到xml结果里
	        $xmlResult .= sprintf("<case title='%s'>\n", $key);
	        $xmlResult .= sprintf("<steps>%s</steps>\n", yutil->xmlEncode($url));
	        $xmlResult .= sprintf("<result>%s</result>\n", yutil->xmlEncode($result));
	        $xmlResult .= sprintf("<reason>%s</reason>\n", yutil->xmlEncode($desc));
	        $xmlResult .= "</case>";
	    }   
	    
	    $xmlResult .= "</module>\n";	    
	}
	
	$xmlResult .= "</product>\n</testresult>";
    $logger->info("xmlResult:\n".$xmlResult);

    &sendResult($xmlResult);
}

#execute smart test backend framework 
&main();

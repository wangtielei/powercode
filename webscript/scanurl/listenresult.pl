#! /usr/bin/perl -w

=pod
/**
 * dependents:
 * 1)install Log4perl
 * 2)install some cpan packages, just as use list below
**/
=cut

use strict;
use warnings;
use Cwd;
use yutil;
use yconfigreader;
use ylog4perl;
use yexcel;
use yxlsx;
use LWP::UserAgent;
use MIME::Lite;
use HTTP::Headers;
use HTTP::Request;
use 5.010;

#log printer
our $logger = undef;

#config reader
our $configReader = undef;

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
    #create logger
    # init log4perl
    ylog4perl->initLogger("log4perl.conf");
    # log4perl instance
    $logger = ylog4perl->getLogger("listen");
    
    #create config reader
    $configReader = yconfigreader->new();
    my $confPath = getcwd()."/listen.conf";
    $logger->info($confPath);
    $configReader->loadConfig($confPath);
            
    return 1;
}

=pod
/**
 * @brief: send the request xml file to http
 * @note:
 * @author: jianjun guan
 * @date: 2011/07/24
 **/
=cut
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

=pod
/**
 * @brief: parse the .xls or .xlsx file
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/07/12
 **/
=cut
sub parseExcel()
{
	my ($excelFile) = @_;
	$logger->info("parse excel file $excelFile\n");
	my $excelParser;
	if ($excelFile =~ m/\.xls$/i) {
		$excelParser = yexcel->new();
	}
	elsif ($excelFile =~ m/\.xlsx$/i) {
		$excelParser = yxlsx->new();
	}
	else
	{
	    $logger->info("Does not support $excelFile excel file format now.\n");
	    return;
    }
	if (!$excelParser->loadExcel($excelFile))
	{
	    $logger->info("excel file parse error:\n" . $excelFile);
	    return;
    }
	my $worksheetNum = $excelParser->getWorksheetCount();
	$logger->info("excel file total worksheets number:\n" . $worksheetNum);
	if ($worksheetNum < 1)
	{
		return;
	}
	my @worksheets = $excelParser->getWorksheetNameList();	
	foreach my $worksheet (@worksheets)
	{
		$excelParser->changeWorksheet($worksheet);
		my $rownumber = $excelParser->getMaxRowIndex() + 1;
		my $colnumber = $excelParser->getMaxColIndex() + 1;
		$logger->info(" worksheets : " . $worksheet . " number rows: " . $rownumber . " number cols: " . $colnumber . "\n");
		if ($rownumber < 2)
		{
			return;
		}
		my $row = 0;
		my @titles = $excelParser->readWholeRow($row);		
		my $execTime = yutil->getTimeStamp();
		my $xmlResult = "<?xml version='1.0' encoding='utf-8'?>\n";		
		$xmlResult .= sprintf("<testresult execid='scanurl-%s' creator='%s'>\n", $execTime, yutil->xmlEncode("shenxiangfeng"));
	    $xmlResult .= sprintf("<mail><mail-to>shenxiangfeng\@360.cn</mail-to><mail-from>shenxiangfeng\@360.cn</mail-from><mail-title>[%s]excel file test</mail-title></mail>\n", $execTime);
        $xmlResult .= "<environment><build>build2155</build><OS>linux</OS><createbug>true</createbug><bugdb>bugfree</bugdb></environment>\n";
        # get the product id and module, title etc.        
        my $product = "";
        my $module = "";
        my $submodule = "";
        my $title = "";
        my $step = "";
        my $priority = "";
        my $result = "";
        my $reason = "";
        my $prepare = "";
        $row = 1;
        my @rows = $excelParser->readWholeRow($row);
        for (my $col = 0; $col < $colnumber; $col ++)
		{
			given (lc($titles[$col]))
			{
				when ("product")     { $product = $rows[$col]; $logger->info(" col: $col Product: $rows[$col]"); }
				when ("feature")     { $module = $rows[$col];  $logger->info(" col: $col Feature: $rows[$col]"); }
				when ("sub feature") { $submodule = $col;      $logger->info(" col: $col Sub Feature: $rows[$col]"); }
				when ("title")       { $title = $col;          $logger->info(" col: $col Title: $rows[$col]"); }
				when ("preparation") { $prepare = $col;        $logger->info(" col: $col Preparation: $rows[$col]"); }
				when ("steps")       { $step = $col;           $logger->info(" col: $col Steps: $rows[$col]"); }
				when ("priority")    { $priority = $col;       $logger->info(" col: $col Priority: $rows[$col]"); }
				when ("result")      { $result = $col;         $logger->info(" col: $col Result: $rows[$col]"); }
				when ("reason")      { $reason = $col;         $logger->info(" col: $col Reason: $rows[$col]"); }
			}
		}
		$xmlResult .= sprintf("<product name='%s'>\n", yutil->xmlEncode($product));
		$xmlResult .= sprintf("<module name='%s'>\n",  yutil->xmlEncode($module));
        $xmlResult .= "<username>shenxiangfeng</username>\n";
        $xmlResult .= "<bug-reporter>shenxiangfeng</bug-reporter>\n";
        $xmlResult .= "<bug-assignto>shenxiangfeng</bug-assignto>\n";
	    # process every row 
		for ($row = 1; $row < $rownumber; $row ++)
		{
			@rows = $excelParser->readWholeRow($row);
			# add the case info into xml
	        $xmlResult .= sprintf("<case title='%s'>\n",   yutil->xmlEncode($rows[$title]));	        
	        $xmlResult .= sprintf("<result>%s</result>\n", yutil->xmlEncode($rows[$result]));
		    $xmlResult .= sprintf("<priority>%s</priority>\n", yutil->xmlEncode($rows[$priority]));
		    $xmlResult .= sprintf("<reason>%s</reason>\n", yutil->xmlEncode($rows[$reason]));
		    $xmlResult .= sprintf("<submodule>%s</submodule>\n", yutil->xmlEncode($rows[$submodule]));
		    $xmlResult .= sprintf("<steps>%s</steps>\n",   yutil->xmlEncode($rows[$step]));
		    $xmlResult .= sprintf("<preparation>%s</preparation>\n", yutil->xmlEncode($rows[$prepare]));		       
		    $xmlResult .= sprintf("</case>\n");
		}
		$xmlResult .= "</module>\n</product>";
	    $xmlResult .= "\n</testresult>";
	    # send the result to process, please use next xml file method if it has issue.
	    &sendResult($xmlResult);
=pod
	    # or generate the xml file to process, this method is workaround if above sendResult has issue. 
	    my $allConfig = $configReader->getAllConfig();	    
	    my $folder = $allConfig->{"listen"}->{"folder"};
	    # next line does not work ?
	    #my $newxml = "$folder/$worksheet.xml";
	    my $newxml = "/home/guanjianjun/testresult/$worksheet.xml";
	    my $FOUT;
	    open($FOUT,">","$newxml") or die "Cannot open the newxml file $newxml.\n";
	    print $FOUT $xmlResult;
	    close($FOUT);
=cut
    }
    return ;
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
	
	my $allConfig = $configReader->getAllConfig();
	my $folder = $allConfig->{"listen"}->{"folder"};
	
	$logger->info("listen folder is: ".$folder);
	
	my @fileList = `find $folder -name "*.xml"`;
	
	foreach my $filePath (@fileList)
	{
	    chomp($filePath);
	    $logger->info("file path: $filePath");
	    my $xmlResult = `cat $filePath`;
	    
	    $logger->info("xml content:\n".$xmlResult);
	    &sendResult($xmlResult);	    
	    
	    `rm $filePath`;
	}
	
	
	@fileList = `find $folder -name "*.xls"`;
	
	foreach my $excelFile (@fileList)
	{
	    chomp($excelFile);
	    $logger->info("excel file path: $excelFile");
	    &parseExcel($excelFile);

	    `rm $excelFile`;
	}
	
	@fileList = `find $folder -name "*.xlsx"`;
	
	foreach my $excelFile (@fileList)
	{
	    chomp($excelFile);
	    $logger->info("excel file path: $excelFile");
		&parseExcel($excelFile);
	    
	    `rm $excelFile`;
	}
}

#execute smart test backend framework 
&main();

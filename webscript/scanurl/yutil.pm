#!/usr/bin/perl 

=pod
/***************************************************************************
 * 
 * Copyright (c) 2011 Jianjun Guan, Inc. All Rights Reserved
 * v 1.13 2011/01/25 guanjianjun
 * 
 **************************************************************************/
=cut

package yutil;

use strict; 
use URI::Escape;
#use ExtUtils::Installed;
use Encode;
use utf8;

=pot
/**
 * @brief: constructor function.
 *
 * @return: 
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/01/27 11:59:25
 **/
=cut
sub new 
{    
    my $type = shift;
    
    # init a empty hash table
    my $this = {};
    
    return bless $this, $type;
}


=pot
/**
 * @brief: query all installed perl modules
 *
 * @return: 
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/01/25 11:59:25
 **/
=cut
=pot
sub getInstalledModules()
{
    my $inst= ExtUtils::Installed->new(); 
    my @modules = $inst->modules();
    
    printf("==================all installed package=================\n");
    foreach(@modules) 
    { 
        my $ver = $inst->version($_) || "???"; 
        printf("%-12s -- %s\n", $_, $ver); 
    }  
    
    printf("==================end=================\n"); 
    return 1;
}
=cut

sub getHostName
{
    my ($self) = @_;
    
    my $result = `hostname`;
    chomp($result);
    return $result;
}

=pod
/**
 * @brief: get current folder
 *
 * @return: string
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/02/22
 **/
=cut
sub getPwd
{
    my ($self) = @_;
    my $pwdFolder = `pwd`;
    chomp($pwdFolder);
    return $pwdFolder;
}

=pod
/**
 * @brief: check a file is existing
 *
 * @return: 1-successful, 0-error
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/02/22
 **/
=cut
sub isFileExist
{
    my ($self, $fileName) = @_;
    
    if (-e $fileName)
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

=pod
/**
 * @brief: remove file
 *
 * @return: 1-successful, 0-error
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/02/22
 **/
=cut
sub rmFile
{
    my ($self, $fileName) = @_;
    
    if ($self->isFileExist($fileName))
    {
        my $cmd = "rm $fileName";
        `$cmd`;
    }
}

=pod
/**
 * @brief: check a attribute name is reserve keyword
 *
 * @return: 1-yes, 0-no
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/02/22
 **/
=cut
sub isReserveKey()
{
    my ($self, $keyName) = @_;
    $keyName = lc($keyName);
        
    if ($keyName eq "id" || 
        $keyName eq "name" || 
        $keyName eq "desc" ||
        $keyName eq "failcount" ||
        $keyName eq "passcount")
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

=pot
/**
 * @brief: write data into a file, if file not exist, it create new one.
 *
 * @return: 
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/02/19
 **/
=cut
sub createFile 
{
    my ($self, $contentData, $filePath) = @_;
   
    open(NEWFH, ">$filePath");
    print NEWFH ("$contentData");
    close (NEWFH);
}

=pod
/**
 * @brief: clean a exist file content;
 * @note:
 * @author: jianjun guan
 * @date: 2011/04/14
 **/
=cut
sub clearFile
{
    my ($self, $filePath) = @_;
    open(NEWFH, ">$filePath");
    close (NEWFH);
}

=pod
/**
 * @brief: add '\n' into file
 * @note:
 * @author: jianjun guan
 * @date: 2011/04/14
 **/
=cut
sub appendFileContent
{
    my ($self, $filePath, $content) = @_;
    open(NEWFH, ">>$filePath");
    print NEWFH ("$content");
    close (NEWFH);
}

=pod
/**
 * @brief: add '\n' into file
 * @note:
 * @author: jianjun guan
 * @date: 2011/04/14
 **/
=cut
sub addNewLine
{
    my ($self, $filePath) = @_;
    
    open(NEWFH, ">>$filePath");
    print NEWFH ("\n");
    close(NEWFH);
}

=pot
/**
 * @brief: write xml result file
 *
 * @return: 
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/02/18
 **/
=cut
sub createXmlReportFile
{
    my ($self, $junitResultList, $xmlFile) = @_;
    
    yutil->clearFile($xmlFile);
    
    my @resultList = @{$junitResultList};
    my $total = $#resultList + 1;
    my $passContent = "";
    my $failContent = "";
    my $passCount = 0;
    my $failCount = 0;
    my $errorCount = 0;
    
    for (my $i=0; $i<$total; $i++)
    {
        my $item = $resultList[$i];
        
        if ($item->{"result"} eq "pass")
        {
            $passCount++;
            $passContent .= sprintf("<Test id=\"%d\">\n<Name>%s</Name>\n</Test>\n", $item->{"id"}, $item->{"name"});
        }
        elsif ($item->{"result"} eq "fail")
        {
            $failCount++;
            $failContent .= sprintf("<Test id=\"%d\">\n<Name>%s</Name>\n</Test>\n", $item->{"id"}, $item->{"name"});
        }
        elsif ($item->{"result"} eq "error")
        {
            $errorCount++;
        }
    }
    
    #write to file
    $self->appendFileContent($xmlFile, "<TestRun>\n");
    
    #write fail element
    if (length($failContent) > 0)
    {
        $self->appendFileContent($xmlFile, "<FailedTests>\n");
        $self->appendFileContent($xmlFile, $failContent);
        $self->appendFileContent($xmlFile, "</FailedTests>\n");
    }
    else
    {
        $self->appendFileContent($xmlFile, "<FailedTests/>\n");
    }
    
    #write pass element
    if (length($passContent) > 0)
    {
        $self->appendFileContent($xmlFile, "<SuccessfulTests>\n");
        $self->appendFileContent($xmlFile, $passContent);
        $self->appendFileContent($xmlFile, "</SuccessfulTests>\n");
    }
    else
    {
        $self->appendFileContent($xmlFile, "<SuccessfulTests/>\n");
    }
    
    #write Statistics element    
    $self->appendFileContent($xmlFile, "<Statistics>\n");
    $self->appendFileContent($xmlFile, sprintf("<Tests>%d</Tests>\n", $total));
    $self->appendFileContent($xmlFile, sprintf("<FailuresTotal>%d</FailuresTotal>\n", $failCount));
    $self->appendFileContent($xmlFile, sprintf("<Errors>%d</Errors>\n", $errorCount));
    $self->appendFileContent($xmlFile, sprintf("<Failures>%d</Failures>\n", $failCount));
    $self->appendFileContent($xmlFile, "</Statistics>\n");
    #write end tag
    $self->appendFileContent($xmlFile, "</TestRun>");
}

=pot
/**
 * @brief: find key word from file line by line
 *
 * @return: 
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/02/19
 **/
=cut
sub findContentFromFile
{
    my ($self, $keyword, $filePath) = @_;
    
    my $cmdContent = sprintf("egrep '%s' %s", $keyword, $filePath);
    
    my $resultTxt = `$cmdContent`;
    
    return $resultTxt;
}

=pod
/**
 * @brief: trim a string
 * @note:
 * @author: jianjun guan
 * @date: 2011/04/14
 **/
=cut
sub trim
{
    my ($self, $string) = @_;
    
    $string =~ s/^\s+//;
    $string =~ s/\s+$//;
    return $string;
}

=pod
/**
 * @brief: ltrim a string
 * @note:
 * @author: jianjun guan
 * @date: 2011/04/14
 **/
=cut
sub ltrim
{
    my ($self, $string) = @_;
    $string =~ s/^\s*//;
    return $string;
}

=pod
/**
 * @brief: ltrim a string
 * @note:
 * @author: jianjun guan
 * @date: 2011/04/14
 **/
=cut
sub rtrim
{
    my ($self, $string) = @_;
    $string =~ s/\s*$//;
    return $string;
}

=pod
/**
 * @brief: replace sub string from a string
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub strReplace
{
    my ($self, $string, $pattern, $replacement) = @_;
    $string =~ s/$pattern/$replacement/g;
    
    return $string;
}

=pod
/**
 * @brief: encode xml
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub xmlEncode
{
    my ($self, $string) = @_;
    $string = $self->strReplace($string, "<", "&lt;");
    $string = $self->strReplace($string, ">", "&gt;");
    $string = $self->strReplace($string, "&", "&amp;");
    $string = $self->strReplace($string, "'", "&apos;");
    $string = $self->strReplace($string, "\"", "&quot;");
    return $string;
}

=pod
/**
 * @brief: decode xml
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub xmlDecode
{
    my ($self, $string) = @_;
    $string = $self->strReplace($string, "&lt;", "<");
    $string = $self->strReplace($string, "&gt;", ">");
    $string = $self->strReplace($string, "&amp;", "&");
    $string = $self->strReplace($string, "&apos;", "'");
    $string = $self->strReplace($string, "&quot;", "\"");
    
    return $string;
}

=pod
/**
 * @brief: parse only file name from full path, example, input is "/home/y/var/bin/wlsdump", output is "wlsdump"
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub getFileName
{
    my ($self, $filePath) = @_;
    
    my $fileName = `/bin/basename $filePath`;
    chomp $fileName;
    
    return $fileName;
}

=pod
/**
 * @brief: get current system time stamp, format is:yyyy-mm-dd hh:mm:ss
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub getTimeStamp
{
    my ($self) = @_;
    
    my ($curSec,$curMin,$curHour,$curDay,$curMonth,$curYear) = localtime(time());
    
    return sprintf("%04d-%02d-%02d %02d:%02d:%02d", $curYear+1900, $curMonth+1, $curDay, $curHour, $curMin, $curSec);
}

=pod
/**
 * @brief: get current system time string, format is:yyyymmdd-hhmmss
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub getTimeString
{
    my ($self) = @_;
    
    my ($curSec,$curMin,$curHour,$curDay,$curMonth,$curYear) = localtime(time());
    
    return sprintf("%04d%02d%02d-%02d%02d%02d", $curYear+1900, $curMonth+1, $curDay, $curHour, $curMin, $curSec);
}

=pod
/**
 * @brief: append a string to dest string, same as string3=string1+string2
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub appendString
{
    my ($self, $dest, $src) = @_;
    
    return "$dest\n$src";
}

=pod
/**
 * @brief: calc two time stamp different
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub calcTimeSpan
{
    my ($self, $begTime, $endTime) = @_;
    
    my $timeSpan = $endTime - $begTime;
    
    my $hours = $timeSpan/3600;
    
    $timeSpan = $timeSpan%3600;
    my $minutes = $timeSpan/60;
    
    #second
    $timeSpan = $timeSpan%60;
    
    my $result =
    {
        "hours" => $hours,
        "minutes" => $minutes,
        "seconds" => $timeSpan
    };
    
    return $result;    
}

=pod
/**
 * @brief: read file line by line
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub readFilebyLine
{
    my ($self, $fileName) = @_;
    my $allContent = `cat $fileName`;
    my @lines = split('\n', $allContent);
    my @validLines = ();
    
    foreach my $line (@lines)
    {
        $line = ltrim($line);
        
        if (length($line) > 0 && substr($line, 0, 1) ne "#")
        {
            push(@validLines, $line);
        }
    }
    
    return @validLines;
}

=pod
/**
 * @brief: get whole file content.
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/08
 **/
=cut
sub getFileContent
{
    my ($self, $fileName) = @_;
    my $allContent = `cat $fileName`;
    return $allContent;    
}

=pod
/**
 * @brief: 
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/08
 **/
=cut
sub readFileContent
{
	my ($self, $fileName) = @_;
	open(FILE, $fileName);
	
	my $allContent = "";
	my $record;
	while($record=<FILE>)
	{
		$allContent .= $record;
	}

	close(FILE);
	
	return $allContent;
}

=pod
/**
 * @brief: check a sub string is in string, if yes, return 1; or return 0;
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub hasSubString
{
    my ($self, $string, $pattern) = @_;
    
    if ($string =~ m/$pattern/i)
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

=pod
/**
 * @brief: encode url
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub urlEncode
{
    my ($self, $string) = @_;
    return uri_escape($string);
}

=pod
/**
 * @brief: execute shell command, and return console output
 * @note:
 * @author: jianjun guan
 * @date: 2011/08/03
 **/
=cut
sub execShellCmd
{
    my ($self, $cmd) = @_;
    
    printf("cmd:%s\n", $cmd);
    my $result = `$cmd`;
    printf("result:%s, pwd:%s\n", $result, $self->getPwd());
    return $result;
}

=pod
/**
 * @brief: add leaf
 * @note:
 * @author: jianjun guan
 * @date: 2011/05/03
 **/
=cut
sub addLeave
{
    my ($self, $xmlWriter, $tagName, $value) = @_;
    
    $xmlWriter->startTag($tagName);
    $xmlWriter->characters($value);
    #$xmlWriter->characters($self->xmlDecode($value));
    $xmlWriter->endTag($tagName);    
}

=pod
/**
 * @brief: add leaf with color
 * @note:
 * @author: jianjun guan
 * @date: 2011/05/03
 **/
=cut
sub addLeaveWithColor
{
    my ($self, $xmlWriter, $tagName, $value, $color) = @_;
    
    $xmlWriter->startTag($tagName, "color" => $color);
    #$xmlWriter->characters($self->xmlEncode($value));
    $xmlWriter->characters($value);
    $xmlWriter->endTag($tagName);    
}

sub isSupportThread
{
    my ($self) = @_;
    my $threadFlag = "useithreads=define";
    
    my $threadInfo = `perl -V|grep use.*threads`;
    
    return $self->hasSubString($threadInfo, $threadFlag);
}

sub findLinebyKeyword
{
    my ($self, $content, $keyword) = @_;
    my @lines = split('\n', $content);
    foreach my $line (@lines)
    {  
        if ($self->hasSubString($line, $keyword))
        {
            return $line;
        }
    }
    
    return "";
}

sub gb2312_to_utf8()
{
    my ($self, $src) = @_;
    return encode("utf-8", decode("gb2312", $src));
}

sub utf8_to_gb2312()
{
    my ($self, $src) = @_;
    return encode("gb2312", decode("utf-8", $src));
}

sub isWebPage()
{
    my ($self, $mimetype) = @_;
    
    if ($self->hasSubString($mimetype, "text/html"))
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

sub isAndroidApp()
{
    my ($self, $mimetype) = @_;

    if ($self->hasSubString($mimetype, "android"))
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

sub cloneArray()
{
    my ($self, $srcArray) = @_;
    my $destArray = ();

    @{$destArray} = @{$srcArray};

    return $destArray;
}

1;

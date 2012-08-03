#!/usr/bin/perl 

=pod
/***************************************************************************
 * 
 * Copyright (c) 2011 Jianjun Guan, Inc. All Rights Reserved
 * v 1.13 2011/04/07 guanjianjun
 * 
 **************************************************************************/
=cut

=pod

=pod
* Description:this module wrapper log4perl lib
* Author: jianjung
* Create Date: 2011/04/07
* Update Date: 2011/04/07
* Dependency: pan/perl-Log-Log4perl

=cut

package ylog4perl;

use strict; 
use Log::Log4perl::Level;
use Log::Log4perl qw(:easy);

=pod
/**
 * @brief: init logger function
 *
 * @return: 
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/01/25 11:59:25
 **/
=cut
sub initLogger()
{
    my ($self, $confFile) = @_;
    
    printf("enter init(), CLASS=$self, conf_file=$confFile\n");
    
    # init log4perl
    Log::Log4perl->init($confFile);    
    
    printf("leave init().\n");
}

=pod
/**
 * @brief: create new logger instance
 *
 * @return: 
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/01/25 11:59:25
 **/
=cut
sub getLogger()
{
    my ($self, $loggerName) = @_;
    
    printf("enter getLogger(), CLASS=$self, logger_name=$loggerName\n");
    
    my $log = Log::Log4perl::get_logger($loggerName);
    
    printf("leave getLogger().\n");
    return $log;
}

1;


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
* Description:this module simulate log4perl, with it server does not need pan/perl-Log-Log4perl package.
* Author: jianjung
* Create Date: 2011/04/07
* Update Date: 2011/04/07
* Dependency: 
=cut

package ylogger;

use strict; 

sub new 
{
    my ($class) = @_;
    
    #default is level
    my $self = 
    {
        "TRACE" => 0,
        "DEBUG" => 1,
        "INFO" => 2,
        "WARN" => 3,
        "ERROR" => 4,
        "FATAL" => 5,
        "curlevel" => 1
    };
    
    bless $self,$class;
    return $self;
}

sub setLevel
{
    my ($self, $level) = @_;
    
    my $value = 0;
    if ($level eq "TRACE")
    {
        $value = 0;
    }
    elsif ($level eq "DEBUG")
    {
        $value = 1;
    }
    elsif ($level eq "INFO")
    {
        $value = 2;
    }
    elsif ($level eq "WARN")
    {
        $value = 3;
    }
    elsif ($level eq "ERROR")
    {
        $value = 4;
    }
    elsif ($level eq "FATAL")
    {
        $value = 5;
    }
    else
    {
        $value = 100;
    }
    
    $self->{"curlevel"} = $value;
}

sub getLevel
{
    my ($self) = @_;
    
    my $value = $self->{"curlevel"};
    my $level = "";
    if ($value == 0)
    {
        $level = "TRACE";
    }
    elsif ($value == 1)
    {
        $level = "DEBUG";
    }
    elsif ($value == 2)
    {
        $level = "INFO";
    }
    elsif ($value == 3)
    {
        $level = "WARN";
    }
    elsif ($value == 4)
    {
        $level = "ERROR";
    }
    elsif ($value == 5)
    {
        $level = "FATAL";
    }
    else
    {
        $level = "";
    }
    
    return $level;
}

sub trace
{
    my ($self, $info) = @_;
    
    if ($self->{"curlevel"} > $self->{"TRACE"})
    {
        return;
    }
    
    print("TRACE - ".$info."\n");
}

sub debug
{
    my ($self, $info) = @_;
    
    if ($self->{"curlevel"} > $self->{"DEBUG"})
    {
        return;
    }
    
    print("DEBUG - ".$info."\n");
}

sub info
{
    my ($self, $info) = @_;
    
    if ($self->{"curlevel"} > $self->{"INFO"})
    {
        return;
    }
    
    print("INFO - ".$info."\n");
}

sub warn
{
    my ($self, $info) = @_;
    
    if ($self->{"curlevel"} > $self->{"WARN"})
    {
        return;
    }
    
    print("WARN - ".$info."\n");
}

sub error
{
    my ($self, $info) = @_;
    
    if ($self->{"curlevel"} > $self->{"ERROR"})
    {
        return;
    }
    
    print("ERROR - ".$info."\n");
}

sub fatal
{
    my ($self, $info) = @_;
    
    if ($self->{"curlevel"} > $self->{"FATAL"})
    {
        return;
    }
    
    print("FATAL - ".$info."\n");
}

1;

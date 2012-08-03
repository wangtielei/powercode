#! /usr/bin/perl

=pod
/***************************************************************************
 * 
 * Copyright (c) 2011 Jianjun Guan, Inc. All Rights Reserved
 * v 1.13 2011/01/25 guanjianjun
 * Dependence: Config::IniFiles
 **************************************************************************/
=cut

package yconfigreader;

use strict;
use Cwd;
use yutil;
use Config::IniFiles;
use ylogger;

# logger
our $logger = undef;

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

sub new {
	$logger = ylogger->new();
	$logger->debug("enter new()");

	my $type = shift;

	# init a empty hash table
	my $this = {};

	$logger->debug("leave new()");
	return bless $this, $type;
}

=pot
/**
 * @brief: load configuration from .conf file.
 *
 * @return: 
 * @retval   
 * @param[in]: conf file path 
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/01/27 11:59:25
 **/
=cut

sub loadConfig {
	my ( $self, $confFile ) = @_;

	$logger->debug("self=$self, confFile=$confFile");

	# load configuration
	# my $cfg = Config::IniFiles->new( -file => $confFile );

	my %ini;
	tie %ini, 'Config::IniFiles', ( -file => $confFile );

	#set config data into $self
	$self->{"iniContent"} = {%ini};

    while( my ($sectionName, $items) = each(%ini))
    {
        $logger->debug("section = ".$sectionName);
        $self->{$sectionName} = ();

        while( my ($itemKey, $itemValue) = each(%$items))
        {
            $logger->debug($itemKey." = ".$itemValue);
            push(@{$self->{$sectionName}}, $itemKey);
        }
    }

	$logger->debug("leave loadConfig()");
}

=pot
/**
 * @brief: query configuration item by config name.
 *
 * @return: 
 * @retval   
 * @param[in]: section name;
 * @param[in]: key name;
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/01/27 11:59:25
 **/
=cut

sub getConfig {
	my ( $self, $sectionName, $itemName ) = @_;
	my $value = $self->{"iniContent"}->{$sectionName}->{$itemName};

	my $first = substr( $value, 0, 1 );
	my $last  = substr( $value, -1 );

	if ( $first eq '"' && $last eq '"' ) {
		$value = substr( $value, 1, length($value) - 2 );
	}
	return $value;
}

sub getAllConfig {
	my ($self) = @_;

	return $self->{"iniContent"};
}

sub getSectionKeys
{
    my ($self, $session) = @_;
    return $self->{$session};
}

=pot
/**
 * @brief: some of conf file has no session, this function just read it line by line and save as hash
 *
 * @author: jianjun guan
 * @date: 2011/01/27 11:59:25
 **/
=cut

sub loadSimpleSetToHash {
	my ( $self, $filePath ) = @_;
	my $setHash = {};

	my $allContent = `cat $filePath`;

	my @lines = split( '\n', $allContent );

	foreach my $line (@lines) {
		my $line = yutil->trim($line);
		my $firstChar = substr( $line, 0, 1 );
		if ( $firstChar eq "#" || ( index( $line, "=" ) <= 0 ) ) {
			next;
		}
		my $firstPos = index( $line, "=" );

		if ( $firstPos == ( length($line) - 1 ) ) {
			next;
		}

		my $key = lc( substr( $line, 0, $firstPos ) );
		my $value = substr( $line, $firstPos + 1 );

		$key             = yutil->trim($key);
		$value           = yutil->trim($value);
		$setHash->{$key} = $value;
	}

	return $setHash;
}

# return 1 for whole module
1;

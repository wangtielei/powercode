#!/usr/bin/perl 

=pod
/***************************************************************************
 * 
 * Copyright (c) 2011 Jianjun Guan, Inc. All Rights Reserved
 * v 1.13 2011/04/19 guanjianjun
 * Dependency: Spreadsheet::ParseExcel
 **************************************************************************/
=cut


package yexcel;

use yutil;
use ylogger;
use Spreadsheet::ParseExcel;

our $logger = undef;

=pot
/**
 * @brief: create new excel parser instance
 *
 * @return: 
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub new()
{
    my $class = shift();
    $logger = ylogger->new();
    $logger->setLevel("ERROR");
    my $self =
    {
        "filepath" => "",
        "workbook" => undef,
        "worksheet" => undef,
    };
    
    bless $self, $class;
    return $self;
}

=pot
/**
 * @brief: load excel file and set first worksheet as default sheet
 *
 * @return: 1--successful, 0--failed
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub loadExcel
{
    my ($self, $filePath) = @_;
    $self->{"filepath"} = $filePath;
    my $parser   = Spreadsheet::ParseExcel->new();
    my $workbook = $parser->parse($filePath);
    if (!defined($workbook)) 
    {
        return 0;
    }
    
    #store workbook
    $self->{"workbook"} = $workbook;
    
    #set first worksheet as default sheet
    for my $worksheet ($workbook->worksheets()) 
    {
        $self->{"worksheet"} = $worksheet;
        last;
    }
    
    return 1;
}

=pot
/**
 * @brief: change current worksheet
 *
 * @return: 1--successful, 0--failed
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub changeWorksheet
{
    my ($self, $sheetName) = @_;
    
    for my $worksheet ($self->{"workbook"}->worksheets()) 
    {
        if ($worksheet->get_name() eq $sheetName)
        {
            $self->{"worksheet"} = $worksheet;
            return 1;
        }
    }
    
    return 0;
}

=pot
/**
 * @brief: get work sheet count
 *
 * @return: worksheet count
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub getWorksheetCount
{
    my ($self) = @_;
        
    my @worksheets = $self->{"workbook"}->worksheets();
    return ($#worksheets+1);
}

=pot
/**
 * @brief: get work sheet name list
 *
 * @return: worksheet name list
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub getWorksheetNameList
{
    my ($self) = @_;
        
    my @nameList = ();
    for my $worksheet ($self->{"workbook"}->worksheets()) 
    {
        push(@nameList, $worksheet->get_name());
    }
    
    return @nameList;
}

=pot
/**
 * @brief: read one cell
 *
 * @return: cell
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub getCell
{
    my ($self, $row, $col) = @_;
    my $worksheet = $self->{"worksheet"};

    if ($row < 0 || $col < 0 || !defined($worksheet))
    {
        return undef;
    }
    
    my $cell = $worksheet->get_cell($row, $col);
    return $cell;
}

=pot
/**
 * @brief: read one cell's text
 *
 * @return: string
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub readCell
{
    my ($self, $row, $col) = @_;
    my $worksheet = $self->{"worksheet"};

    if ($row < 0 || $col < 0 || !defined($worksheet))
    {
        return undef;
    }
    
    my $cell = $worksheet->get_cell($row, $col);
    if (!defined($cell))
    {
        return undef;
    }
    else
    {
        return $cell->value();
    }
}

=pot
/**
 * @brief: read whole row data. Reading will stop once reach a empty
 *         cell
 *
 * @return: string array
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub readWholeRow
{
    my ($self, $row) = @_;
    my $worksheet = $self->{"worksheet"};
    my @rowData = ();
    if ($row < 0 || !defined($worksheet))
    {
        return @rowData;
    }
    
    for (my $col=0; ; $col++)
    {
        my $cell = $worksheet->get_cell($row, $col);
        if (!defined($cell))
        {
            last;
        }
        push(@rowData, $cell->value());
    }
    
    return @rowData;
}

=pot
/**
 * @brief: read row data according to special range
 *
 * @return: string array
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub readRow
{
    my ($self, $row, $colBeg, $colEnd) = @_;
    my $worksheet = $self->{"worksheet"};
    my @rowData = ();
    if ($row < 0 || !defined($worksheet))
    {
        return @rowData;
    }
    
    for (my $col=$colBeg; $col <= $colEnd; $col++)
    {
        my $cell = $worksheet->get_cell($row, $col);
        if (!defined($cell))
        {
            push(@rowData, "");
        }
        else
        {
            push(@rowData, $cell->value());
        }        
    }
    
    return @rowData;
}

=pot
/**
 * @brief: read whole Col data. Reading will stop once reach a empty
 *         cell
 *
 * @return: string array
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub readWholeCol
{
    my ($self, $col) = @_;
    my $worksheet = $self->{"worksheet"};
    my @colData = ();
    if ($col < 0 || !defined($worksheet))
    {
        return @colData;
    }
    
    for (my $row=0; ; $row++)
    {
        my $cell = $worksheet->get_cell($row, $col);
        if (!defined($cell))
        {
            last;
        }
        else
        {
            push(@colData, $cell->value());
        }        
    }
    
    return @colData;
}

=pot
/**
 * @brief: read col data according to special range
 *
 * @return: string array
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub readCol
{
    my ($self, $col, $rowBeg, $rowEnd) = @_;
    my $worksheet = $self->{"worksheet"};
    my @colData = ();
    if ($col < 0 || $rowBeg>$rowEnd || $rowBeg < 0 || !defined($worksheet))
    {
        return @colData;
    }
    
    for (my $row=$rowBeg; $row <= $rowEnd; $row++)
    {
        my $cell = $worksheet->get_cell($row, $col);
        if (!defined($cell))
        {
            push(@colData, "");
        }
        else
        {
            push(@colData, $cell->value());
        }        
    }
    
    return @colData;
}

=pot
/**
 * @brief: get Min row index
 *
 * @return: min row index
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub getMinRowIndex
{
    my ($self) = @_;
    my $worksheet = $self->{"worksheet"};
    if (!defined($worksheet))
    {
        return 0;
    }
    
    my ( $row_min, $row_max ) = $worksheet->row_range();
    
    return $row_min;    
}

=pot
/**
 * @brief: get Max row index
 *
 * @return: max row index
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub getMaxRowIndex
{
    my ($self) = @_;
    my $worksheet = $self->{"worksheet"};
    if (!defined($worksheet))
    {
        return 0;
    }
    
    my ( $row_min, $row_max ) = $worksheet->row_range();
    
    return $row_max;    
}

=pot
/**
 * @brief: get Min col index
 *
 * @return: Min col index
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub getMinColIndex
{
    my ($self) = @_;
    my $worksheet = $self->{"worksheet"};
    if (!defined($worksheet))
    {
        return 0;
    }
    
    my ( $col_min, $col_max ) = $worksheet->col_range();
    
    return $col_min;    
}

=pot
/**
 * @brief: get Max col index
 *
 * @return: max col index
 * @retval   
 * @see 
 * @note:
 * @author: jianjun guan
 * @date: 2011/06/02 11:59:25
 **/
=cut
sub getMaxColIndex
{
    my ($self) = @_;
    my $worksheet = $self->{"worksheet"};
    if (!defined($worksheet))
    {
        return 0;
    }
    
    my ( $col_min, $col_max ) = $worksheet->col_range();
    
    return $col_max;    
}
    
1;

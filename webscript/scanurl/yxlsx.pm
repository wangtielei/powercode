#!/usr/bin/perl 

=pod
/***************************************************************************
 * 
 * Copyright (c) 2012 xiangfeng shen, Inc. All Rights Reserved
 * v 1.0 2012/07/13 shenxiangfeng@360.cn
 * Dependency: Spreadsheet::XLSX
 **************************************************************************/
=cut

package yxlsx;

use yutil;
use ylogger;
use Spreadsheet::XLSX;

our $logger = undef;

=pot
/**
 * @brief: create new excel parser instance
 * @return: 
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
 * @return: 1--successful, 0--failed
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
 **/
=cut
sub loadExcel
{
    my ($self, $filePath) = @_;
    $self->{"filepath"} = $filePath;
    my $workbook = Spreadsheet::XLSX->new($filePath);
    if (!defined($workbook)) 
    {
        return 0;
    }

    #store workbook
    $self->{"workbook"} = $workbook;

    #set first worksheet as default sheet
    foreach my $worksheet (@{$workbook->{Worksheet}})
    {
        $self->{"worksheet"} = $worksheet;
        last;
    }

    return 1;
}

=pot
/**
 * @brief: change current worksheet
 * @return: 1--successful, 0--failed
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
 **/
=cut
sub changeWorksheet
{
    my ($self, $sheetName) = @_;
    
    foreach my $worksheet ($self->{"workbook"}->worksheets()) 
    {
        if ($worksheet->{Name} eq $sheetName)
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
 * @return: worksheet count
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
 * @return: worksheet name list
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
 **/
=cut
sub getWorksheetNameList
{
    my ($self) = @_;
        
    my @nameList = ();
    foreach my $worksheet ($self->{"workbook"}->worksheets()) 
    {
        push(@nameList, $worksheet->{Name});
    }
    
    return @nameList;
}

=pot
/**
 * @brief: read one cell
 * @return: cell
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
    
    my $cell = $worksheet->{Cells}[$row][$col];
    return $cell;
}

=pot
/**
 * @brief: read one cell's text
 * @return: string
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
    
    my $cell = $worksheet->{Cells}[$row][$col];
    if (!defined($cell))
    {
        return undef;
    }
    else
    {
        return $cell->{Val};
    }
}

=pot
/**
 * @brief: read whole row data. Reading will stop once reach a empty cell
 * @return: string array
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
        my $cell = $worksheet->{Cells}[$row][$col];
        if (!defined($cell))
        {
            last;
        }
        push(@rowData, $cell->{Val});
    }
    
    return @rowData;
}

=pot
/**
 * @brief: read row data according to special range
 * @return: string array
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
        my $cell = $worksheet->{Cells}[$row][$col];
        if (!defined($cell))
        {
            push(@rowData, "");
        }
        else
        {
            push(@rowData, $cell->{Val});
        }        
    }
    
    return @rowData;
}

=pot
/**
 * @brief: read whole Col data. Reading will stop once reach a empty cell
 * @return: string array
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
        my $cell = $worksheet->{Cells}[$row][$col];
        if (!defined($cell))
        {
            last;
        }
        else
        {
            push(@colData, $cell->{Val});
        }
    }
    
    return @colData;
}

=pot
/**
 * @brief: read col data according to special range
 * @return: string array
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
            push(@colData, $cell->{Val});
        }        
    }
    
    return @colData;
}

=pot
/**
 * @brief: get Min row index
 * @return: min row index
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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

    return $worksheet->{MinRow};    
}

=pot
/**
 * @brief: get Max row index
 * @return: max row index
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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

    return $worksheet->{MaxRow};    
}

=pot
/**
 * @brief: get Min col index
 * @return: Min col index
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
    
    return $worksheet->{MinCol};    
}

=pot
/**
 * @brief: get Max col index
 * @return: max col index
 * @retval   
 * @see 
 * @note:
 * @author: xiangfeng shen
 * @date: 2012/06/02 11:59:25
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
    
    return $worksheet->{MaxCol};    
}
    
1;

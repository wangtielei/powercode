#! /usr/bin/perl

#add alias into your .bashrc file, like this
# alias pushcode="perl ~/code/powercode/perl/pushcode.pl"
#
use strict;
use warnings;
use Cwd;

our $paramHash = {};

sub parseCommandLine
{
    my (@argArray) = @_;
    my $key = undef;
    my $value = undef;

    foreach my $item (@argArray)
    {
        if (substr($item, 0, 1) eq "-")
        {
            my $pos = rindex($item, "-");
            $key = substr($item, $pos+1);

            if (length($key) > 0)
            {
                $paramHash->{$key} = "";
            }
        }
        else
        {
            if (defined($key))
            {
                $paramHash->{$key} = $item;
            }

            $key = undef;
        }
    }
}

sub main
{
    &parseCommandLine(@ARGV);

    #git remote add helloworld git@github.com:powerguan/powercode.git
    #git push -u helloworld master
    if (defined($paramHash->{"job"}))
    {
        my $job = $paramHash->{"job"};
        print("job name: $job\n");
        #my $cmd = sprintf("git remote add '%s' git\@github.com:powerguan/powercode.git", $job);
        my $cmd = sprintf("git remote add -f -t master -m master %s git\@github.com:powerguan/powercode.git", $job);
        print("cmd: $cmd\n");
        system($cmd);

        #$cmd = sprintf("git push -u %s master", $job);
        $cmd = sprintf("git push git\@github.com:powerguan/powercode.git");
        print("cmd: $cmd\n");
        system($cmd);
    }
    else
    {
        print("没有指定job名称，请使用 '-job xxx' 指定提交名称\n");
    }
}

&main();

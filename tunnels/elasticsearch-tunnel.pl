#!/usr/bin/perl

# ElasticSearch Tunnel Tool for MacOS X and Linux
# Forked from CouchDB Tunnel Tool for MacOS X
# Copyright (c) 2010 Linode, LLC
# Author: Philip C. Paradis <pparadis@linode.com>
# Modifications: Sam Kleinman <sam@linode.com>
# Modifications: Mike Joseph <mike@statenews.com>
# Usage: ElasticSearch-tunnel.pl [start|stop]
# Access a ElasticSearch instance by way of an SSH tunnel.
# Only use for testing and stage environments, NEVER production.

##
## Edit these values to reflect the authentication credentials for the
## server running the ElasticSearch instance with which you wish to
## connect. If you have chosen to run ElasticSearch on an alternate port,
## modify the `$remote_port` value. You should not need to modify the
## `$remote_ip` value.
##

$remote_user = "USERNAME";
$remote_host = "SERVER";
$remote_port = "9200";
$remote_ip   = "127.0.0.1";

##
## Modify these values only if you are running a local ElasticSearch
## instance.
##

$local_ip    = "127.0.0.1";
$local_port  = "9200";

##
## You do not need to edit this file beyond this point.
##

$a = shift;
$a =~ s/^\s+//;
$a =~ s/\s+$//;

$pid=`ps ax|grep ssh|grep $local_port|grep $remote_port`;
$pid =~ s/^\s+//;
@pids = split(/\n/,$pid);
foreach $pid (@pids)
{
 if ($pid =~ /ps ax/) { next; }
 split(/ /,$pid);
}

if (lc($a) eq "start")
{
 if ($_[0]) { print "ElasticSearch tunnel already running.\n"; exit 1; }
 else
 {
  system "ssh -f -L $local_ip:$local_port:$remote_ip:$remote_port $remote_user\@$remote_host -N";
  exit 0;
 }
}
elsif (lc($a) eq "stop")
{
 if ($_[0]) { kill 9,$_[0]; exit 0; }
 else { exit 1; }
}
else
{
 print "Usage: elasticsearch-tunnel.pl [start|stop]\n";
 exit 1;
}

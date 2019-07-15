<?php
/**
 * @framework       MailTng Framework
 * @version         1.1
 * @author          MailTng Team
 * @copyright       Copyright (c) 2015 - 2016.	
 * @license		
 * @link	
 */ 
require_once '/usr/mailtng/scripts/helpers/main.php';
/**
 * @name            move.php 
 * @description     moves all accounting files 
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */

# get the current date 
$date = @date('Y-m-d');

# delivery
$folder = "/etc/pmta/delivered/moved/$date/";

$files = cmd("find /etc/pmta/delivered/archived/d-$date-* -exec awk 'END { if (NR > 1) print FILENAME }' {} \;",true);

if(count($files) && key_exists('output', $files) && count($files['output']))
{
    $command = "mkdir -p $folder; mv -t $folder ";

    foreach ($files['output'] as $file)
    {
        $command .= trim($file) . " ";
    }
    
    cmd($command);
}

# bounces 
$folder = "/etc/pmta/bounces/moved/$date/";

$files = cmd("find /etc/pmta/bounces/archived/b-$date-* -exec awk 'END { if (NR > 1) print FILENAME }' {} \;",true);

if(count($files) && key_exists('output', $files) && count($files['output']))
{
    $command = "mkdir -p $folder; mv -t $folder ";

    foreach ($files['output'] as $file)
    {
        $command .= trim($file) . " ";
    }
    
    cmd($command);
}

# remove empty files 
cmd("rm -rf /etc/pmta/bounces/archived/*");
cmd("rm -rf /etc/pmta/delivered/archived/*");

# finish process
die('Operation Finished!');
<?php
date_default_timezone_set('Europe/London');
$prefix = $argc > 1 ? $argv[1] : 'lld_';
$clientsFolderName = $argc > 2 ? $argv[2] : 'clients';
$prefixLen = strlen($prefix);
$thisDir = dirname(__FILE__);
$scanDir = dirname($thisDir);
$scanDirLen = strlen($scanDir);

// build a list of locations to scan
$rit = new RecursiveDirectoryIterator($scanDir);
$ritit = new RecursiveIteratorIterator($rit, RecursiveIteratorIterator::SELF_FIRST);
$toScan = array();
foreach($ritit as $path => $splFileInfo){
    $depth = $ritit->getDepth();
    $isDir = is_dir($path);
    $baseName = $splFileInfo->getBasename();
    if($path == $thisDir
                || !$isDir
                || $baseName == '.' 
                || $baseName == '..' 
                || $depth != 0  
                || ($prefixLen && strpos($baseName, $prefix) !== 0) 
                || !file_exists($path . "/.git")
                ){
        continue;		
    }
    $toScan[] = $path;
}

// functions to work with the output array
$output = array();
function addClient($client) {
    global $output;
    if(!array_key_exists($client, $output)){
        $output[$client] = array();
    }
}
function addJob($client, $job) {
    global $output;
    if(!array_key_exists($job, $output[$client])){
        $output[$client][$job] = array();
    }
}
function addDevJob($client, $dev, $job, $stamp) {
    global $output;
    addJob($client, $job);
    if(!array_key_exists($dev, $output[$client][$job])){
        $output[$client][$job][$dev] = $stamp;
    }
}

// interate through scan locations. pack the results into output array
foreach($toScan as $scanPath){
    $scanPathBaseName = basename($scanPath);
    $dev = substr($scanPathBaseName, $prefixLen);
    $projectsPath = $scanPath . '/' . $clientsFolderName;
    $rit = new RecursiveDirectoryIterator($projectsPath);
    $ritit = new RecursiveIteratorIterator($rit, RecursiveIteratorIterator::SELF_FIRST);
    $client = '';
    $job = '';
    foreach($ritit as $path => $splFileInfo){
        $depth = $ritit->getDepth();
        $isDir = is_dir($path);
        $baseName = $splFileInfo->getBasename();
        if($baseName == '.' || $baseName == '..' || !$isDir || $depth > 1){
            continue;
        }
        switch($depth){
            case 0:
                // client folder
                $client = $baseName;
                addClient($client);
                break;
            case 1:
                // job folder
                $job = $baseName;
                $stamp = date("YmdGis", $splFileInfo->getMTime());
                addDevJob($client, $dev, $job, $stamp);
                break;
        }
    }
}

// delete the _scan/clients folder
$scanClientsDir = $thisDir . '/clients';
if(file_exists($scanClientsDir)){
    system("rm -rf ".escapeshellarg($scanClientsDir));
}

// and (re)create it
mkdir($scanClientsDir);

// using the output array, create the folders as needed
foreach($output as $client => $jobs){
    $clientDir = $scanClientsDir . '/' . $client;
    mkdir($clientDir);
    foreach($jobs as $job => $devs){
        $jobDir = $clientDir . '/' . $job;
        mkdir($jobDir);
        foreach($devs as $dev => $stamp) {
            $devLink = $jobDir . '/' . $stamp . '-' . $dev;
            if(!is_link($devLink)){
                $target = $scanDir . '/' . $prefix . $dev . '/' . $clientsFolderName . '/' . $client . '/' . $job;
                symlink($target, $devLink);
            }
        }
    }
}


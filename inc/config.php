<?php
/**
*   File: Default Configuration
*   Author: https://twitter.com/inevitable360 and all #Dogecoin friends and familly helped will try to find a way to put all names eheh!
*   Description: Real use case of the dogecoin.com CORE Wallet connected by RPC Calls using Old School PHP Coding with easy to learn steps (I hope lol)
*   License: Well, do what you want with this, be creative, you have the wheel, just reenvent and do it better! Do Only Good Everyday
*/
    //ini_set('display_errors', 1);// to debug uncomment this~

    // Add your Data Base credentials here!
    $config["dbhost"] = "localhost";  // put here you database adress
    $config["dbname"] = ""; // your DB name
    $config["dbuser"] = ""; // your DB username
    $config["dbpass"] = ""; // your DB password

    // Add your Dogecoin Core Node credentials here!
    $config["rpcuser"] = "";
    $config["rpcpassword"] = "";
    $config["dogecoinCoreProtocol"] = "http://";
    $config["dogecoinCoreServer"] = "localhost";
    $config["dogecoinCoreServerPort"] = 22555; 

    // Global Sanityse
    $_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
    $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    
    // include functions
    require_once('vendors/dogecoinRPCBridge.php');
    include("functions.php");
?>
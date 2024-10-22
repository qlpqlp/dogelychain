<?php
/**
 * File: functions
 * Author: https://twitter.com/inevitable360 and all #Dogecoin friends
 * License: Free to use, modify, and share
 * Description: Dogecoin CORE Wallet RPC connected using PHP and PDO
 */

// Database connection using PDO
try {
    $pdo = new PDO(
        'mysql:host=' . $config["dbhost"] . ';dbname=' . $config["dbname"], 
        $config["dbuser"], 
        $config["dbpass"], 
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    echo '<br>DB Error: ' . $e->getMessage() . '<br><br>';
    echo 'This page will auto-refresh in 5 seconds.';
    header("Refresh:5");
    exit();
}

// Class for interacting between DB and Dogecoin Core RPC
class DogeBridge {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Helper function to execute prepared queries
    private function executeQuery($query, $params = []) {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    public function addCoinBase($txid, $coinbase, $tag, $sequence, $time, $blocktime, $json) {
        $query = "INSERT INTO `coinbase` (`txid`, `coinbase`, `tag`, `sequence`, `time`, `blocktime`, `json`, `date`) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->executeQuery($query, [$txid, $coinbase, $tag, $sequence, $time, $blocktime, $json, date('Y-m-d H:i:s')]);
    }

    public function addVout($txid, $address, $value) {
        $query = "INSERT INTO `vout` (`txid`, `address`, `value`, `date`) 
                  VALUES (?, ?, ?, ?)";
        $this->executeQuery($query, [$txid, $address, $value, date('Y-m-d H:i:s')]);
    }

    public function addTrack($txid, $type, $inout, $name, $from, $to, $amount) {
        $query = "INSERT INTO `track` (`txid`, `type`, `inout`, `name`, `from`, `to`, `amount`, `date`) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->executeQuery($query, [$txid, $type, $inout, $name, $from, $to, $amount, date('Y-m-d H:i:s')]);
    }

    public function addTX($block, $ntx) {
        $query = "INSERT INTO `ntx` (`block`, `tx`, `date`) 
                  VALUES (?, ?, ?)";
        $this->executeQuery($query, [$block, $ntx, date('Y-m-d H:i:s')]);
    }

    public function addTX1($block, $ntx, $size, $datetime) {
        $query = "INSERT INTO `ntx1` (`block`, `tx`, `size`, `date`) 
                  VALUES (?, ?, ?, ?)";
        $this->executeQuery($query, [$block, $ntx, $size, $datetime]);
    }
}

$d = new DogeBridge($pdo);

// Function to format bytes
if (!function_exists('formatBytes')) {
    function formatBytes($size, $precision = 2) {
        if ($size <= 0) return '0 B';  // Handle the case where size is 0 or negative
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}


if (isset($_REQUEST["fullblocks"])) {
    $tx = dogecoin_rpc($config,"getblockchaininfo", []);
    $block = $tx['blocks'];
    $blocknumber = $block;

    while ($blocknumber <= $block) {
        $blockHash = dogecoin_rpc($config, "getblockhash", [$blocknumber]);
        $blockData = dogecoin_rpc($config, "getblock", [$blockHash]);

        $datetime = date('Y-m-d H:i:s', $blockData["time"]);
        $txData = dogecoin_rpc($config, $config, "getrawtransaction", [$blockData['tx'][0], 1]);

        foreach ($txData["vout"] as $txvalue) {
            $value = $txvalue["value"];
            foreach ($txvalue["scriptPubKey"]["addresses"] as $address) {
                echo "->" . $address . "->" . $value . "<br>";
            }
        }

        exit();
    }

    echo "<br>Finished!";
}

if (isset($_REQUEST["nodeblocks"])) {
    echo "Track Node Blocks Transactions";

    $tx = dogecoin_rpc($config, "getblockchaininfo", []);
    $block = $tx['blocks'];
    $dbblock = $pdo->query("SELECT `block` FROM ntx1 ORDER BY `block` DESC LIMIT 1")->fetch();
    $blocknumber = $dbblock["block"] - 1;

    while ($blocknumber <= $block) {
        $blockHash = dogecoin_rpc($config, "getblockhash", [$blocknumber]);
        $blockData = dogecoin_rpc($config, "getblock", [$blockHash]);

        $datetime = date('Y-m-d H:i:s', $blockData["time"]);
        $ntx = count($blockData['tx']);
        $size = $blockData['size'];

        $dbc = $pdo->query("SELECT id FROM ntx1 WHERE `block` = ? LIMIT 1", [$blocknumber])->fetch();
        if (!isset($dbc["id"])) {
            $d->addTX1($blocknumber, $ntx, $size, $datetime);
        }

        $blocknumber = $block ++;
    }

    echo "<br>Finished!";
}

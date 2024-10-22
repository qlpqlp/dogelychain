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

    public function findExchange($find,$address){ 
        foreach($find["exchange"] as $name => $exchange){                   
           foreach($exchange as $findout){
             $pos = strpos($address, $findout);
             if ($pos !== false) {
               return $name;
             };
          };
        }; 
     }
     
     public function findMiner($find,$address){ 
       foreach($find["miner"] as $name => $miner){                   
          foreach($miner as $findout){
            $pos = strpos($address, $findout);
            if ($pos !== false) {
              return $name;
            };
        }; 
      };     
     }     
}

$d = new DogeBridge($pdo);

     
      /* 
        lets find out where and wen the miners new generated 
        dogecoin enter on the known public Dogecoin Address's
      */
      $find["exchange"]["robinhood"][] = "DPDLBAe3RGQ2GiPxDzhgjcmpZCZD8cSBgZ";
      
      $find["exchange"]["binance"][]   = "DE5opaXjFgDhFBqL6tBDxTAQ56zkX6EToX";
      $find["exchange"]["binance"][]   = "DQA5h47M4NdTt2mKDj4nP2VNZWczZi42RY"; // dono if is binance
      $find["exchange"]["binance"][]   = "DDuXGMFNGpGjaAqyDunSMvceMBruc1wwKF"; // dono if is binance because is a loop
      $find["exchange"]["binance"][]   = "DHQsfy66JsYSnwjCABFN6NNqW4kHQe63oU"; // dono if is binance because is a loop
      
      $find["exchange"]["binance"][]   = "DJfU2p6woQ9GiBdiXsWZWJnJ9uDdZfSSNC";
      $find["exchange"]["binance"][]   = "DGmzv39riELTuigZCUD6sWoHEHPdSbxdUB";
      
      $find["exchange"]["kraken"][]    = "D8WhgsmFUkf4imvsrwYjdhXL45LPz3bS1S";
      $find["exchange"]["kraken"][]    = "DR2SpAVZPwJDVxJgTkJvGej3HC5aLBhQBM";
      $find["exchange"]["kraken"][]    = "DLtqyzfk5JdwLDhWT6VHLecGL3D2vXznmX";
      $find["exchange"]["kraken"][]    = "DNBSNPvc29e14oMDo6vMR5Zb6uGTRYvVmY";
      $find["exchange"]["kraken"][]    = "DTcvX4DFe8TCpoxneurPWcfuskTxCQdHfh";
      $find["exchange"]["kraken"][]    = "DEmDBsiKxzBBwwXs8Cu3jBhwepENmXqrYj";
      $find["exchange"]["kraken"][]    = "DSFZTXbGJGMdjVakNsDjK15eVPdLJGi6hQ";
      $find["exchange"]["kraken"][]    = "DFSx1g9WW1f29Tnny26g58M35WVsVTG7e3";
      $find["exchange"]["kraken"][]    = "DPctHazrfYyRbeTLtg4qg5X6bZCzyS2GpC";
      $find["exchange"]["kraken"][]    = "DQBJFhhorivTPDDHD2Prg6W6mbBHXJGk4V";
      $find["exchange"]["kraken"][]    = "DQBVCsBKuyR19UHzPZK9r2kxksousFLeWz";
      $find["exchange"]["kraken"][]    = "DQQftxWRPoFgxdf1RJVBCkX1LW5vy8xsV5";
      $find["exchange"]["kraken"][]    = "DFdNZmmhAb9gNJm2HxfuHFHgCEuHiRL6uX";
      $find["exchange"]["kraken"][]    = "DSPYeHCvBoENJcnNiyrPSbYUA2daxDNzsJ";
      $find["exchange"]["kraken"][]    = "DAu272zSbfXkguSFS2xndd7FVjGEUzScYC";
      $find["exchange"]["kraken"][]    = "D8znqeuFkxCfvyEFtudGxoaYJjP7A2yxti";
      $find["exchange"]["kraken"][]    = "DTE2rj2tcq1b2Gt5cTxnPZdJM7dAAurxFy";
      $find["exchange"]["kraken"][]    = "DEdpq3THF6HBE4u4Shj8WrTZZSWcozePYw";
      $find["exchange"]["kraken"][]    = "DSPRCNM3CU3sAKmDd7qoeF9XfHPxCQSJkF";
      $find["exchange"]["kraken"][]    = "D7GD51VpzLk5LuKD6XqYDBMBCdNwxDPKcG";
      
      $find["exchange"]["cryptocom"][] = "D61T1GVeMZM8UHvXKyyD55Ur9efAF2mb5f";
      
      $find["exchange"]["coinbase"][] = "DDUoTGov76gcqAEBXXpUHzSuSQkPYKze9N";
      $find["exchange"]["coinbase"][] = "DUEnRvVqTTR3nsXMtVrEyayyuzawPKcbgD";
      $find["exchange"]["coinbase"][] = "DJE8ECVEc5NZeAxaA3sgFmrGqLP1KQMJVB";
  
      
      /* 
        We alredy know the top centralized miner pools
        tracking for the last days all dogecoin blocks rewards
      */    
      $find["miner"]["viabtc"][] = "DMqRVLrhbam3Kcfddpxd6EYvEBbpi3bEpP"; // ~35% ??????????????????? 
      $find["miner"]["f2pool"][] = "DTZSTXecLmSXpRGSfht4tAMyqra1wsL7xb"; // ~15% ???????????????????
      $find["miner"]["antpool"][] = "DMr3fEiVrPWFpoCWS958zNtqgnFb7QWn9D"; // ~15% ??????????????????
      $find["miner"]["litecoinpool"][] = "DDPodQNBoj4FHDgKWSBXJyC1jV88YqedzW"; // ~13%
      $find["miner"]["pooling"][] = "DPwQPzebSMcN4kzkcdEvqE8rE2r8SfJ8pC"; // ~5%
      $find["miner"]["unknown"][] = "DHFu8WjwXzHVy9pknMrxdQpePFir2FmiuG"; // ~3%
      

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

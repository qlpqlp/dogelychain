    <?php
    /**
    *   File: functions
    *   Author: https://x.com/inevitable360
    *   Description: Dogecoin CORE Wallet connected by RPC Calls using PHP.
    *   License: Open and modifiable.
    */
    
    // Establish PDO connection
    try {
        $db = 'mysql:host='.$config["dbhost"].';dbname='.$config["dbname"];
        $pdo = new PDO($db, $config["dbuser"], $config["dbpass"], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        echo "DB Error: " . $e->getMessage();
        header("Refresh:5"); 
        exit();
    }
    
    // Class to interact between DB and Dogecoin Core RPC
    class DogeBridge {
        private $pdo;
        
        public function __construct($pdo) {
            $this->pdo = $pdo;
        }
    
        public function addCoinBase($txid, $coinbase, $tag, $sequence, $time, $blocktime, $json) {
            $this->pdo->prepare("
                INSERT INTO `coinbase` (`txid`, `coinbase`, `tag`, `sequence`, `time`, `blocktime`, `json`, `date`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([$txid, $coinbase, $tag, $sequence, $time, $blocktime, $json, date('Y-m-d H:i:s')]);
        }
    
        public function addVout($txid, $address, $value) {
            $this->pdo->prepare("
                INSERT INTO `vout` (`txid`, `address`, `value`, `date`)
                VALUES (?, ?, ?, ?)
            ")->execute([$txid, $address, $value, date('Y-m-d H:i:s')]);
        }
    
        public function addTrack($txid, $type, $inout, $name, $from, $to, $amount) {
            $this->pdo->prepare("
                INSERT INTO `track` (`txid`, `type`, `inout`, `name`, `from`, `to`, `amount`, `date`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([$txid, $type, $inout, $name, $from, $to, $amount, date('Y-m-d H:i:s')]);
        }
    
        public function addTX($block, $ntx) {
            $this->pdo->prepare("
                INSERT INTO `ntx` (`block`, `tx`, `date`)
                VALUES (?, ?, ?)
            ")->execute([$block, $ntx, date('Y-m-d H:i:s')]);
        }
    
        public function addTX1($block, $ntx, $size, $datetime) {
            $this->pdo->prepare("
                INSERT INTO `ntx1` (`block`, `tx`, `size`, `date`)
                VALUES (?, ?, ?, ?)
            ")->execute([$block, $ntx, $size, $datetime]);
        }
    }
    
    // Helper function to format bytes
    function formatBytes($size, $precision = 2) {
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
    
    // Instantiate DogeBridge
    $d = new DogeBridge($pdo);
    
    // Handle different $_REQUEST cases
    if (isset($_REQUEST["fullblocks"])) {
        // Processing of full blocks
        // Fetch current block
        $blockInfo = dogecoin_rpc($config,"getblockchaininfo", []);
        $blockNumber = $blockInfo['blocks'];
        while ($blockNumber <= $blockInfo['blocks']) {
            $blockHash = dogecoin_rpc($config,"getblockhash", [$blockNumber]);
            $blockData = dogecoin_rpc($config,"getblock", [$blockHash]);
            $datetime = date('Y-m-d H:i:s', $blockData["time"]);
            $tx = dogecoin_rpc($config,"getrawtransaction", [$blockData['tx'][0], 1]);
            
            foreach ($tx["vout"] as $txvalue) {
                foreach ($txvalue["scriptPubKey"]["addresses"] as $address) {
                    $d->addVout($tx["txid"], $address, $txvalue["value"]);
                    echo "->" . $address . "->" . $txvalue["value"] . "<br>";
                }
            }
            $blockNumber++;
        }
        echo "<br>Finished!";
    }
    
    if (isset($_REQUEST["nodeblocks"])) {
        echo "Track Node Blocks Transactions";
        $blockInfo = dogecoin_rpc($config,"getblockchaininfo", []);
        $currentBlock = $blockInfo['blocks'];
        $dbBlock = $pdo->query("SELECT `block` FROM ntx1 ORDER BY `block` DESC LIMIT 1")->fetch();
        $blockNumber = $dbBlock["block"] - 1;
        
        while ($blockNumber <= $currentBlock) {
            $blockHash = dogecoin_rpc($config,"getblockhash", [$blockNumber]);
            $blockData = dogecoin_rpc($config,"getblock", [$blockHash]);
            $datetime = date('Y-m-d H:i:s', $blockData["time"]);
            $ntx = count($blockData['tx']);
            $size = $blockData['size'];
    
            $dbc = $pdo->query("SELECT id FROM ntx1 WHERE `block` = '" . $blockNumber . "' LIMIT 1")->fetch();
            if (!isset($dbc["id"])) {
                $d->addTX1($blockNumber, $ntx, $size, $datetime);
            }
            $blockNumber++;
        }
        echo "<br>Finished!";
    }
    
    if (isset($_REQUEST["blocks"])) {
        echo "Blocks";
        $blockInfo = dogecoin_rpc($config,"getblockchaininfo", []);
        $_REQUEST["bestblockhash"] = $blockInfo;
        $blockData = dogecoin_rpc($config,"getblock", [$blockInfo["bestblockhash"]]);
        $_REQUEST["block_decode"] = $blockData;
    
        foreach ($blockData["vout"] as $txvalue) {
            foreach ($txvalue["scriptPubKey"]["addresses"] as $address) {
                $d->addVout($blockData["txid"], $address, $txvalue["value"]);
            }
        }
        echo "<br>Finished!";
    }
    
    // Define functions to find exchanges and miners
    function findExchange($find, $address) {
        foreach ($find["exchange"] as $name => $exchange) {
            foreach ($exchange as $findout) {
                if (strpos($address, $findout) !== false) {
                    return $name;
                }
            }
        }
        return null;
    }
    
    function findMiner($find, $address) {
        foreach ($find["miner"] as $name => $miner) {
            foreach ($miner as $findout) {
                if (strpos($address, $findout) !== false) {
                    return $name;
                }
            }
        }
        return null;
    }
    
    // Hardcoded list of exchanges and miners
    $find = [
        "exchange" => [
            "robinhood" => ["DPDLBAe3RGQ2GiPxDzhgjcmpZCZD8cSBgZ"],
            "binance" => ["DE5opaXjFgDhFBqL6tBDxTAQ56zkX6EToX", "DQA5h47M4NdTt2mKDj4nP2VNZWczZi42RY", "DDuXGMFNGpGjaAqyDunSMvceMBruc1wwKF"
                        , "DJfU2p6woQ9GiBdiXsWZWJnJ9uDdZfSSNC", "DHQsfy66JsYSnwjCABFN6NNqW4kHQe63oU", "DGmzv39riELTuigZCUD6sWoHEHPdSbxdUB"],
            "kraken" => ["D8WhgsmFUkf4imvsrwYjdhXL45LPz3bS1S", "DR2SpAVZPwJDVxJgTkJvGej3HC5aLBhQBM", "DLtqyzfk5JdwLDhWT6VHLecGL3D2vXznmX"      
                        , "DTcvX4DFe8TCpoxneurPWcfuskTxCQdHfh", "DEmDBsiKxzBBwwXs8Cu3jBhwepENmXqrYj", "DSFZTXbGJGMdjVakNsDjK15eVPdLJGi6hQ"
                        , "DFSx1g9WW1f29Tnny26g58M35WVsVTG7e3", "DPctHazrfYyRbeTLtg4qg5X6bZCzyS2GpC", "DQBJFhhorivTPDDHD2Prg6W6mbBHXJGk4V"
                        , "DQBVCsBKuyR19UHzPZK9r2kxksousFLeWz", "DQQftxWRPoFgxdf1RJVBCkX1LW5vy8xsV5", "DFdNZmmhAb9gNJm2HxfuHFHgCEuHiRL6uX"
                        , "DSPYeHCvBoENJcnNiyrPSbYUA2daxDNzsJ", "DAu272zSbfXkguSFS2xndd7FVjGEUzScYC", "D8znqeuFkxCfvyEFtudGxoaYJjP7A2yxti"
                        , "DTE2rj2tcq1b2Gt5cTxnPZdJM7dAAurxFy", "DEdpq3THF6HBE4u4Shj8WrTZZSWcozePYw", "DSPRCNM3CU3sAKmDd7qoeF9XfHPxCQSJkF"
                        , "D7GD51VpzLk5LuKD6XqYDBMBCdNwxDPKcG", "DNBSNPvc29e14oMDo6vMR5Zb6uGTRYvVmY"],
            "cryptocom" => ["DDUoTGov76gcqAEBXXpUHzSuSQkPYKze9N"],
            "coinbase" => ["DUEnRvVqTTR3nsXMtVrEyayyuzawPKcbgD", "DJE8ECVEc5NZeAxaA3sgFmrGqLP1KQMJVB"],
        ],
        "miner" => [
            "viabtc" => ["DMqRVLrhbam3Kcfddpxd6EYvEBbpi3bEpP"],
            "f2pool" => ["DTZSTXecLmSXpRGSfht4tAMyqra1wsL7xb"],
            "antpool" => ["DMr3fEiVrPWFpoCWS958zNtqgnFb7QWn9D"],
            "litecoinpool" => ["DDPodQNBoj4FHDgKWSBXJyC1jV88YqedzW"],
            "pooling" => ["DPwQPzebSMcN4kzkcdEvqE8rE2r8SfJ8pC"],
            "unknown" => ["DHFu8WjwXzHVy9pknMrxdQpePFir2FmiuG"],
        ]
    ];
    
    if (isset($_REQUEST["test"])) {
        $dbm = $pdo->query("SELECT * FROM vout")->fetchAll();
        foreach ($dbm as $naddressm) {
            $db = $pdo->query("SELECT * FROM track WHERE `to` = '" . $naddressm["address"] . "'")->fetchAll();
            foreach ($db as $naddress) {
                $exchange = findExchange($find, $naddress["from"]);
                if ($exchange) {
                    echo $exchange . "<br>";
                }
            }
        }
    }    

    // Process "miners" request
    if (isset($_REQUEST["miners"])) {
        $i = 0;
    
        // Get the best block hash
        $block_hash = isset($_REQUEST["bestblockhash"]) ? ["bestblockhash" => $_REQUEST["bestblockhash"]] : dogecoin_rpc($config,"getblockchaininfo", []);
    
        // Get block data based on txid or block decode
        if (isset($_REQUEST["txid"])) {
            $block_decode["tx"][] = "";
            $block_decode["tx"][] = $_REQUEST["txid"];
        } else {
            $block_decode = isset($_REQUEST["block_decode"]) ? $_REQUEST["block_decode"] : dogecoin_rpc($config,"getblock", [$block_hash["bestblockhash"]]);
            echo "Block: " . $block_decode["height"] . "<br><br>";
        }
    
        $txi = 0;
        foreach ($block_decode["tx"] as $tx) {
            if ($txi > 0) {
                $tx_decoded = dogecoin_rpc($config,"getrawtransaction", [$tx, 1]);
                $vin["txid"] = $tx_decoded["txid"];
    
                if (!isset($vin["coinbase"])) {
                    echo "Txid:" . $vin["txid"] . "<br>";
                    $txid = $vin["txid"];
                    $vin_array_decoded = dogecoin_rpc($config,"getrawtransaction", [$vin["txid"], 1]);
    
                    $valueFrom = 0;
                    $totalFrom = 0;
                    $totalTo = 0;
                    $trackall = 0;
                    $addall = 0;
    
                    // Process "vin" transactions
                    foreach ($vin_array_decoded["vin"] as $vout) {
                        $vin_tx_decoded = dogecoin_rpc($config,"getrawtransaction", [$vout["txid"], 1]);
                        foreach ($vin_array_decoded["vin"] as $name => $vin) {
                            $valueFrom = $vin_tx_decoded["vout"][$vout["vout"]]["value"];
                            $addressFrom = $vin_tx_decoded["vout"][$vout["vout"]]["scriptPubKey"]["addresses"][0];
                        }
    
                        echo "<br>From: " . $addressFrom;
                        $exchange = findExchange($find, $addressFrom);
                        if ($exchange) {
                            echo " <b style='color: #FF3300'>(" . $exchange . ")</b>";
                            if ($trackall != 1) {
                                $d->AddTrack($txid, "exchange", "out", $exchange, $addressFrom, "", $valueFrom);
                                $trackall = 1;
                            }
                        }
    
                        $miner = findMiner($find, $addressFrom);
                        if ($miner) {
                            echo " <b style='color: #99CC00'>(" . $miner . ")</b>";
                            if ($trackall != 1) {
                                $d->AddTrack($txid, "miner", "out", $miner, $addressFrom, "", $valueFrom);
                                $trackall = 1;
                            }
                        }
    
                        if ($addall == 1) {
                            if ($exchange) {
                                $d->AddTrack($txid, "exchange", "out", $exchange, $addressFrom, "", $valueFrom);
                            } elseif ($miner) {
                                $d->AddTrack($txid, "miner", "out", $miner, $addressFrom, "", $valueFrom);
                            } else {
                                $d->AddTrack($txid, "", "out", "", $addressFrom, "", $valueFrom);
                            }
                        }
    
                        echo "<br>Value: " . $valueFrom;
                        $totalFrom += $valueFrom;
                    }
    
                    echo "<br>------";
    
                    // Process "vout" transactions
                    foreach ($vin_array_decoded["vout"] as $voutTo) {
                        if ($trackall == 1) $addall = 1;
    
                        echo "<br>To: " . $voutTo["scriptPubKey"]["addresses"][0];
                        $exchange = findExchange($find, $voutTo["scriptPubKey"]["addresses"][0]);
                        if ($exchange) {
                            echo " <b style='color: #FF3300'>(" . $exchange . ")</b>";
                            if ($trackall != 1) {
                                $d->AddTrack($txid, "exchange", "in", $exchange, "", $voutTo["scriptPubKey"]["addresses"][0], $voutTo["value"]);
                                $trackall = 1;
                            }
                        }
    
                        $miner = findMiner($find, $voutTo["scriptPubKey"]["addresses"][0]);
                        if ($miner) {
                            echo " <b style='color: #99CC00'>(" . $miner . ")</b>";
                            if ($trackall != 1) {
                                $d->AddTrack($txid, "miner", "in", $miner, "", $voutTo["scriptPubKey"]["addresses"][0], $voutTo["value"]);
                                $trackall = 1;
                            }
                        }
    
                        if ($addall == 1) {
                            if ($exchange) {
                                $d->AddTrack($txid, "exchange", "in", $exchange, "", $voutTo["scriptPubKey"]["addresses"][0], $voutTo["value"]);
                            } elseif ($miner) {
                                $d->AddTrack($txid, "miner", "in", $miner, "", $voutTo["scriptPubKey"]["addresses"][0], $voutTo["value"]);
                            } else {
                                $d->AddTrack($txid, "", "in", "", "", $voutTo["scriptPubKey"]["addresses"][0], $voutTo["value"]);
                            }
                        }
    
                        echo "<br>Value: " . $voutTo["value"];
                        $totalTo += $voutTo["value"];
                    }
    
                    echo "<br>Fees:" . ($totalFrom - $totalTo);
                    echo "<br><br>__________________________________________<br><br>";
                }
            }
            $txi++;
        }
    }
    
    // Process mempool count request
    if (isset($_POST["mempool"])) {
        echo count($dogecoin->getrawmempool());
    }
    
    // Process mining difficulty request
    if (isset($_POST["difficulty"])) {
        $mining = $dogecoin->getmininginfo();
        echo $mining["difficulty"];
    }
    
    ?>
    
    
<?php

function dogecoin_rpc($config, $method, $params = []) {
    $url = $config["dogecoinCoreProtocol"] .  $config["rpcuser"] . ":" .  $config["rpcpassword"] . "@" .  $config["dogecoinCoreServer"] . ":" . $config["dogecoinCoreServerPort"] . "/";
    $headers = [
        "Content-Type: application/json"
    ];

    $data = [
        "method" => $method,
        "params" => $params,
        "id" => time(),
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL Error: {$error}");
    }

    curl_close($ch);

    // Decode the response as an associative array
    $result = json_decode($response, true);

    if (!$result) {
        throw new Exception("Invalid JSON response");
    }

    if (isset($result["error"]) && $result["error"] !== null) {
        throw new Exception("RPC Error: " . $result["error"]["message"]);
    }

    return $result["result"] ?? null; // Return null if no result
}
?>

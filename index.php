<?php 
include("inc/config.php"); 

// Function to clean the string
function clean($string) {
    return preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $string));
}

// Use prepared statement to avoid SQL injection
$show = 6;
$query = "SELECT * FROM vout WHERE date >= NOW() - INTERVAL ? HOUR ORDER BY txid";
$stmt = $pdo->prepare($query);
$stmt->execute([$show]);
$vouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Caching doges and txiss
$doges = [];
$txiss = [];
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>DogelyChain - Tracking all Dogecoin Miners and Where the money flows</title>
  <meta name="description" content="DogelyChain - Tracking all Dogecoin Miners and Where the money flows">
  <meta name="author" content="All Dogecoin Community!">
  <link rel="icon" href="//what-is-dogecoin.com/img/dogecoin-300.png" />
  <link href="//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
  <style>
      #mynetwork {
          width: 100%;
          height: 100%;
          margin: 0px;
          position: absolute;
      }
  </style>
</head>
<body style="font-family: 'Comic Sans MS'; background-color: rgba(0, 0, 0, 1);">
  <div style="position: absolute; color: #fff; padding: 10px;">
    Showing the Last <?php echo $show; ?> Hours<br>(loading takes some time...)
  </div>

  <div id="dig"></div>
  <div id="mynetwork"></div>

  <script type="text/javascript">
    // Create an array with nodes and edges
    var nodes = new vis.DataSet([]);
    var edges = new vis.DataSet([]);

    <?php foreach ($vouts as $rowv): ?>
      <?php if (!in_array($rowv["txid"], $txiss)): 
          $txiss[] = $rowv["txid"];
          $blockRow = $pdo->prepare("SELECT * FROM coinbase WHERE txid = ?");
          $blockRow->execute([$rowv["txid"]]);
          $block = $blockRow->fetch(PDO::FETCH_ASSOC);
      ?>
        nodes.add({
          id: '<?php echo $rowv["txid"]; ?>',
          label: '<?php echo $block["blocktime"]; ?>',
          shape: 'circle',
          color: '#F84B01',
          url: 'https://bitinfocharts.com/dogecoin/tx/<?php echo $rowv["txid"]; ?>'
        });
      <?php endif; ?>

      <?php if (!in_array($rowv["address"], $doges)): 
          $doges[] = $rowv["address"]; 
          $valueStmt = $pdo->prepare("SELECT SUM(value) as total_value FROM vout WHERE date >= NOW() - INTERVAL ? HOUR AND address = ?");
          $valueStmt->execute([$show, $rowv["address"]]);
          $rowv["value"] = $valueStmt->fetchColumn();
      ?>
        nodes.add({
          id: 'vout<?php echo $rowv["address"]; ?>',
          label: '[Miner tag: <?php echo clean(hex2bin($block["tag"])); ?>]\n\n<?php echo $rowv["address"]; ?>\n\n<?php echo round($rowv["value"], 2); ?>',
          shape: 'circle',
          url: 'https://bitinfocharts.com/dogecoin/address/<?php echo $rowv["address"]; ?>',
          size: <?php echo round($rowv["value"], 0); ?>
        });
      <?php endif; ?>
    <?php endforeach; ?>

    // Add edges
    <?php foreach ($vouts as $rowv): ?>
      edges.add({
        from: '<?php echo $rowv["txid"]; ?>',
        to: 'vout<?php echo $rowv["address"]; ?>'
      });
    <?php endforeach; ?>

    // Create the network
    var container = document.getElementById('mynetwork');
    var data = {nodes: nodes, edges: edges};
    var options = {autoResize: false};
    var network = new vis.Network(container, data, options);

    // Open URL on double click
    network.on("doubleClick", function(params) {
      if (params.nodes.length === 1) {
        var node = nodes.get(params.nodes[0]);
        if (node.url != null) {
          window.open(node.url, '_blank');
        }
      }
    });
  </script>

</body>
</html>

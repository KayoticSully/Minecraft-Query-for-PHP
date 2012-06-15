
<?php
define('MQ_SERVER_ADDR', 'localhost');
define('MQ_SERVER_PORT', 25575);
define('MQ_SERVER_PASS', 'lolrcontest');
define('MQ_TIMEOUT', 2);

require __DIR__ . '/minecraftRcon.class.php';

echo "<pre>";

try {
    $rcon = new minecraftRcon;

    $rcon->connect(MQ_SERVER_ADDR, MQ_SERVER_PORT, MQ_SERVER_PASS, MQ_TIMEOUT);

    $data = $rcon->command("say Hello from xPaw's minecraft rcon implementation.");

    if ($data === false) {
        throw new minecraftRconException("Failed to get command result.");
    } else if (strlen($data) == 0) {
        throw new minecraftRconException("Got command result, but it's empty.");
    }

    echo htmlspecialchars($data);
}
catch (minecraftRconException $e) {
    echo $e->getMessage();
}

$rcon->disconnect();
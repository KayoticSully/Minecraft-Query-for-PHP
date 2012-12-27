<?php
/*
 * Queries Minecraft server
 * Returns array on success, false on failure.
 *
 * Originally written by xPaw
 * Modifications and additions by ivkos
 *
 * GitHub: https://github.com/ivkos/Minecraft-Query-for-PHP
 */

function queryMinecraft($IP, $port = 25565, $timeout = 2)
{
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array(
        'sec' => (int) $timeout,
        'usec' => 0
    ));
    
    $latencyStart = microtime(true);
    
    if ($socket === false || @socket_connect($socket, $IP, (int) $port) === false) {
        return false;
    }
    
    socket_send($socket, "\xFE\x01", 2, 0);
    $len = socket_recv($socket, $data, 256, 0);
    socket_close($socket);
    $latencyEnd = microtime(true);
    
    if ($len < 4 || $data[0] !== "\xFF") {
        return false;
    }
    
    $data = substr($data, 3);  // Strip packet header (kick message packet and short length)
    $data = iconv('UTF-16BE', 'UTF-8', $data);
    
    // Are we dealing with Minecraft 1.4+ server?
    if($data[1] === "\xA7" && $data[2] === "\x31") {
        $data = explode("\x00", $data);
        
        return array(
            'HostName'   => $data[3],
            'Players'    => intval($data[4]),
            'MaxPlayers' => intval($data[5]),
            'Latency'    => round(($latencyEnd - $latencyStart) * 1000, 2),
            'Protocol'   => intval($data[1]),
            'Version'    => $data[2]
        );
    }
    
    $data = explode("\xA7", $data);
    
    return array(
        'HostName'   => substr($data[0], 0, -1),
        'Players'    => isset($data[1]) ? intval($data[1]) : 0,
        'MaxPlayers' => isset($data[2]) ? intval($data[2]) : 0,
        'Latency'    => round(($latencyEnd - $latencyStart) * 1000, 2),        
        'Protocol'   => 0,
        'Version'    => '1.3'
    );
}

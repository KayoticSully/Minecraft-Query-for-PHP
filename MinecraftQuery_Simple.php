<?php
/*
 * Queries Minecraft server (1.8+)
 * Returns array on success, false on failure.
 *
 * Originally written by xPaw
 * Modifications and additions by ivkos
 *
 * GitHub: https://github.com/ivkos/Minecraft-Query-for-PHP
 */

function queryMinecraft($ip, $port = 25565, $timeout = 2)
{
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array(
        'sec' => (int) $timeout,
        'usec' => 0
    ));
    
    if ($socket === false || @socket_connect($socket, $ip, (int) $port) === false) {
        return false;
    }
    
    socket_send($socket, "\xFE", 1, 0);
    $len = socket_recv($socket, $data, 256, 0);
    socket_close($socket);
    
    if ($len < 4 || $data[0] != "\xFF") {
        return false;
    }
    
    $data = substr($Data, 3);
    $data = iconv('UTF-16BE', 'UTF-8', $data);
    $data = explode("\xA7", $data);
    
    return array(
        'hostName'   => substr($data[0], 0, -1),
        'players'    => isset($data[1]) ? intval($data[1]) : 0,
        'maxPlayers' => isset($data[2]) ? intval($data[2]) : 0
    );
}
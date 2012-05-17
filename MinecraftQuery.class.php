<?php
class minecraftQueryException extends Exception
{
}

class minecraftQuery
{
    /*
     * Class originally written by xPaw
     * Modifications and additions by ivkos
	 *
	 * GitHub: https://github.com/ivkos/Minecraft-Query-for-PHP
	 */
    
    const STATISTIC = 0x00;
    const HANDSHAKE = 0x09;
    
    private $socket;
    private $players;
    private $info;
    private $online;
    
    public function connect($ip, $port = 25565, $timeout = 3)
    {
        if ($this->socket = fsockopen('udp://' . $ip, (int) $port)) {
            socket_set_timeout($this->socket, $timeout);
            
            $challenge = $this->getChallenge();
            
            if ($challenge === false) {
                fclose($this->socket);
                throw new minecraftQueryException("Failed to receive challenge.");
            }
            
            if (!$this->getStatus($challenge)) {
                fclose($this->socket);
                throw new minecraftQueryException("Failed to receive status.");
            } else {
                $this->online = true;
            }
            
            fclose($this->socket);
        } else {
            throw new minecraftQueryException("Can't open connection.");
        }
    }
    
    public function getInfo()
    {
        return isset($this->info) ? $this->info : false;
    }
    
    public function getPlayerList()
    {
        return isset($this->players) ? $this->players : false;
    }
    
    public function isOnline()
    {
        return isset($this->online) ? $this->online : false;
    }
    
    private function getChallenge()
    {
        $data = $this->writeData(self::HANDSHAKE);
        
        return $data ? pack('N', $data) : false;
    }
    
    private function getStatus($challenge)
    {
        $data = $this->writeData(self::STATISTIC, $challenge . pack('c*', 0x00, 0x00, 0x00, 0x00));
        
        if (!$data) {
            return false;
        }
        
        $last = "";
        $info = array();
        
        $data    = substr($data, 11); // splitnum + 2 int
        $data    = explode("\x00\x00\x01player_\x00\x00", $data);
        $players = substr($data[1], 0, -2);
        $data    = explode("\x00", $data[0]);
        
        // Array with known keys in order to validate the result
        // It can happen that server sends custom strings containing bad things (who can know!)
        $keys = array(
            'hostname'   => 'hostName',
            'gametype'   => 'gameType',
            'version'    => 'version',
            'plugins'    => 'plugins',
            'map'        => 'map',
            'numplayers' => 'players',
            'maxplayers' => 'maxPlayers',
            'hostport'   => 'hostPort',
            'hostip'     => 'hostIp'
        );
        
        foreach ($data as $key => $value) {
            if (~$key & 1) {
                if (!array_key_exists($value, $keys)) {
                    $last = false;
                    continue;
                }
                
                $last        = $keys[$value];
                $info[$last] = "";
            } else if ($last != false) {
                $info[$last] = $value;
            }
        }
        
        // Ints
        $info['players']    = intval($info['players']);
        $info['maxPlayers'] = intval($info['maxPlayers']);
        $info['hostPort']   = intval($info['hostPort']);
        
        // Parse "plugins", if any
        if ($info['plugins']) {
            $Data = explode(": ", $info['plugins'], 2);
            
            $info['rawPlugins'] = $info['plugins'];
            $info['software']   = $data[0];
            
            if (count($data) == 2) {
                $info['plugins'] = explode("; ", $data[1]);
            }
        } else {
            $info['software'] = 'Vanilla';
        }
        
        $this->info = $info;
        
        if ($players) {
            $this->players = explode("\x00", $players);
        }
        
        return true;
    }
    
    private function writeData($command, $append = "")
    {
        $command = pack('c*', 0xFE, 0xFD, $command, 0x01, 0x02, 0x03, 0x04) . $append;
        $length  = strlen($command);
        
        if ($length !== fwrite($this->socket, $command, $length)) {
            return false;
        }
        
        $data = fread($this->socket, 1440);
        
        if (strlen($data) < 5 || $data[0] != $command[2]) {
            return false;
        }
        
        return substr($data, 5);
    }
}
# Minecraft Query for PHP

## Description
You can query Minecraft servers using this class.<br>
This works for servers running **Minecraft 1.0** and up.

## Instructions
Before using this class, you need to make sure that your server has querying enabled.

Look for the following settings in **server.properties**:
> *enable-query=true*<br>
> *query.port=25565*

## Example
```php
<?php
require __DIR__ . '/MinecraftQuery.class.php';

$q = new minecraftQuery();

try {
    $q->connect('localhost', 25565, 3); // Connects to the Minecraft server running
                                        // on 'localhost' on port 25565, with timeout 3 seconds
    
    print_r($q->getInfo());             // Prints server information (e.g. version, number of players)
    print_r($query->getPlayerList());   // Prints a list of players currently playing
}
catch (minecraftQueryException $e) {
    echo $e->getMessage();              // Prints an error if there is such
}
?>
```

## License
This class was originally written by xpaw. Modifications and additions by ivkos.
> *This work is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License.<br>
> To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/3.0/*
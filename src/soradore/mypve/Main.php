<?php

namespace soradore\mypve;

use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLogger;
use soradore\mypve\entity\EntityManager;
use soradore\mypve\item\ItemManager;

class Main extends PluginBase
{
    public static $baseDir = '/';
    public static $baseNamespace = __NAMESPACE__;
    public static ?PluginLogger $logger = null;

    public function onEnable(): void
    {
        self::$baseDir = $this->getFile() . 'src/';
        self::$logger = $this->getLogger();

        EntityManager::autoRegister();
        ItemManager::autoRegister();
    }
}

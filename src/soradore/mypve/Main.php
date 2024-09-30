<?php

namespace soradore\mypve;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\SpawnEgg;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLogger;
use pocketmine\world\World;
use soradore\mypve\entity\EntityManager;
use soradore\mypve\entity\Skeleton;
use soradore\mypve\item\ItemManager;
use soradore\mypve\item\ItemRegister;

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

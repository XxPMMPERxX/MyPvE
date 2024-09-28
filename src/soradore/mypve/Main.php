<?php

namespace soradore\mypve;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\SpawnEgg;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLogger;
use pocketmine\world\World;
use soradore\mypve\entity\EntityManager;
use soradore\mypve\entity\Skeleton;
use soradore\mypve\item\ItemRegister;

class Main extends PluginBase
{
    public static $baseDir = '/';
    public static ?PluginLogger $logger = null;

    public function onEnable(): void
    {
        self::$baseDir = $this->getFile() . 'src/';
        self::$logger = $this->getLogger();

        EntityManager::autoRegister();
        self::registerItems();
    }

    protected static function registerItems()
    {
        ItemRegister::register(
            new class(new ItemIdentifier(ItemTypeIds::newId()), "Skeleton Spawn Egg") extends SpawnEgg {
                public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
                {
                    return new Skeleton(Location::fromObject($pos, $world, $yaw, $pitch));
                }
            },
            ItemTypeNames::SKELETON_SPAWN_EGG,
            'skeleton_spawn_egg',
            true,
        );
    }
}

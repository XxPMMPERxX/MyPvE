<?php

namespace soradore\mypve\entity;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\Entity;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use soradore\mypve\Main;
use soradore\mypve\utils\ClassFinder;

final class EntityManager
{
    public static function register(string $entityClassName, array $saveNames)
    {
        EntityFactory::getInstance()->register(
            $entityClassName, 
            function(World $world, CompoundTag $nbt) use ($entityClassName): Entity {
                return new $entityClassName(
                    EntityDataHelper::parseLocation($nbt, $world),
                    $nbt
                );
            },
            $saveNames
        );
    }

    public static function autoRegister()
    {
        $classList = ClassFinder::getClassesFromNamespace(
            __NAMESPACE__,
            function ($class) {
                return in_array(Entity::class, class_parents($class));
            }
        );
        
        foreach ($classList as $class) {
            $shortName = (new \ReflectionClass($class))->getShortName();

            self::register(
                $class,
                [$shortName, $class::getNetworkTypeId()]
            );

            Main::$logger?->info(
                TextFormat::AQUA . $shortName . TextFormat::WHITE . ' が登録されました'
            );
        }
    }
}

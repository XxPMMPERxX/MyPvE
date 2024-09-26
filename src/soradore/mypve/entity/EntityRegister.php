<?php

namespace soradore\mypve\entity;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\Entity;
use pocketmine\world\World;

final class EntityRegister
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


}
<?php

namespace soradore\mypve\item;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\item\SpawnEgg;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use soradore\mypve\entity\Skeleton;

class SkeletonSpawnEgg extends SpawnEgg implements AutoRegistableInterface
{
    public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
    {
        return new Skeleton(Location::fromObject($pos, $world, $yaw, $pitch));
    }

    public static function provide(): RegistableProvideData
    {
        return new RegistableProvideData(
            ItemTypeNames::SKELETON_SPAWN_EGG,
            'skeleton_spawn_egg',
            true,
        );
    }
}

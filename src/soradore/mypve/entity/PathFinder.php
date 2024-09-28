<?php

namespace soradore\mypve\entity;

use pocketmine\math\Facing;
use pocketmine\world\Position;

class PathFinder
{
    public static function calcPath(Position $start, Position $target)
    {
        $world = $start->getWorld();
        $stack = [];
        $opened = [];
        $limit = 200;

        $current = $start;
        while(--$limit > 0) {

            $rounds = self::getRounds($current);
            $rounds = array_filter($rounds, function ($position) use ($stack, $opened) {
                return !in_array($position, $stack) && !in_array($position, array_column($opened, 0));
            });

            foreach ($rounds as $position) {
                $cost = self::calcCost(count($stack), $position->distance($target), $position->y - $current->y);
                $opened[] = [
                    $position,
                    $cost,
                ];
            }

            /** コストでソート */
            usort($opened, function ($openedPositionA, $openedPositionB) {
                return $openedPositionA[1] < $openedPositionB[1] ? -1 : 1;
            });

            /** 一番コストが低いのを取得 */
            $next = $opened[0][0] ?? null;

            if (!$next) {
                return [];
            }

            $next = Position::fromObject($next->floor()->add(0.5, 0, 0.5), $world);
            $stack[] = $next;

            $current = $next;
        }

        return $stack;
    }

    /**
     * @return Position[]
     */
    public static function getRounds(Position $center)
    {
        $world = $center->getWorld();

        $rounds = [];
        foreach (Facing::HORIZONTAL as $side) {
            $side = $center->getSide($side);

            $block = $world->getBlock($side);
            // 通れない場合は除外
            if ($block->isSolid() && $block->getSide(Facing::UP)->isSolid()) {
                continue;
            }

            // 段差があり、段差の上が通れない場合は除外
            if ($block->isSolid() && ($block->getSide(Facing::UP)->isSolid() || $block->getSide(Facing::UP, 2)->isSolid())) {
                continue;
            }

            // 段差があり、段差の上が通れる場合は段差の上の座標をセット
            if ($block->isSolid() && !$block->getSide(Facing::UP)->isSolid() && !$block->getSide(Facing::UP, 2)->isSolid()) {
                $side = $block->getSide(Facing::UP)->getPosition();
            }

            $rounds[] = $side;
        }

        return $rounds;
    }

    public static function calcCost(int $step, float $distance, float $yDiff)
    {
        return $step + $distance + abs($yDiff);
    }
}

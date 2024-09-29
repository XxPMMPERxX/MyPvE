<?php

namespace soradore\mypve\entity;

use pocketmine\math\Facing;
use pocketmine\world\particle\RedstoneParticle;
use pocketmine\world\Position;

/**
 * A* アルゴリズムでパスを計算、一番近い次の目標を返す
 * Ref: https://ja.wikipedia.org/wiki/A*
 */
class PathFinder
{
    public static function calcPath(Position $start, Position $target)
    {
        $world = $start->getWorld();

        $start = Position::fromObject($start->floor(), $world);
        $target = Position::fromObject($target->floor(), $world);

        $limit = PHP_INT_MAX;

        $opened = new NodeList();
        $closed = new NodeList();
        
        $opened->push(new Node($start, 0, $start->distance($target)));

        while(--$limit > 0) {
            // var_dump($opened->count());
            if ($opened->count() <= 0 || $opened->count() >= 30) {
                break;
            }

            $node = $opened->pop();

            if ($node->getPosition()->equals($target)) {
                $paths = [];
                while (($parent = $node?->getParent()) !== null) {
                    $paths[] = $node;
                    $node = $parent;
                }

                // return $paths[count($paths)-2] ?? null;
                return end($paths);
            }

            $closed->push($node);

            $roundNodes = $node->getRoundNodes($node->getPosition());

            foreach ($roundNodes as $roundNode) {
                $roundPosition = $roundNode->getPosition();

                $fn = $node->gn + $roundPosition->distance($target);

                if (!$opened->has($roundNode) && !$closed->has($roundNode)) {
                    $roundNode->fn = $fn;
                    $roundNode->setParent($node);
                    $opened->push($roundNode);

                    continue;
                }

                if (($old = $opened->find($roundNode)) !== null) {
                    if ($fn < $old->fn) {
                        $old->fn = $fn;
                        $old->setParent($node);

                        $opened->orderByCost();
                    }

                    continue;
                }

                if (($old = $closed->find($roundNode)) !== null) {
                    if ($fn < $old->fn) {
                        $old->fn = $fn;
                        $old->setParent($node);

                        $opened->orderByCost();

                        $closed->remove($old);
                    }
                }
            }
        }
        
        /**
         * 見つからない場合はターゲットに向かって直進だ!
         */
        return new Node($target);
    }
}

class Node
{

    public function __construct(
        public Position $position, 
        public $gn = 0, 
        public float $fn = 0, 
        public ?Node $parent = null
    ) {
        
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setParent(Node $parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return Node[]
     */
    public function getRoundNodes()
    {
        $position = $this->getPosition();
        $world = $this->getPosition()->getWorld();

        $center = Position::fromObject($position->floor(), $world);

        $rounds = [];
        foreach (Facing::HORIZONTAL as $side) {
            $side = $center->getSide($side);

            $block = $world->getBlock($side);
            // 通れない場合は除外
            if ($block->getSide(Facing::UP)->isSolid()) {
                continue;
            }

            /** 二段以上の穴があれば通れないので除外 */
            if (!$block->isSolid() && !$block->getSide(Facing::DOWN)->isSolid() && !$block->getSide(Facing::DOWN, 2)->isSolid()) {
                $world->addParticle(
                    $block->getPosition()->add(0.5, 0.5, 0.5),
                    new RedstoneParticle(),
                );
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

            $rounds[] = new Node($side);
        }

        return $rounds;
    }
}

class NodeList
{
    /** @var Node[] */
    protected $list = [];

    public function __construct(array $list = [])
    {
        $this->list = $list;
        $this->orderByCost();
    }

    public function push(Node $node)
    {
        $this->list[] = $node;
        $this->orderByCost();
    }

    /**
     * @return Node
     */
    public function pop()
    {
        return array_pop($this->list);
    }

    public function orderByCost()
    {
        usort($this->list, function (Node $nodeA, Node $nodeB) {
            return $nodeB->fn <=> $nodeA->fn;
        });
    }

    /**
     * @return int count
     */
    public function count()
    {
        return count($this->list);
    }

    /**
     * Node を見つけて返す
     * ない場合は null
     */
    public function find(Node $needle)
    {
        $position = $needle->getPosition();

        foreach ($this->list as $node) {
            if ($node->getPosition()->equals($position)) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Node が存在するかどうか
     */
    public function has(Node $needle)
    {
        return !!$this->find($needle);
    }
    

    /**
     * Node を削除
     */
    public function remove(Node $needle)
    {
        $newList = array_filter($this->list, function (Node $node) use ($needle) {
            $position = $needle->getPosition();

            return !$node->getPosition()->equals($position);
        });

        $this->list = $newList;

        $this->orderByCost();
    }
}
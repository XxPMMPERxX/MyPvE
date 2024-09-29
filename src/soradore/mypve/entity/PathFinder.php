<?php

namespace soradore\mypve\entity;

use pocketmine\math\Facing;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\LavaDripParticle;
use pocketmine\world\Position;

class PathFinder
{
    public static function calcPath(Position $start, Position $target)
    {
        $world = $start->getWorld();

        $start = Position::fromObject($start->floor(), $world);
        $target = Position::fromObject($target->floor(), $world);

        $limit = 10;

        $opened = new NodeList();
        $closed = new NodeList();
        
        $opened->push(new Node($start, 0, $start->distance($target)));

        while(--$limit > 0) {
            if ($opened->count() <= 0) {
                return null;
            }

            $node = $opened->pop();

            if ($node->getPosition()->equals($target)) {
                $parent = $node->getParent();
                while (($parent = $parent?->getParent()) !== null) {
                    $world->addParticle(
                        $parent->getPosition()->add(0.5, 1, 0.5),
                        new FlameParticle(),
                    );
                }
                return $node;
            }

            $closed->push($node);

            $roundNodes = $node->getRoundNodes($node->getPosition());


            foreach ($roundNodes as $roundNode) {
                $roundPosition = $roundNode->getPosition();

                $fn = $node->gn + abs($roundPosition->y - $node->getPosition()->y) + $roundPosition->distance($target);

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

        return null;
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

    public function has(Node $needle)
    {
        return !!$this->find($needle);
    }
    
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
<?php

namespace soradore\mypve\item;

use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\world\format\io\GlobalItemDataHandlers;

final class ItemRegister
{
    protected static $wrapperItems = [];

    public static function register(Item $item, string $itemTypeName, string $namespace, bool $addCreativeInventory = true) : void
    {
        $runtimeId = $item->getTypeId();

        (function() use ($item, $itemTypeName) : void {
            /** @var \pocketmine\data\bedrock\item\ItemDeserializer $this */
            if(isset($this->deserializers[$itemTypeName])){
                unset($this->deserializers[$itemTypeName]);
            }
            $this->map($itemTypeName, static fn(SavedItemData $_) => clone $item);
        })->call(GlobalItemDataHandlers::getDeserializer());

        (function() use ($item, $itemTypeName) : void {
            /** @var \pocketmine\data\bedrock\item\ItemSerializer $this */
            $this->itemSerializers[$item->getTypeId()] = static fn() => new SavedItemData($itemTypeName);
        })->call(GlobalItemDataHandlers::getSerializer());

        (function() use ($item, $runtimeId, $itemTypeName) : void {
            /** @var \pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary $this */
            $this->stringToIntMap[$itemTypeName] = $runtimeId;
            $this->intToStringIdMap[$runtimeId] = $itemTypeName;
            $this->itemTypes[] = new ItemTypeEntry($itemTypeName, $runtimeId, true);
        })->call(TypeConverter::getInstance()->getItemTypeDictionary());

        if ($namespace !== "") {
            StringToItemParser::getInstance()->register($namespace, fn() => clone $item);
        }

        if ($addCreativeInventory) {
            CreativeInventory::getInstance()->add($item);
        }

        self::$wrapperItems[$item::class] = $item;
    }
}
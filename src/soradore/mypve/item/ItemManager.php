<?php

namespace soradore\mypve\item;

use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use soradore\mypve\Main;
use soradore\mypve\utils\ClassFinder;

final class ItemManager
{
    protected static $wrapperItems = [];

    public static function register(Item $item, RegistableProvideData $data) : void
    {
        $runtimeId = $item->getTypeId();

        $itemTypeName = $data->getItemTypeName();
        $namespace = $data->getNamespace();
        $addCreativeInventory = $data->getAddCreativeInventory();

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

    public static function autoRegister()
    {
        $classList = ClassFinder::getClassesFromNamespace(
            __NAMESPACE__,
            function ($class) {
                return in_array(AutoRegistableInterface::class,  class_implements($class));
            }
        );
        
        foreach ($classList as $class) {
            $shortName = (new \ReflectionClass($class))->getShortName();

            self::register(
                new $class(new ItemIdentifier(ItemTypeIds::newId()), $shortName),
                $class::provide(),
            );

            Main::$logger?->info(
                TextFormat::AQUA . $shortName . TextFormat::WHITE . ' が登録されました'
            );
        }
    }
}
<?php

namespace Polaris\lib\inventoryapi;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Polaris\lib\inventoryapi\inventories\{BaseInventoryCustom, SimpleChestInventory, DoubleInventory};

class InventoryAPI extends PluginBase
{
    /*
     * Features: workbench inventory, hopper inventory
     */
    
    use SingletonTrait;

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        self::$instance = $this;
    }

    public static function createSimpleChest(bool $isViewOnly = false): SimpleChestInventory {
        $inventory = new SimpleChestInventory();
        $inventory->setViewOnly($isViewOnly);
        return $inventory;
    }

    public static function createDoubleChest(bool $isViewOnly = false): SimpleChestInventory {
        $inventory = new DoubleInventory();
        $inventory->setViewOnly($isViewOnly);
        return $inventory;
    }

    public function getDelaySend(): int {
        return $this->getConfig()->get('delay-send-double-chest') ?? 10;
    }
}

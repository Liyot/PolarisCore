<?php

namespace Polaris\cosmetics;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\SingletonTrait;
use Polaris\cosmetics\head\CrownCosmetic;
use Polaris\lib\inventoryapi\inventories\BaseInventoryCustom;
use Polaris\lib\inventoryapi\InventoryAPI;
use Polaris\player\PolarisPlayer;

class CosmeticsManager
{
    use SingletonTrait;

    /**
     * @var Cosmetic[]
     */
    private array $cosmetics =  [];

    public function __construct()
    {
        $this->init();
        self::setInstance($this);
    }

    public function init(): void
    {
        $this->addCosmetic(new CrownCosmetic());
    }

    public function addCosmetic(Cosmetic $cosmetic): void
    {
        $this->cosmetics[$cosmetic->getInfo()->getSlot()] = $cosmetic;
    }

    public function sendInventory(PolarisPlayer $player): void
    {
        $inventory = InventoryAPI::createSimpleChest(true);
        for($size = $inventory->getSize() - 1; $size >= 0 ; $size--)
        {
            $inventory->setItem($size, VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::GRAY())->asItem()->setCustomName(" "));
        }

        foreach ($this->getAll() as $name => $cosmetic)
        {
            $inventory->setItem($cosmetic->getInfo()->getSlot(), VanillaItems::BONE()->setCustomName($cosmetic->getInfo()->getName()));
        }
        $inventory->setClickListener(function (PolarisPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot)
        {
            if($sourceItem->getId() === VanillaItems::BONE()->getId())
            {
                $this->cosmetics[$slot]->linkToPlayer($player);
                $inventory->onClose($player);
            }
        });

        $inventory->send($player);
    }

    public function getAll(): array
    {
        return $this->cosmetics;
    }

    public function getCosmetic(string $name): Cosmetic
    {
        return array_map(function (int $k,Cosmetic $v) use ($name) {
            if(strtolower($v->getInfo()->getName()) === strtolower($name)) {
                return $v;
            }
            throw new \UnhandledMatchError("Cannot find cosmetic $name");
        } , array_keys($this->cosmetics), array_values($this->cosmetics))[0];
    }
}
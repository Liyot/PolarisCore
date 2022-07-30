<?php

namespace Polaris\trait;


use pocketmine\block\BlockFactory;
use pocketmine\data\bedrock\EntityLegacyIds as LegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use Polaris\blocks\CustomPlate;
use Polaris\blocks\EndPlate;
use Polaris\entity\FloatingText;
use Polaris\entity\PearlEntity;
use Polaris\entity\ShulkerEntity;
use Polaris\games\Types\GetDown;
use Polaris\games\types\ShootCraft;
use Polaris\item\ItemManager;
use Polaris\item\Specialitem;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;
use Polaris\utils\FormReference;
use Polaris\utils\GameUtils;
use Polaris\games\types\Jump;

trait LoaderTrait{

    use VectorUtilsTrait;

    public function loadAll(): void
    {
        @mkdir(Polaris::getInstance()->getDataFolder()."games\\");
        $this->initManager();
        $this->loadCustomGames();
        $this->loadEntity();
        $this->loadBlocks();
    }

    //Todo: Try to found a way to manipulate the return value of the function.
    public function loadBlocks(): void
    {
        BlockFactory::getInstance()->register(new CustomPlate(), true);
        BlockFactory::getInstance()->register(new EndPlate(), true);
    }

    private function loadEntity(): void
    {
        EntityFactory::getInstance()->register(FloatingText::class, function(World $world, CompoundTag $nbt) : FloatingText{
            return new FloatingText(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [EntityIds::FALLING_BLOCK]);

        EntityFactory::getInstance()->register(PearlEntity::class, function (World $world, CompoundTag $nbt): PearlEntity{
            return new PearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['ThrownEnderpearl', 'minecraft:ender_pearl'], LegacyIds::ENDER_PEARL);

        EntityFactory::getInstance()->register(ShulkerEntity::class, function (World $world, CompoundTag $nbt): ShulkerEntity{
            return new ShulkerEntity(EntityDataHelper::parseLocation($nbt, $world), [false], null);
        }, ['ShulkerEntity'], LegacyIds::SHULKER_BULLET);

    }

    private function initManager(): void{
        $list = [new Specialitem( new ItemIdentifier(ItemIds::BOOK, 0), "Book", function (PolarisPlayer $player){
            $player->sendForm(FormReference::MDJForm($player));
        }),  new Specialitem(new ItemIdentifier(VanillaItems::RED_DYE()->getId(), VanillaItems::RED_DYE()->getMeta()), VanillaItems::RED_DYE()->getName(), function (PolarisPlayer $player){
            $player->getActualGame()?->leave($player);
        }),
            new Specialitem(new ItemIdentifier(351, 2), VanillaItems::GREEN_DYE()->getName(), function (PolarisPlayer $player){
                $player->teleport($player->getActualGame()?->getCheckPoint($player));
            }),];
        new ItemManager($list);
    }

    private function loadCustomGames(): void{
        for ($x = 6; $x > 0; $x--){
            new GetDown("getdownnn");
        }

        new ShootCraft();

        $blocks = ["-51:60:-62" => new CustomPlate(), "-53:60:-62" => new CustomPlate(),"-55:60:-62" => new CustomPlate() ,
            "-57:60:-62" => new CustomPlate(), "-59:60:-62" => new CustomPlate(), "-61:60:-62" => new CustomPlate(),
            "-63:60:-62" => new CustomPlate(), "-65:60:-62" => new EndPlate()];
        new Jump(2, 99999, PHP_INT_MAX,'Jump', new Position(-51, 61, -62, GameUtils::getSpawnWorld()), 5, $blocks);
    }
}
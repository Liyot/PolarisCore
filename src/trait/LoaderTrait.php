<?php

namespace Polaris\trait;


use pocketmine\block\BlockFactory;
use pocketmine\data\bedrock\EntityLegacyIds as LegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use Polaris\blocks\CustomPlate;
use Polaris\blocks\EndPlate;
use Polaris\command\cosmetics\CosmeticsCommand;
use Polaris\command\groups\GroupsCommand;
use Polaris\cosmetics\CosmeticsManager;
use Polaris\entity\FloatingText;
use Polaris\entity\PearlEntity;
use Polaris\entity\ShulkerEntity;
use Polaris\entity\VillagerEntity;
use Polaris\games\GameListener;
use Polaris\games\GameLoader;
use Polaris\games\types\RushGame;
use Polaris\item\ItemManager;
use Polaris\item\Specialitem;
use Polaris\listener\EntityListener;
use Polaris\listener\PacketListener;
use Polaris\listener\PlayerListener;
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
        $this->loadCommands();
        $this->loadEvents();
		$this->launchTask();
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

        EntityFactory::getInstance()->register(VillagerEntity::class, function(World $world, CompoundTag $nbt) : VillagerEntity{
            return new VillagerEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt,new EntitySizeInfo(1.0, 1.0), function(){});
        }, ['VillagerEntity']);

        EntityFactory::getInstance()->register(PearlEntity::class, function (World $world, CompoundTag $nbt): PearlEntity{
            return new PearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['ThrownEnderpearl', 'minecraft:ender_pearl'], LegacyIds::ENDER_PEARL);

        EntityFactory::getInstance()->register(ShulkerEntity::class, function (World $world, CompoundTag $nbt): ShulkerEntity{
            return new ShulkerEntity(EntityDataHelper::parseLocation($nbt, $world), [false], null);
        }, ['ShulkerEntity'], LegacyIds::SHULKER_BULLET);
        $a = true;
        foreach (GameUtils::getSpawnWorld()->getEntities() as $entity)
        {
            if ($entity->getPosition()->equals(new Vector3(-73, 72, -68)))
            {
                $a = false;
            }
        }
        if($a)
        {
            $path = Polaris::getInstance()->getDataFolder() . "image/logo10x10.png";
            $json = file_get_contents(Polaris::getInstance()->getDataFolder(). "json/logo10x10.geo.json");
            (new Human(new Location(-73, 72, -68, GameUtils::getSpawnWorld(), 0.0, 0.0),
                new Skin("Logo", GameUtils::PNGtoBYTES($path), "", "geometry.unknown",$json)))->spawnToAll();
        }
    }

    public function loadEvents(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new PacketListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new EntityListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new GameListener(), $this);
    }

    public function loadCommands(): void
    {
        $this->getServer()->getCommandMap()->register('', new GroupsCommand());
        $this->getServer()->getCommandMap()->register('', new CosmeticsCommand());
    }

    private function initManager(): void{
        $list = [new Specialitem( new ItemIdentifier(ItemIds::BOOK, 0), "Book", function (PolarisPlayer $player){
            $player->sendForm(FormReference::MDJForm());
        }),  new Specialitem(new ItemIdentifier(VanillaItems::RED_DYE()->getId(), 1), VanillaItems::RED_DYE()->getName(), function (PolarisPlayer $player){
            $player->getActualGame()?->leave($player);
        }),
            new Specialitem(new ItemIdentifier(351, 2), VanillaItems::GREEN_DYE()->getName(), function (PolarisPlayer $player){
                $player->teleport($player->getActualGame()?->getCheckPoint($player));
            }),];
        new ItemManager($list);
        CosmeticsManager::initCosmetics();

    }

    private function loadCustomGames(): void{
        for($i = 0; $i !== 5; $i++)
        {
            new RushGame("1vs1", $i);
        }
        /**for ($x = 6; $x > 0; $x--){
         * new GetDown("getdownnn");=
         * }
         * new ShootCraft();
         */
        $blocks = ["-51:60:-62" => new CustomPlate(), "-53:60:-62" => new CustomPlate(),"-55:60:-62" => new CustomPlate() ,
            "-57:60:-62" => new CustomPlate(), "-59:60:-62" => new CustomPlate(), "-61:60:-62" => new CustomPlate(),
            "-63:60:-62" => new CustomPlate(), "-65:60:-62" => new EndPlate()];
        new Jump(99999, PHP_INT_MAX, 8, new Position(-51, 61, -62, GameUtils::getSpawnWorld()), $blocks, GameUtils::getSpawnPosition());

    }

    public function launchTask(): void
    {
		Polaris::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function ()
		{
			foreach (GameLoader::getInstance()->getLobbyList() as $lobby)
			{
				$lobby->onTick();
			}
			foreach (GameLoader::getGameList() as $game){
				$game->onTick();

			}
		}), 1);
       /** Server::getInstance()->getAsyncPool()->submitTask(new class extends AsyncTask{
			public function onRun(): void{
				foreach (GameLoader::getGameList() as $game){
					$game->onTick();
				}
			}
		});*/
    }
}
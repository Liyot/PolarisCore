<?php

namespace Polaris\games\types;

use pocketmine\entity\Location;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use Polaris\blocks\CustomPlate;
use Polaris\blocks\EndPlate;
use Polaris\entity\FloatingText;
use Polaris\games\Game;
use Polaris\games\GameLoader;
use Polaris\games\MinorGameInterface;
use Polaris\item\ItemManager;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;
use Polaris\utils\GameUtils;

final class Jump extends TimedGames implements MinorGameInterface {

    public function __construct(public int $maxPlayer, public int $time, public int $maxcheckpoints, public Position $pos, array $nonPlacedblocks)
    {
        parent::__construct(GameUtils::ID_JUMP, $maxPlayer, $time, "Jump", $pos, $nonPlacedblocks);
    }

    protected function initListeners(): void{}

    public function onStop(): void
    {
        parent::onStop();
        $config = new Config(Polaris::getInstance()->getDataFolder() . "games/{$this->getName()}.json", 1);
        $config->set("timings", $this->bestTimings);
        $config->save();

        GameLoader::getInstance()->removeGame($this);
    }

    public function join(PolarisPlayer $player): void
    {
        if(!$player->getActualGame() instanceof self) {
            parent::join($player);
            $player->getInventory()->setItem(2, ItemManager::getInstance()->get(VanillaItems::RED_DYE()->getId())?->setCustomName("§cLeave"));

            $player->getInventory()->setItem(6, VanillaItems::GREEN_DYE()->setCustomName("§aGo to checkpoint"));
        }else{
            $this->nextCheckpoint($player);
        }
    }

    public function getStartPosition(): Position
    {
        return $this->pos;
    }
}
<?php

namespace Polaris\games\types;

use pocketmine\block\BlockFactory;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\item\VanillaItems;
use pocketmine\world\Position;
use Polaris\games\MinorGameInterface;
use Polaris\item\ItemManager;
use Polaris\item\Specialitem;
use Polaris\player\PolarisPlayer;
use Polaris\utils\GameUtils;
use Polaris\utils\ListenerUtils;

final class WallRun extends TimedGames implements MinorGameInterface
{

    public function __construct(Position $pos, public int $maxcheckpoints, array $nonPlacedblocks, protected Position $spawn)
    {
        parent::__construct(GameUtils::ID_WALLRUN,1, PHP_INT_MAX,"WallRun" ,$pos, $nonPlacedblocks);
        $this->pos = $pos;
    }

    public function join(PolarisPlayer $player): void
    {
        if(!$player->getActualGame() instanceof self) {
            parent::join($player);
            $player->getInventory()->setItem(2, ItemManager::getInstance()->get(VanillaItems::RED_DYE()->getId(), 1)?->setCustomName("§cLeave"));

        }else{
            $this->nextCheckpoint($player);
        }
    }

	protected function getSpawn(): Position
	{
		return $this->spawn;
	}

    public function initListeners(): void
    {
        $this->addCallback(ListenerUtils::BLOCK_PLACE, function (BlockPlaceEvent $event)
        {
            $player = $event->getPlayer();
            $block = $event->getBlock();

            foreach ($block->getAllSides() as $side)
            {
                if($side instanceof (BlockFactory::getInstance()->get(159, 14)))
                {
                    $event->cancel();
                }
            }
        });
    }
}
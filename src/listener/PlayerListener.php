<?php

namespace Polaris\listener;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Server;
use Polaris\blocks\CustomPlate;
use Polaris\blocks\EndPlate;
use Polaris\games\GameLoader;
use Polaris\games\types\Jump;
use Polaris\games\types\RushGame;
use Polaris\games\types\TimedGames;
use Polaris\item\Specialitem;
use Polaris\player\PolarisPlayer;
use Polaris\utils\GameUtils;
use Polaris\utils\PlayerUtils;

class PlayerListener implements Listener
{

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        if ($player instanceof PolarisPlayer) {
            $player->sendMessage("§l§b[§aPolaris§b] §aBienvenue sur Polaris !");
            $player->teleportToSpawn();
        }
    }

    /**
     * Petit feature bonus by Yanoox
     * Utilité : "voir ce que le joueur voit" utilise pour le mode specteur
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        if (!$player instanceof PolarisPlayer) return;
		$player->getActualGame()?->leave($player);
        if ($player->currentViewerPlayer !== null) {
            $player->currentViewerPlayer->removeViewer($player);
            $player->teleportToSpawn();
        }
        foreach ($player->getViewersPlayers() as $viewer) {
            $viewer->removeViewer($player);
            $viewer->teleportToSpawn();
            $viewer->sendMessage($player->getName() . " a quitté le serveur");
        }
    }

    /**
     * Petit feature bonus by Yanoox
     * Utilité : "voir ce que le joueur voit" utilise pour le mode specteur
     * @param PlayerEntityInteractEvent $event
     * @return void
     */
    public function onEntityInteract(PlayerEntityInteractEvent $event)
    {
        $player = $event->getPlayer();
        $entity = $event->getEntity();
        if (!$player instanceof PolarisPlayer) return;
        if ($entity instanceof PolarisPlayer && $player->isAbleToBeAViewerSpectator) {
            $entity->addViewer($player);
        }
    }

    public function onDamage(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        if ($player instanceof PolarisPlayer) {
            if (!$player->canDie()) {
                $event->cancel();
            }
        }
    }

    public function onExhaust(PlayerExhaustEvent $event)
    {
        $event->cancel();
    }

    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player instanceof PolarisPlayer) {
            if ($player->getPosition()->y <= 0 && is_null($player->getActualGame())) {
                $player->teleportToSpawn();
            }
            $block = $player->getWorld()->getBlock($player->getPosition(), false, false);
            $Actualgame = $player->getActualGame();
            if ($block instanceof CustomPlate) {
                if (!$Actualgame instanceof TimedGames) {
                    foreach (GameLoader::getGameList() as $game) {
                        if ($game instanceof TimedGames && $game->pos->distance($player->getPosition()) <= 1.5 && $game->pos->world === $player->getWorld()) {
                            $game->join($player);
							return;
                        }
                    }
                    return;
                }
                $Actualgame->nextCheckpoint($player);
            } elseif ($block instanceof EndPlate) {
                if($Actualgame instanceof TimedGames)
                {
                    $Actualgame->finish($player);
                }
            }
        }
    }

    public function onBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        if (Server::getInstance()->isOp($player->getName())) {
            if ($player->getPosition()->getWorld()->getDisplayName() === "Ma bite") { // MDRRR
                $event->cancel();
            }
        }
    }

    public function onCreate(PlayerCreationEvent $event)
    {
        $event->setPlayerClass(PolarisPlayer::class);
    }

    public function onReceive(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();

        if ($packet instanceof ModalFormResponsePacket) {
            $player = $event->getOrigin()->getPlayer();
            if ($player instanceof PolarisPlayer) {
                $player->getPlayerProperties()->setProperties("cleanScreen", true);
            }
        } elseif ($packet instanceof ModalFormRequestPacket) {
        }
    }

    public function onPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        if (Server::getInstance()->isOp($player->getName())) {
            if ($player->getPosition()->getWorld()->getDisplayName() === "Ma bite") {
                $event->cancel();
            }
        }
    }

    public function onUse(PlayerItemUseEvent $event)
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if (!$player instanceof PolarisPlayer) return;
        if ($item->getId() === 1) {
            GameLoader::getInstance()->getDisponibleGame("RushGame")->preJoin($player);
        }
        if ($item instanceof Specialitem) {
            $item->clickListener->__invoke($player);
        }
    }

    public function onHit(EntityDamageByEntityEvent $event)
    {
        $player = $event->getEntity();
        if ($player instanceof PolarisPlayer) {
            if (Server::getInstance()->isOp($player->getName())) {
                if ($player->getPosition()->getWorld()->getDisplayName() === "Ma bite") {
                    $event->cancel();
                }
            }
        }
    }
}

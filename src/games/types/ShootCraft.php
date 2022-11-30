<?php

namespace Polaris\games\types;

use pocketmine\entity\Location;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\world\Position;
use Polaris\entity\ShulkerEntity;
use Polaris\games\Game;
use Polaris\games\GameLoader;
use Polaris\games\Zone;
use Polaris\player\PolarisPlayer;
use Polaris\utils\GameUtils;
use Polaris\utils\ListenerUtils;


final class ShootCraft extends Game implements ZoneGame
{

    private Zone $zone;

    private array $cooldown = [];

    public function __construct()
    {

        GameLoader::getInstance()->addGame($this);

        Server::getInstance()->getWorldManager()->loadWorld("shootcraft");
        $world = Server::getInstance()->getWorldManager()->getWorldByName("shootcraft");
        $this->zone = new Zone("ShootCraft", new Position(-9999, 0, -9999, $world), $this, new Position(9999, 256, 9999, $world), new Position(500, 70, 250, $world));

        $this->addCallback('Start', function (Server $server) {
            $this->initProperties();
            $this->properties->setProperties('Starting', false)->setProperties('Running', true)->setProperties(GameUtils::PROPERTIES_ACCEPT_PLAYERS, true);
            foreach ($server->getOnlinePlayers() as $player) {
                $player->sendMessage("§l§b[§aShootCraft§b] §aLe shootcraft a commencé !");
            }
        });

        $this->addCallback('Stop', function (Server $server) {
            foreach ($this->zone->getEntities() as $entity) {
                $entity->close();
            }
            foreach ($server->getOnlinePlayers() as $player) {
                $player->sendMessage("§l§b[§aShootCraft§b] §aLe shootcraft a fini !");
            }
        });
        parent::__construct(GameUtils::ID_SHOOTCRAFT, PHP_INT_MAX, PHP_INT_MAX, "ShootCraft");
        $this->onCreation();
    }

    protected function initListeners(): void
    {
        $this->addCallback(ListenerUtils::PLAYER_ITEM_USE, function (PlayerItemUseEvent $event)
        {
            $player = $event->getPlayer();
            $item = $event->getItem();
            if ($player instanceof PolarisPlayer) {
                if ($player->getActualGame() instanceof ShootCraft) {
                    if ($item->getId() === ItemIds::STICK) {
                        $cooldown = $this->cooldown[$player->getName()] ?? 0;
                        if ($cooldown < time()) {
                            $location = Location::fromObject($player->getLocation()->add(0, 1, 0), $player->getWorld(), $player->getLocation()->yaw);
                            $entity = new ShulkerEntity($location, [false, "game" => $player->getActualGame()]);
                            $entity->spawnToAll();
                            $this->cooldown[$player->getName()] = time() + 5;
                            $entity->setMotion($player->getDirectionVector());
                        } else {
                            $event->cancel();
                            $player->sendMessage("§cVous ne pouvez pas utiliser cette arme pour le moment");
                        }
                    }
                }
            }
        });
    }

    public function getZone(): Zone
    {
        return $this->zone;
    }

    public function getName(): string
    {
        return 'ShootCraft';
    }

    public function join(PolarisPlayer $player): void
    {
        parent::join($player);
        $player->getInventory()->setItem(4, VanillaItems::STICK());
        $player->teleport($this->zone->getMainPosition());
    }

    public function onCreation(): void
    {
        $this->onStart();
    }

    public function onStart(): void
    {
        $this->processCallback('Start', Server::getInstance());
    }

    public function getGameId(): int
    {
        return GameUtils::ID_SHOOTCRAFT;
    }

    public function getTime(): int
    {
        return PHP_INT_MAX;
    }

    public function getMaxPlayers(): int
    {
        return PHP_INT_MAX;
    }

    public function onTick(): void
    {
    }

    public function onStop(): void
    {
        $this->processCallback('Stop', Server::getInstance());
        GameLoader::getInstance()->removeGame($this);
    }

    public function onLeave(PolarisPlayer $player): void
    {
        $this->leave($player, $this);
    }
}
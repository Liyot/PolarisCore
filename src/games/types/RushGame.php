<?php

namespace Polaris\games\types;

use pocketmine\block\Bed;
use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\world\World;
use Polaris\entity\VillagerEntity;
use Polaris\games\Game;
use Polaris\games\GameLoader;
use Polaris\games\GameProperties;
use Polaris\games\queue\Queue;
use Polaris\games\team\Team;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;
use Polaris\task\CooldownTask;
use Polaris\trait\callBackTrait;
use Polaris\trait\ConversionTrait;
use Polaris\trait\TeamTrait;
use Polaris\trait\VectorUtilsTrait;
use Polaris\utils\GameUtils;
use Polaris\utils\ListenerUtils;
use Polaris\utils\PlayerUtils;
use Polaris\utils\Scoreboard;

final class RushGame extends Game
{
    use callBackTrait;
    use VectorUtilsTrait;
    use TeamTrait;
    use ConversionTrait;

    const NAME = "Rush";
    const GAME_1VS1 = "1vs1";
    const GAME_2VS2 = "2vs2";
    const GAME_4VS4 = "4vs4";
    /**
     * @var PolarisPlayer[]
     */
    public array $players = [];

    /**
     * @var PolarisPlayer[]
     */
    public array $deadPlayers = [];

    public GameProperties $properties;

    private World $world;

    /**
     * @var VillagerEntity[]
     */
    private array $villagers;

    private Queue $queue;

    /**
     * @var VanillaBlocks[]
     */
    private array $forbiddenBlock = [];

    private array $generatorItems;

    private int $cooldown = 5;

    public function __construct(string $type, int $count)
    {

        $maxPlayer = match ($type) {
            self::GAME_1VS1 => 1,
            self::GAME_2VS2 => 4,
            self::GAME_4VS4 => 8,
        };

        $this->addTeam(new Team("Red", $maxPlayer), new Team("Blue", $maxPlayer));

        parent::__construct(GameUtils::ID_RUSHGAME, $maxPlayer,3, 0, "RushGame");

		$this->world = Server::getInstance()->getWorldManager()->getWorldByName($this->copyWorld("RushPattern\\RushFastPattern4vs4", "CurrentRush\\Rush$type.$count")); //TODO generate suffix
		GameLoader::getInstance()->addGame($this);

        $this->properties->setProperties("starting", true);
        $this->queue = new Queue($this->getName(), $maxPlayer, $this);

        $this->villagers = [
            /**WARNING: va falloir modifier le yaw bien comme il faut, ça dépend de la map là
             *  J'utiliserais un autre méthode si possible
             *  ajouter un callback interact et tout à chaque VillagerEntity
             *  Update: faut juste les faire fixer un point au centre de la map
             */
           "terroriste" => new VillagerEntity(Location::fromObject(new Vector3(331, 52, 311), $this->world, 0, 0), "terroriste", new EntitySizeInfo(1.0, 1.0), function(PolarisPlayer $player){}),
        ];
        foreach ($this->villagers as $villager) {
            $villager->spawnToAll();
        }
        $this->forbiddenBlock = [
            VanillaBlocks::OBSIDIAN(),
            VanillaBlocks::BANNER(),
            VanillaBlocks::ITEM_FRAME(),
            VanillaBlocks::CHEST(),
            VanillaBlocks::TRAPPED_CHEST(),
            VanillaBlocks::ENDER_CHEST(),
            VanillaBlocks::BED(),
            VanillaBlocks::SEA_LANTERN(),
        ];
        //item, vector, tick

        $this->generatorItems = [
            40 => [
                [VanillaItems::BRICK(), [
                    new Vector3(331, 52, 310),
                    new Vector3(331, 52, 312),
                    new Vector3(331, 52, 314),
                    new Vector3(331, 52, 316),
                    new Vector3(50, 10, 10),
                ],
                    //autres item à 40 ticks
                ],
            ],
        ];
        $this->properties->setProperties("GameInfo",
            [
                //les spawnpoints des teams
                "TeamPosition" =>
                    [
                        "Red" => new Vector3(270, 53, 259),
                        "Blue" => new Vector3(322, 53, 313)
                    ],
                //la position des lits
                "BedPosition" =>
                    [
                        // positions des lits des teams (bed & otherhalf)
                        "Red" => [new Vector3(270, 52, 258), new Vector3(270, 52, 259)],
                        "Blue" => [new Vector3(324, 52, 313), new Vector3(233, 52, 313)],
                        /*
                         * lit bonus
                         * /En partant de la base des rouges, donc base rouge = 1
                         */
                        "Base2" => [
                            "Vector" => [new Vector3(50, 50, 50), new Vector3(50, 50, 51)/** .. */],
                            "Red" => [4, null],
                            "Blue" => [4, null]
                        ],
                        "Base3" => [
                            "Vector" => [new Vector3(50, 50, 50), new Vector3(50, 50, 51)/** .. */],
                            "Red" => [6, "tnt"],
                            "Blue" => [4, null]
                        ],
                        "Base4" => [
                            "Vector" => [new Vector3(50, 50, 50), new Vector3(50, 50, 51)/** .. */],
                            "Red" => [8, null],
                            "Blue" => [4, null]
                        ],
                        "Base5" => [
                            "Vector" => [new Vector3(50, 50, 50), new Vector3(50, 50, 51)/** .. */],
                            "Red" => [2, null],
                            "Blue" => [8, null]
                        ],
                        "Base6" => [
                            "Vector" => [new Vector3(50, 50, 50), new Vector3(50, 50, 51)/** .. */],
                            "Red" => [2, null],
                            "Blue" => [4, "tnt"]
                        ],
                        "Base7" => [
                            "Vector" => [new Vector3(50, 50, 50), new Vector3(50, 50, 51)/** .. */],
                            "Red" => [2, null],
                            "Blue" => [2, null]
                        ],

                        //ne pas compté la base des bleu
                    ],
                //savoir si les lits des teams est envie ou non
                "BedAlive" => [
                    "Red" => true,
                    "Blue" => true,
                ],
                //les générateurs à tnt,
                "GeneratorsTnt" =>
                    [
                        "Red" => [new Vector3(100, 100, 100), new Vector3(101, 100, 101)/** .. */],
                        "Blue" => [new Vector3(100, 100, 100), new Vector3(101, 100, 101)/** .. */],
                    ],
                "Players" => [],
                "TeamAbsorption" => [
                    "Red" => 0,
                    "Blue" => 0,
                ],
            ]);
    }

    public function preJoin(PolarisPlayer $player): void
    {
        if ($player->canJoin($this) && count($this->players) < $this->getMaxPlayers()) {
            $this->getLobby()->join($player);
        } else {
            PlayerUtils::sendVerification($player, function (PolarisPlayer $player) {
                $player->hasAccepted[$this->getName()] = true;
                $this->getQueue()->addPlayer($player);
            }, " de vouloir rentré dans le " . $this->getName());
        }
    }

    public function join(PolarisPlayer $player): void
    {
        $player->setGamemode(GameMode::ADVENTURE());
        $player->sendMessage("§l§b[§a{$this->getName()}§b] §aVous avez rejoint le {$this->getName()} !");
        $this->players[$player->getName()] = $player;
        $this->getTeam("Red")->addPlayers($player);
        if (count($this->players) >= $this->getMaxPlayers()) {
            Polaris::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () {
                if (count($this->players) < $this->getMaxPlayers()) {
                    foreach ($this->players as $player) {
                        $player->sendMessage("Il manque " . $this->getMaxPlayers() - count($this->players) . " joueur pour commencer la partie.");
                    }
                    throw new CancelTaskException();
                }
                if ($this->cooldown <= 0) {
                    foreach ($this->players as $player) {
                        $player->setTeam($this->getTeam("Blue"));
                        $player->inGame = true;
                        $player->getInventory()->clearAll();
                        $this->properties->setNestedProperties("GameInfo.Players." . $player->getName(), ["kill" => 0, "death" => 0]);
                        $player->actualGame = $this;
                        $player->hasAccepted[$this->getName()] = false;
                    }
                    $this->onStart();
                    throw new CancelTaskException();
                }
                foreach ($this->players as $player) {
                    $player->sendMessage("La partie commence dans $this->cooldown seconde(s)");
                }
                $this->cooldown--;
            }
            ), 20);
        }
    }

    public function leave(PolarisPlayer $player): void
    {
        if (isset($this->deadPlayers[$player->getUniqueId()->toString()])) unset($this->deadPlayers[$player->getUniqueId()->toString()]);
        if (isset($this->players[$player->getName()])) unset($this->players[$player->getName()]);
        /**
         * pour la condition en dessous, c'est encore à voir (imaginons il a crash et il veut rejoin la game)
         */
        if ($this->properties->getProperties("GameInfo")["Players"][$player->getName()] !== null) $this->properties->removeProperties("GameInfo.Players." . $player->getName());
        foreach ($this->players as $p) {
            $this->sendScoreboard($p);
        }
        foreach ($player->getViewersPlayers() as $viewer) {
            $viewer->removeViewer($player);
            $viewer->teleportToSpawn();
            $viewer->sendMessage($player->getName() . " a quitté le serveur");
        }
        $this->processCallBack("initGame");
        parent::leave($player);
    }

    public function sendScoreboard(PolarisPlayer $player): void
    {
        $player->setScoreboard(new Scoreboard("§l§b[§a" . $this->getName() . "§b]", [
            str_replace("{time}", $this->secondToTimer($this->time * 20), $this->getScoreboardLine()[0]),
            str_replace(["{blue}", "{red}"], [count($this->getTeam("Blue")?->getPlayers()), count($this->getTeam("Red")?->getPlayers())], $this->getScoreboardLine()[1]),
            str_replace("{alive}", $this->properties->getProperties("GameInfo")["BedAlive"][$player->getTeam()->getName()], $this->getScoreboardLine()[2]),
            str_replace(["{kill}", "{death}"], [$this->timeToTwoChars((int)$this->properties->getProperties("GameInfo")["Players"][$player->getName()]["kill"]), $this->timeToTwoChars((int)$this->properties->getProperties("GameInfo")["Players"][$player->getName()]["death"])], $this->getScoreboardLine()[3]),
        ]));
    }

    public function onStart(): void
    {
        $this->properties->setProperties("starting", false);
        $this->properties->setProperties("running", true);
        //TODO: mettre le joueur en team s'il en a pas
        foreach ($this->players as $player) {
            $this->processCallback("LoadPlayer", $player);
        }

        foreach ($this->generatorItems as $tick => $data) {
            foreach ($data as $datum => $item) {
                $this->processCallBack("ItemGenerator", $item[0], $item[1], $tick);
            }
        }
    }

    public function onTick(): void
    {
        if($this->canTick()) {
            if ($this->properties->getProperties("running")) {
                foreach ($this->players as $player) {
                    $player->getScoreboard()->addLine(0, $player, str_replace("{time}", $this->secondToTimer($this->time / 20), $this->getScoreboardLine()[0]));
                }
                $this->time++;
            }
        }
        parent::onTick();
    }



    public function getScoreboardLine(): array
    {
        return [
            "Temps: {time}",
            "Equipes: {blue} | {red}",
            "Lit: {alive}",#✅, ❌
            "Stats: K: {kill} D: {death}",
        ];
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function getDeathPlayer(string $uuid): ?PolarisPlayer
    {
        return $this->deadPlayers[$uuid] ?? null;
    }

    public function getVillagers(): array
    {
        return $this->villagers;
    }
	protected function initListeners(): void
	{
		$this->addCallback(
			ListenerUtils::ENTITY_DAMAGE, function (EntityDamageEvent $event) {
				$victim = $event->getEntity();
				if (!$victim instanceof PolarisPlayer) return;
				if ($event instanceof EntityDamageByEntityEvent) {
					$damager = $event->getDamager();
					if (!$damager instanceof PolarisPlayer) return;
					if ($victim->getTeam()->getName() === $damager->getTeam()->getName()) {
						$event->cancel();
					} else {
						if ($event->getFinalDamage() >= $victim->getHealth()) {
							$event->cancel();
							$this->processCallback("kill", $victim, $damager, null);
						}
					}
					return;
				}
				if ($event->getFinalDamage() >= $victim->getHealth()) {
					$event->cancel();
					$this->processCallBack("kill", $victim, null, $event->getCause());
				}
			});
		$this->addCallback(ListenerUtils::ENTITY_EXPLODE,  function (EntityExplodeEvent $event)
		{
		//pas au point, j'était fatigué

			$player = $event->getEntity()?->getOwningEntity();
			if (!$player instanceof PolarisPlayer) return;
			$properties = $this->properties->getProperties("GameInfo")["BedPosition"];
			foreach ($event->getBlockList() as $block) {
				if ($block instanceof Bed) {
					$event->setBlockList(array_diff([$block->getOtherHalf()], $event->getBlockList()));
					switch (array_keys($properties)) {
						case "Base2":
							$player->setAbsorption($player->getAbsorption() + (float)$properties["Base2"][$player->getTeam()?->getName()][0]);
							foreach ($this->getWorld()->getPlayers() as $p) {
								$p->sendMessage($player->getName() . "a explosé un lit et octroie un bonus de " . (float)$properties["Base7"][$player->getTeam()->getName()][0] . " ❤ à son équipe");
							}
							if ($properties["Base2"][$player->getTeam()?->getName()][1] !== null) {
								$this->processCallBack("GeneratorsTnt", $player->getTeam()?->getName());
							}
							break;
						case "Base3":
							$player->setAbsorption($player->getAbsorption() + (float)$properties["Base3"][$player->getTeam()?->getName()][0]);
							foreach ($this->getWorld()->getPlayers() as $p) {
								$p->sendMessage($player->getName() . "a explosé un lit et octroie un bonus de " . (float)$properties["Base7"][$player->getTeam()->getName()][0] . " ❤ à son équipe");
							}
							if ($properties["Base3"][$player->getTeam()?->getName()][1] !== null) {
								$this->processCallBack("GeneratorsTnt", $player->getTeam()?->getName());
							}
							break;
							case "Base4":
								$player->setAbsorption($player->getAbsorption() + (float)$properties["Base4"][$player->getTeam()->getName()][0]);
								foreach ($this->getWorld()->getPlayers() as $p) {
									$p->sendMessage($player->getName() . "a explosé un lit et octroie un bonus de " . (float)$properties["Base7"][$player->getTeam()->getName()][0] . " ❤ à son équipe");

								}
								if ($properties["Base4"][$player->getTeam()?->getName()][1] !== null) {
									$this->processCallBack("GeneratorsTnt", $player->getTeam()?->getName());
								}
								break;
							case "Base5":
								$player->setAbsorption($player->getAbsorption() + (float)$properties["Base5"][$player->getTeam()->getName()][0]);
								foreach ($this->getWorld()->getPlayers() as $p) {
									$p->sendMessage($player->getName() . "a explosé un lit et octroie un bonus de " . (float)$properties["Base7"][$player->getTeam()->getName()][0] . " ❤ à son équipe");
								}
								if ($properties["Base5"][$player->getTeam()?->getName()][1] !== null) {
									$this->processCallBack("GeneratorsTnt", $player->getTeam()?->getName());
								}
								break;
								case "Base6":
									$player->setAbsorption($player->getAbsorption() + (float)$properties["Base6"][$player->getTeam()->getName()][0]);
									foreach ($this->getWorld()->getPlayers() as $p) {
										$p->sendMessage($player->getName() . "a explosé un lit et octroie un bonus de " . (float)$properties["Base7"][$player->getTeam()->getName()][0] . " ❤ à son équipe");
									}
									if ($properties["Base6"][$player->getTeam()?->getName()][1] !== null) {
										$this->processCallBack("GeneratorsTnt", $player->getTeam()?->getName());
									}
									break;
								case "Base7":
									$player->setAbsorption($player->getAbsorption() + (float)$properties["Base7"][$player->getTeam()->getName()][0]);
									foreach ($this->getWorld()->getPlayers() as $p) {
										$p->sendMessage($player->getName() . "a explosé un lit et octroie un bonus de " . (float)$properties["Base7"][$player->getTeam()->getName()][0] . " ❤ à son équipe");
									}
									if ($properties["Base7"][$player->getTeam()->getName()][1] !== null) {
										$this->processCallBack("GeneratorsTnt", $player->getTeam()->getName());
									}
									break;
								default:
									foreach ($event->getBlockList() as $blockList) {
										if (!in_array($block, $this->forbiddenBlock)) continue;
										$event->setBlockList(array_diff([$blockList], $this->forbiddenBlock));
									}
									foreach ($event->getBlockList() as $blockList) {
										if ($blockList instanceof Bed) {
											if (in_array($block->getPosition(), $properties[$player->getTeam()->getName()])) {
												$event->setBlockList(array_diff([$blockList], $this->forbiddenBlock));
												$player->sendMessage("§cVous ne pouvez pas casser le lit de votre équipe");
											} else {
												$this->processCallBack("TeamBedExplode", $player, $block);
											}
										}
									}
									break;
					}
				}
			}
		});
	$this->addCallback(ListenerUtils::BLOCK_BREAK, function (BlockBreakEvent $event) {
		$block = $event->getBlock();
		if (in_array($block, $this->forbiddenBlock)) {
			$event->cancel();
		}
	});

	$this->addCallback("GeneratorsTnt", function (string $team) {
		$this->processCallBack("ItemGenerator", VanillaBlocks::TNT()->asItem(), $this->properties->getPropertiesList()["GameInfo"]["GeneratorsTnt"][$team], 20 * 60, true);
	});

	$this->addCallback("ItemGenerator", function (Item $item, array $vectors, int $tick, bool $spawnWhenCalled = false) {
		if ($spawnWhenCalled) {
			foreach ($vectors as $vector3) {
				if (!$vector3 instanceof Vector3) continue;
				$this->getWorld()->dropItem($vector3, $item, new Vector3(0, 0.2, 0));
			}
		}
		Polaris::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($item, $vectors) {
			foreach ($vectors as $vector3) {
				if (!$vector3 instanceof Vector3) continue;
				$this->getWorld()->dropItem($vector3, $item, new Vector3(0, 0.2, 0));
			}
		}), $tick);
	});

	$this->addCallback("LoadPlayer", function (PolarisPlayer $player) {
		$player->setHealth(20);
		$this->sendScoreboard($player);
		$player->setAbsorption($this->properties->getProperties("GameInfo")["TeamAbsorption"][$player->getTeam()?->getName()] ? null : 0);
		$pos = $this->properties->getProperties("GameInfo")["TeamPosition"][$player->getTeam()?->getName()];
		$this->getWorld()->loadChunk($pos->x, $pos->z);
		$player->teleport(Location::fromObject($pos, $this->getWorld()));
		$sword = VanillaItems::IRON_SWORD();


		/** @var Armor[] $armors */
		$armors = [ //TODO: check enchantment
			VanillaItems::LEATHER_CAP(),
			VanillaItems::LEATHER_TUNIC(),
			VanillaItems::LEATHER_PANTS(),
			VanillaItems::LEATHER_BOOTS()
		];

		//TODO: atout (genre 16 blocks dès le départ)
		$flint = VanillaItems::FLINT_AND_STEEL();
		$player->getInventory()->setItem(0, $sword);
		$player->getInventory()->setItem(1, $flint);
		switch ($player->getTeam()->getName()) {
			case "Red":
				$player->getArmorInventory()->setHelmet($armors[0]->setCustomColor(new Color(0xb0, 0x2e, 0x26)));
				$player->getArmorInventory()->setChestplate($armors[1]->setCustomColor(new Color(0xb0, 0x2e, 0x26)));
				$player->getArmorInventory()->setLeggings($armors[2]->setCustomColor(new Color(0xb0, 0x2e, 0x26)));
				$player->getArmorInventory()->setBoots($armors[3]->setCustomColor(new Color(0xb0, 0x2e, 0x26)));
				break;
			case "Blue":
				$player->getArmorInventory()->setHelmet($armors[0]->setCustomColor(new Color(0x3c, 0x44, 0xaa)));
				$player->getArmorInventory()->setChestplate($armors[1]->setCustomColor(new Color(0x3c, 0x44, 0xaa)));
				$player->getArmorInventory()->setLeggings($armors[2]->setCustomColor(new Color(0x3c, 0x44, 0xaa)));
				$player->getArmorInventory()->setBoots($armors[3]->setCustomColor(new Color(0x3c, 0x44, 0xaa)));
				break;
		}

		$delay = 5;
		Polaris::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $sword, $delay): void {
			if ($delay <= 0) {
				if ($player?->getInventory()->contains($sword)) {
					$player->getInventory()->removeItem($sword);
					$player->getInventory()->addItem($sword->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2)));
				}
				throw new CancelTaskException();
			}
			$delay--;
		}), 5 * 20);
	});

	$this->addCallback("TeamBedExplode", function (PolarisPlayer $player, Bed $block) {
		switch ($player->getTeam()->getName()) {
			case "Red":
				if (in_array($block, $this->properties->getProperties("GameInfo")["BedPosition"]["Blue"], $block) or in_array($block, $this->properties->getProperties("GameInfo")["BedPosition"]["Blue"], $block->getOtherHalf())) {
					$this->properties->setNestedProperties("GameInfo.BedAlive.Blue", false);
					//TODO: scoreboard
					foreach ($this->getWorld()->getPlayers() as $p) {
						if (!$p instanceof PolarisPlayer) return;
						$p->sendMessage($player->getName() . " a explosé le lit de l'équipe Bleu");
						$this->sendScoreboard($p);
					}
					foreach ($this->players as $p) {
						if ($p->getTeam()->getName() === "Blue") {
							$p->sendMessage("Votre lit d'équipe vient d'être détruit, vous ne réapparaîtrez plus");
						}
					}
				}
				break;
			case "Blue":
				if (in_array($block, $this->properties->getProperties("GameInfo")["BedPosition"]["Red"], $block) or in_array($block, $this->properties->getProperties("GameInfo")["BedPosition"]["Red"], $block->getOtherHalf())) {
					$this->properties->setNestedProperties("GameInfo.BedAlive.Red", false);
					foreach ($this->getWorld()->getPlayers() as $p) {
						if (!$p instanceof PolarisPlayer) return;
						$p->sendMessage($player->getName() . " a explosé le lit de l'équipe Bleu");
						$this->sendScoreboard($p);
					}
					foreach ($this->players as $p) {
						if ($p->getTeam()->getName() === "Red") {
							$p->sendMessage("Votre lit d'équipe vient d'être détruit, vous ne réapparaîtrez plus");
						}
					}
				}
				break;
		}
	});

	$this->addCallback("kill", function (PolarisPlayer $victim, ?PolarisPlayer $killer = null, ?int $cause = null) {
		//TODO: add $killer kill $point + $victim death
		//Todo: check if block position is dead
		if ($killer != null) {
			$this->properties->setNestedProperties("GameInfo.Players." . $killer->getUniqueId()->toString() . ".kill",
				(int)$this->properties->getProperties("GameInfo")["Players"][$killer->getUniqueId()->toString()]["kill"] + 1);
			$this->sendScoreboard($killer);
			$victim->sendMessage("$killer vous a tuez");
			$killer->sendMessage("Vous avez tuez $victim");
		} else {
			switch ($cause) {
				case EntityDamageEvent::CAUSE_SUFFOCATION:
					foreach ($this->getWorld()->getPlayers() as $p) {
						$p->sendMessage("La maman à $victim ne lui a pas appris à respirer au bon moment");
					}
					break;
				case EntityDamageEvent::CAUSE_FALL:
					foreach ($this->getWorld()->getPlayers() as $p) {
						$p->sendMessage("$victim s'est pris pour Spider-Man");
					}
					break;
				case EntityDamageEvent::CAUSE_VOID:
					foreach ($this->getWorld()->getPlayers() as $p) {
						$p->sendMessage("$victim n'a pas regardé où il marchait Soit plus prudent la prochaine fois :)");
					}
					break;
				case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
					foreach ($this->getWorld()->getPlayers() as $p) {
						$p->sendMessage("$victim s'est pris un 14 juillet dans la tête");
					}
					break;
				case null;
					foreach ($this->getWorld()->getPlayers() as $p) {
						$p->sendMessage("$killer a tué $victim");
					}
					break;
			}
		}

		$this->properties->setNestedProperties("GameInfo.Players." . $victim->getUniqueId()->toString() . ".kill",
			(int)$this->properties->getProperties("GameInfo")["Players"][$victim->getUniqueId()->toString()]["kill"] + 1);

		$this->sendScoreboard($victim);

		if ($this->properties->getProperties("GameInfo")["BedAlive"][$victim->getTeam()->getName()]) {
			$this->processCallback("LoadPlayer", $victim);

			Polaris::getInstance()->getScheduler()->scheduleRepeatingTask(new CooldownTask(5,
				function (PolarisPlayer $victim) {
					$victim->sendMessage("§l§b[§a{$this->getName()}§b] §akill dans §e{time} §asecondes");
					$this->processCallback("LoadPlayer", $victim);
				}, $victim
			), 20);

		} else {
			$this->deadPlayers[$victim->getUniqueId()->toString()] = $victim;
			$victim->sendTitle("§cMort");
			$victim->sendSubTitle("§cVous ne pouvez plus réapparaître");
			$victim->setGamemode(GameMode::SPECTATOR());
			$victim->isAbleToBeAViewerSpectator = true;
		}

		$this->processCallBack("initGame");
	});

	$this->addCallback("initGame", function () {
		foreach ($this->getTeams() as $team) {
			if (count($team->getPlayers()) === 0) {
				foreach ($team->getPlayers() as $player) {
					$player->sendTitle("§aPerdu !", "Votre équipe a perdu.", -1, 20, 20 * 2);
				}
				foreach ($this->players as $player) {
					if ($player->getTeam()?->getName() !== $team->getName()) {
						$player->sendTitle("§6Victoire !", "Votre équipe a gagné !", -1, 20, 20 * 2);
					}
				}
				$this->onStop();
				return;
			}
		}
	});

	$this->addCallback("end", function () {
		!Server::getInstance()->getWorldManager()->isWorldLoaded($this->world->getFolderName()) || Server::getInstance()->getWorldManager()->unloadWorld($this->world);
		Filesystem::recursiveUnlink(GameUtils::getRushWorldDir() . $this->world->getFolderName());
		Polaris::getInstance()->getLogger()->notice("[RUSH] Disabling Rush " . $this->world->getFolderName());
		var_dump($this->world->isLoaded());

		//TODO: give xp, + 1 win data, tp lobby, reset inventory, reset team, reset gamemode, reset inGame
	});
}

	public function onStop(): void
	{
		$this->properties->setProperties("running", false);
		$this->properties->setProperties("ending", true);
		$this->processCallback("end");
		var_dump($this->count);
		GameLoader::getInstance()->removeGame($this);
	}
}
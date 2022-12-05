<?php

namespace Polaris\games\lobby;

use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldManager;
use Polaris\blocks\CustomPlate;
use Polaris\blocks\EndPlate;
use Polaris\games\Game;
use Polaris\games\GameLoader;
use Polaris\games\types\Jump;
use Polaris\games\types\WallRun;
use Polaris\player\PolarisPlayer;
use Polaris\trait\VectorUtilsTrait;
use Polaris\utils\Scoreboard;

class WaitingLobby
{

    use VectorUtilsTrait;

    private World $world;

    private Jump $jump;
    private WallRun $wallRun;

    private int $delay = 20 * 10;
    /**
     * @var PolarisPlayer[]
     */
    private array $players = [];

    public function __construct(private Game $game)
    {
        $this->world = Server::getInstance()->getWorldManager()->getWorldByName($this->copyWorld("Lobi", "Lobi".count(GameLoader::getInstance()->getLobbyList())));
		Server::getInstance()->getWorldManager()->loadWorld($this->world->getFolderName());
        $blocks = ["16:85:-216" => new CustomPlate(), "33:82:-215" => new EndPlate()];
        $this->wallRun = new WallRun(new Position(15, 85, -216, $this->world), 2, $blocks, $this->getSpawn());
        $blocks = ["-19:86:-262" => new CustomPlate(), "-19:86:-266" => new CustomPlate(), "-19:86:-270" => new CustomPlate(), "-19:86:-273" => new CustomPlate(), "-19:86:-275" => new EndPlate()];
        $this->jump = new Jump(9999, PHP_INT_MAX, 5, new Position(-19, 86, -261, $this->world), $blocks, $this->getSpawn());
    }

    public function getWallRun(): WallRun
    {
        return $this->wallRun;
    }

    public function getJump(): Jump
    {
        return $this->jump;
    }

	/**
	 * @return PolarisPlayer[]
	 */
	public function getPlayers(): array
	{
		return $this->players;
	}

    public function __destruct()
    {
        Server::getInstance()->getLogger()->notice(TextFormat::AQUA. "Disabling Lobby number " . count(GameLoader::getInstance()->getLobbyList()));
		$name = $this->world->getFolderName();
		!Server::getInstance()->getWorldManager()->isWorldLoaded($this->world->getFolderName()) || Server::getInstance()->getWorldManager()->unloadWorld($this->world);
        Filesystem::recursiveUnlink(Server::getInstance()->getDataPath() . "worlds/" .$name);
		$this->game->onStop();
    }

    public function onTick(): void
    {
		$scoreboard = new Scoreboard("§l§aPolaris§r§7",
            [
                $this->game->getName(),
                count($this->getPlayers()) . "/".  $this->game->getMinPlayers(),
				round($this->delay / 20) . "'s restantes"
            ]
        );
		if(!empty($this->players))
		{
			foreach ($this->players as $player)
			{
				if($this->delay < 20 * 5)
				{
					$player->sendSubTitle(($this->delay < 2 ? "§4" : "§2"). $this->delay);
				}

				$player->setScoreboard($scoreboard);
			}

			if(count($this->getPlayers()) >= $this->game->getMinPlayers())
			{
				$this->delay = 20 * 10;
			}

			if($this->delay === 0)
			{
				foreach ($this->getPlayers() as $player)
				{
					$this->redirect($player);
				}
				$this->__destruct();
			}
			$this->delay--;
		}
    }

    public function join(PolarisPlayer $player): void
    {
		$this->players[$player->getUniqueId()->toString()] = $player;
        $player->teleport($this->getSpawn());
    }

	final protected function redirect(PolarisPlayer $player): void
	{
		if ($this->isInLobby($player))
		{
			if (count($this->players) >= $this->game->getMinPlayers())
			{
				unset($this->players[$player->getUniqueId()->toString()]);
				$this->game->join($player);
				return;
			}
			$this->leave($player);
			$player->sendMessage("Nous n'avons pas pu trouver assez de joueur pour lancer la partie");
		}
	}

	public function leave(PolarisPlayer $player): void
	{
		if (isset($this->players[$player->getUniqueId()->toString()]))
		{
			$player->teleportToSpawn();
			unset($this->players[$player->getUniqueId()->toString()]);
		}
	}

	final public function isInLobby(PolarisPlayer $player): bool
	{
		return isset($this->players[$player->getUniqueId()->toString()]);
	}

    public function getSpawn(): Position
    {
        return Position::fromObject(new Vector3(-19, 86, -258), $this->world);
    }
}
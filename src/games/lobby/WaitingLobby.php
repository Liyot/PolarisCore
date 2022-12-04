<?php

namespace Polaris\games\lobby;

use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
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

    private int $delay = 20 * 60 * 5;
    /**
     * @var PolarisPlayer[]
     */
    private array $players = [];

    public function __construct(private Game $game)
    {
        $this->world = Server::getInstance()->getWorldManager()->getWorldByName($this->copyWorld("Lobi", "Lobi".count(GameLoader::getInstance()->lobby)));
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
        Server::getInstance()->getLogger()->notice(TextFormat::AQUA. "Disabling Lobby number " . count(GameLoader::getInstance()->lobby));
        Filesystem::recursiveUnlink(Server::getInstance()->getDataPath() . "worlds/" .$this->world->getFolderName());
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
				$this->game->join($player);
			}
		}
		$this->delay--;
    }

    public function join(PolarisPlayer $player): void
    {
		$this->players[$player->getUniqueId()->toString()] = $player;
        $player->teleport($this->getSpawn());
    }

    public function getSpawn(): Position
    {
        return Position::fromObject(new Vector3(-19, 86, -258), $this->world);
    }
}
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

class WaitingLobby
{

    use VectorUtilsTrait;

    private World $world;

    private Jump $jump;
    private WallRun $wallRun;

    private int $delay = 20 * 60;

    private array $players = [];

    public function __construct()
    {
        $this->world = Server::getInstance()->getWorldManager()->getWorldByName($this->copyWorld("Lobi", "Lobi".count(GameLoader::getInstance()->lobby)));
        $block = ["16:85:-216" => new CustomPlate(), "33:82:-215" => new EndPlate()];
        $this->wallRun = new WallRun(new Position(15, 85, -216, $this->world), 2, $block);
        $blocks = ["-19:86:-262" => new CustomPlate(), "-19:86:-266" => new CustomPlate(), "-19:86:-270" => new CustomPlate(), "-19:86:-273" => new CustomPlate(), "-19:86:-275" => new EndPlate()];
        $this->jump = new Jump(9999, PHP_INT_MAX, 9, new Position(-19, 86, -261, $this->world), $blocks);
    }

    public function getWallRun(): WallRun
    {
        return $this->wallRun;
    }

    public function getJump(): Jump
    {
        return $this->jump;
    }

    public function __destruct()
    {
        Server::getInstance()->getLogger()->notice(TextFormat::AQUA. "Disabling Lobby number " . count(GameLoader::getInstance()->lobby));
        Filesystem::recursiveUnlink(Server::getInstance()->getDataPath() . "worlds/" .$this->world->getFolderName());
    }

    public function onTick(): void
    {
    }

    public function join(PolarisPlayer $player): void
    {
        $player->teleport($this->getSpawn());
    }

    public function getSpawn(): Position
    {
        return Position::fromObject(new Vector3(-19, 86, -258), $this->world);
    }
}
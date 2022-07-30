<?php

namespace Polaris\trait;

use JetBrains\PhpStorm\Pure;
use pocketmine\entity\Entity;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\Server;
use pocketmine\utils\Binary;
use pocketmine\utils\Filesystem;
use pocketmine\world\format\io\data\BedrockWorldData;
use pocketmine\world\WorldManager;
use Polaris\player\PolarisPlayer;
use Webmozart\PathUtil\Path;

trait VectorUtilsTrait{

    public PolarisPlayer|null $player;

    #[Pure] public function getMatchedVector(int $y, int $multiplier): Vector3{
        $vector = match ($this->player->getHorizontalFacing()){
            Facing::NORTH => [1 * $multiplier, $y, 0],
            Facing::SOUTH => [-1 * $multiplier, $y, 0],
            Facing::EAST  => [0, $y, -1 * $multiplier],
            Facing::WEST  => [0, $y, 1 * $multiplier],
        };
        return new Vector3($vector[0], $vector[1], $vector[2]);
    }

    /**
     * @param Entity $entity
     * @param Vector3[] $pos
     * @return bool
     */
    public function inZone(Entity $entity, array $pos): bool{
        $playerPos = $entity->getPosition();
        return $pos[0]->getX() <= $playerPos->getX() && $pos[1]->getX() >= $playerPos->getX() && $pos[0]->getY() <= $playerPos->getY()
            && $pos[1]->getY() >= $playerPos->getY() && $pos[0]->getZ() <= $playerPos->getZ() && $pos[1]->getZ() >= $playerPos->getZ();
    }

    public function removeWorld(string $worldPath): void{
        chmod($worldPath, 0777);
        Filesystem::recursiveUnlink($worldPath);
    }

    public function copyWorld(string $from, string $name ): string{
        $server = Server::getInstance();
        @mkdir($server->getDataPath() . "/worlds/$name/", 0777);
        @mkdir($server->getDataPath() . "/worlds/$name/db/", 0777);
        copy($server->getDataPath() . "/worlds/" . $from. "/level.dat", $server->getDataPath() . "/worlds/$name/level.dat");
        $oldWorldPath = $server->getDataPath() . "/worlds/$from/level.dat";
        $newWorldPath = $server->getDataPath() . "/worlds/$name/level.dat";

        $oldWorldNbt = new BedrockWorldData($oldWorldPath);
        $newWorldNbt = new BedrockWorldData($newWorldPath);

        $worldData = $oldWorldNbt->getCompoundTag();
        $newWorldNbt->getCompoundTag()->setString("LevelName", $name);


        $nbt = new LittleEndianNbtSerializer();
        $buffer = $nbt->write(new TreeRoot($worldData));
        file_put_contents(Path::join($newWorldPath), Binary::writeLInt(BedrockWorldData::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
        $this->copyDir($server->getDataPath() . "/worlds/" . $from . "/db", $server->getDataPath() . "/worlds/$name/db/");

        return $name;
    }

    public function copyDir($from, $to): void
    {
        $to = rtrim($to, "\\/") . "/";
        /** @var \SplFileInfo $file */
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($from)) as $file){
            if($file->isFile()){
                $target = $to . ltrim(substr($file->getRealPath(), strlen($from)), "\\/");
                $dir = dirname($target);
                if(!is_dir($dir)){
                    mkdir(dirname($target), 0777, true);
                }
                @copy($file->getRealPath(), $target);
            }
        }
    }

}
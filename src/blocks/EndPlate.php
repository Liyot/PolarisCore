<?php

namespace Polaris\blocks;

use pocketmine\block\BlockBreakInfo as BreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\BlockToolType as ToolType;
use pocketmine\block\PressurePlate;
use pocketmine\item\ToolTier;
use pocketmine\Server;
use Polaris\player\PolarisPlayer;

class EndPlate extends PressurePlate{

    protected bool $pressed = false;

    public function __construct(){
        $weightedPressurePlateBreakInfo = new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
        parent::__construct(new BlockIdentifier(BlockLegacyIds::LIGHT_WEIGHTED_PRESSURE_PLATE, 0),
            "Weighted Pressure Plate Light", $weightedPressurePlateBreakInfo );
    }

    protected function writeStateToMeta() : int{
        return $this->pressed ? BlockLegacyMetadata::PRESSURE_PLATE_FLAG_POWERED : 0;
    }

    public function readStateFromData(int $id, int $stateMeta) : void{
        $this->pressed = ($stateMeta & BlockLegacyMetadata::PRESSURE_PLATE_FLAG_POWERED) !== 0;
    }

    public function getStateBitmask() : int{
        return 0b1;
    }

    public function isPressed() : bool{ return $this->pressed; }

    /** @return $this */
    public function setPressed(bool $pressed) : self{
        $this->pressed = $pressed;
        return $this;
    }

    public function getPressedPlayer(): ?PolarisPlayer{
        foreach (Server::getInstance()->getOnlinePlayers() as $player){
            if($player->getPosition()->distance($this->getPosition()) <= 1){
                if($player instanceof PolarisPlayer){
                    return $player;
                }
            }
        }
        return null;
    }
}
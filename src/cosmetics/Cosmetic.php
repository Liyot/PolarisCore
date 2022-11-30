<?php

namespace Polaris\cosmetics;

use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\Server;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;
use Polaris\utils\GameUtils;

class Cosmetic
{

    protected Skin $skin;

    /**
     * @param CosmeticInfo $info
     */
    public function __construct(private CosmeticInfo $info)
    {
    }

    /**
     * @throws \JsonException
     */
    public function generateSkin(): Skin
    {
        $imgData = GameUtils::PNGtoBYTES(($folder = Polaris::getInstance()->getDataFolder()). "image/".$this->info->getImageName());
        $jsonData = file_get_contents($folder ."json/".$this->info->getJsonName());
        return new Skin($this->info->getImageName(), $imgData, "","geometry.geometry.humanoid", $jsonData);
    }

    public function getInfo(): CosmeticInfo
    {
        return $this->info;
    }

    /**
     * @throws \JsonException
     */
    public function linkToPlayer(PolarisPlayer $player)
    {
        $entity = new Human($player->getLocation(), $this->generateSkin());
        $entity->spawnToAll();
        $packet = SetActorLinkPacket::create(new EntityLink($player->getId(), $entity->getId(), EntityLink::TYPE_RIDER, true, true));
        $player->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, new Vector3(0, 3, 0));
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, true);
        Server::getInstance()->broadcastPackets(Server::getInstance()->getOnlinePlayers(), [$packet]);
        $player->sendPopup($this->getInfo()->getName() ." a bien été équipé(e)");
        $player->link($entity);
    }
}
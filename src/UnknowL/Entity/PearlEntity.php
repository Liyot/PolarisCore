<?php

namespace UnknowL\Entity;

use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\Server;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;
use UnknowL\Player\PolarisPlayer;

class PearlEntity extends Throwable{

    public static function getNetworkTypeId() : string{ return EntityIds::ENDER_PEARL; }

    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
    }

    public function onUpdate(int $currentTick): bool
    {
        $owner = $this->getOwningEntity();
        if(!is_null($owner)){
            $this->riding(true);
        }
        if(!is_null($owner) && $this->isFlaggedForDespawn() ) {
            $this->riding(false);
        }
        return parent::onUpdate($currentTick); // TODO: Change the autogenerated stub
    }

    public function riding(bool $ride): void{
        $owner = $this->getOwningEntity();
        if($owner instanceof PolarisPlayer){
            $link = new EntityLink($this->getId(),$owner->getId() ,EntityLink::TYPE_PASSENGER, false, true);
            $packet = new SetActorLinkPacket();
            $packet->link = $link;
            $owner->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, new Vector3(0, $ride ? 1.45 : 0, 0));
            $owner->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, $ride);
            Server::getInstance()->broadcastPackets($this->getViewers(), [$packet]);
            $owner->setRiding($ride);
        }
    }

    protected function onHit(ProjectileHitEvent $event) : void{
        $this->riding(false);
        $this->flagForDespawn();
    }
}
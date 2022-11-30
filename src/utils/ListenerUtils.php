<?php

namespace Polaris\utils;

use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockItemPickupEvent;
use pocketmine\event\block\BlockMeltEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\BlockTeleportEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class ListenerUtils
{


    //BLOCK
    public const BLOCK_BREAK = BlockBreakEvent::class;
    public const BLOCK_BURN = BlockBurnEvent::class;
    public const BLOCK_GROW = BlockGrowEvent::class;
    public const BLOCK_ITEM_PICKUP = BlockItemPickupEvent::class;
    public const BLOCK_MELT = BlockMeltEvent::class;
    public const BLOCK_PLACE = BlockPlaceEvent::class;
    public const BLOCK_SPREAD = BlockSpreadEvent::class;
    public const BLOCK_TELEPORT = BlockTeleportEvent::class;
    public const BLOCK_UPDATE = BlockUpdateEvent::class;

    //PLAYER
    public const PLAYER_MOVE = PlayerMoveEvent::class;
    public const PLAYER_SNEAK = "pocketmine\event\player\PlayerSneakEvent";
    public const PLAYER_ITEM_USE = PlayerItemUseEvent::class;
    public const PLAYER_ENTITY_INTERACT = "pocketmine\event\player\PlayerEntityInteractEvent";

    //ENTITY
    public const ENTITY_DAMAGE_BY_ENTITY = EntityDamageByEntityEvent::class;
    public const ENTITY_DAMAGE = EntityDamageEvent::class;
    public const ENTITY_EXPLODE = EntityExplodeEvent::class;
}

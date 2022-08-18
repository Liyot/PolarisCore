<?php

namespace Polaris\utils;

class ListenerUtils
{

    //BLOCK
    public const BLOCK_BREAK = "pocketmine\event\block\BlockBreakEvent";
    public const BLOCK_BURN = "pocketmine\event\block\BlockBurnEvent";
    public const BLOCK_GROW = "pocketmine\event\block\BlockGrowEvent";
    public const BLOCK_ITEM_PICKUP = "pocketmine\event\block\BlockItemPickupEvent";
    public const BLOCK_MELT = "pocketmine\event\block\BlockMeltEvent";
    public const BLOCK_PLACE = "pocketmine\event\block\BlockPlaceEvent";
    public const BLOCK_SPREAD = "pocketmine\event\block\BlockSpreadEvent";
    public const BLOCK_TELEPORT = "pocketmine\event\block\BlockTeleportEvent";
    public const BLOCK_UPDATE = "pocketmine\event\block\BlockUpdateEvent";

    //PLAYER
    public const PLAYER_MOVE = "pocketmine\event\player\PlayerMoveEvent";
    public const PLAYER_SNEAK = "pocketmine\event\player\PlayerSneakEvent";
    public const PLAYER_ENTITY_INTERACT = "pocketmine\event\player\PlayerEntityInteractEvent";

    //ENTITY
    public const ENTITY_DAMAGE_BY_ENTITY = "pocketmine\event\entity\EntityDamageByEntityEvent";
    public const ENTITY_DAMAGE = "pocketmine\event\entity\EntityDamageEvent";
    public const ENTITY_EXPLODE = "pocketmine\event\entity\EntityExplodeEvent";
}

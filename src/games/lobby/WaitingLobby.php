<?php

namespace Polaris\games\lobby;

use pocketmine\world\World;
use Polaris\games\types\Jump;
use Polaris\player\PolarisPlayer;

class WaitingLobby
{
    /**
     * @param World $world
     * @param Jump $jump
     * @param PolarisPlayer[] $players
     */
    public function __construct(private World $world, private Jump $jump, private array $players)
    {

    }
}
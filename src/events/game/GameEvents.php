<?php

namespace Polaris\events\game;

use pocketmine\event\Event;
use Polaris\games\Game;

abstract class GameEvents extends Event
{
	public function __construct(private Game $game){}

	public function getGame(): Game
	{
		return $this->game;
	}
}

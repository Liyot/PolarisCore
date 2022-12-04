<?php

namespace Polaris\events\game\rush;

use Polaris\events\game\GameEvents;
use Polaris\games\Game;
use Polaris\games\types\RushGame;

class BedExplosionEvent extends GameEvents
{
	public function __construct(private RushGame $game, private int $base, private string $color)
	{
		parent::__construct($this->game, $base, $this->color);
	}

	/**
	 * @return RushGame
	 */
	public function getGame(): RushGame
	{
		return $this->game;
	}

	/**
	 * @return int
	 */
	public function getBase(): int
	{
		return $this->base;
	}

	/**
	 * @return string
	 */
	public function getColor(): string
	{
		return $this->color;
	}
}
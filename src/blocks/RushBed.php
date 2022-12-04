<?php

namespace Polaris\blocks;

use pocketmine\block\Bed;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockBreakInfo as BreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\tile\Bed as TileBed;
use pocketmine\item\ItemIds;
use Polaris\games\types\RushGame;

final class RushBed extends Bed
{
	public function __construct(private RushGame $game, private string $team, private int $base)
	{
		parent::__construct(new BID(Ids::BED_BLOCK, 0, ItemIds::BED, TileBed::class), "Bed Block", new BreakInfo(0.2));
	}

	public function destroy(): void
	{

	}

	/**
	 * @return RushGame
	 */
	public function getGame(): RushGame
	{
		return $this->game;
	}

	/**
	 * @return string
	 */
	public function getTeam(): string
	{
		return $this->team;
	}

	/**
	 * @return int
	 */
	public function getBase(): int
	{
		return $this->base;
	}
}
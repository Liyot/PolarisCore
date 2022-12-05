<?php

namespace Polaris\listener;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\Listener;
use pocketmine\Server;
use Polaris\forms\bootstrap\Main;
use Polaris\games\types\RushGame;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;
use Polaris\task\TntTask;

class EntityListener implements Listener
{

	/**
	 * @priority Monitor
	 * @return void
	 */

	public function onExplode(ExplosionPrimeEvent $event)
	{
		$entity = $event->getEntity();
		if($entity instanceof PrimedTNT)
		{
			var_dump($entity);
			if (str_contains($entity->getWorld()->getFolderName(), "Rush"))
			{
				Server::getInstance()->getAsyncPool()->submitTask(new TntTask($entity));
			}
		}
	}
}
<?php

namespace Polaris\task;

use pocketmine\entity\object\PrimedTNT;
use pocketmine\scheduler\AsyncTask;

class TntTask extends AsyncTask
{
	private int $seconds;

	public function __construct(private PrimedTNT $entity)
	{
		$this->seconds = microtime(true);
	}

	public function onRun(): void
	{
		if($this->entity->getFuse() === 0) $this->cancelRun();
		$this->entity->setNameTag(round(microtime(true) - $this->seconds));
	}
}
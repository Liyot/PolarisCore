<?php

namespace UnknowL;

use pocketmine\plugin\PluginBase;
use UnknowL\Command\Groups\GroupsCommand;
use UnknowL\Groups\Team;
use UnknowL\Listener\PlayerListener;

class Polaris extends PluginBase{

    public static array $teams = [];

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener() , $this);
        $this->getServer()->getCommandMap()->register('', new GroupsCommand());
    }

    public static function getTeam(string $name): ?Team{
        return self::$teams[$name] ?? null;
    }



}
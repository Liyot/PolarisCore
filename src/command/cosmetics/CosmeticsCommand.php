<?php

namespace Polaris\command\cosmetics;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Polaris\cosmetics\CosmeticsManager;
use Polaris\player\PolarisPlayer;

class CosmeticsCommand extends Command
{
    public function __construct()
    {
        parent::__construct("Cosmetics", "Gérer vos cosmetics", "/cosmetics", ['cosmetics']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof PolarisPlayer)
        {
            CosmeticsManager::saveSkin($sender->getSkin(), $sender->getName());
            CosmeticsManager::formCosmetics($sender);
        }
    }
}
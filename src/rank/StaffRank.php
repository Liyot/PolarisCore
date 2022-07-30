<?php

namespace Polaris\rank;

use Polaris\player\PolarisPlayer;

class StaffRank implements Rank{


    public function getName(): string
    {
        return 'Staff';
    }

    public function getColor(): string
    {
        return '§l§b';
    }

    public function sendStaffMod(PolarisPlayer $player){

    }

    public function getPrefix(): string
    {
        return "[Staff]";
    }

    public function isPremium(): bool
    {
        return false;
    }
}
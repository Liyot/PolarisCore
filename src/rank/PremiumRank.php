<?php

namespace Polaris\rank;


class PremiumRank implements Rank
{
    public function getName(): string
    {
        return 'Premium';
    }

    public function getColor(): string
    {
        return '#00FF00';
    }

    public function isPremium(): bool
    {
        return true;
    }


    public function getPrefix(): string
    {
        return "§l§b[Premium]§l§b";
    }
}
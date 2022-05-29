<?php

namespace UnknowL\Rank;

use PolarisCore\Rank\Rank;

class PremiumRank extends Rank
{
    public function getName(): string
    {
        return 'Premium';
    }

    public function getColor(): string
    {
        return '#00FF00';
    }

    public function getLevel(): int
    {
        return 2;
    }

    public function getRecompense()
    {
        // TODO: Implement getRecompense() method.
    }
}
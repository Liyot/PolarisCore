<?php

namespace Polaris\cosmetics\head;

use Polaris\cosmetics\Cosmetic;
use Polaris\cosmetics\CosmeticInfo;

class CrownCosmetic extends Cosmetic
{
    public function __construct()
    {
        parent::__construct(new CosmeticInfo("Crown", "texture.png", "mobs.geo.json", CosmeticInfo::TYPE_HEAD, 15));
    }
}
<?php

namespace Polaris\cosmetics;

class CosmeticInfo
{

    public const TYPE_HEAD = 0;
    public const TYPE_BODY = 1;

    public function __construct(private string $name,private string $imageName, private string $jsonName, private int $type, private int $slot = 0){}

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getImageName(): string
    {
        return $this->imageName;
    }

    /**
     * @return int
     */
    public function getSlot(): int
    {
        return $this->slot;
    }

    /**
     * @return string
     */
    public function getJsonName(): string
    {
        return $this->jsonName;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
}
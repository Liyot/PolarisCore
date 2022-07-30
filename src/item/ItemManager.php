<?php

namespace Polaris\item;

use pocketmine\item\ItemFactory;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

class ItemManager{
    use SingletonTrait;

    /**
     * @param Specialitem[] $list
     */
    public function __construct(public array $list = []){
        self::setInstance($this);
        $this->register();
    }

    public function add(Specialitem $item): void{
        $key = $item->getId()."-".$item->getMeta();
       isset($this->list[$key]) ?: $this->list[$key] = $item;
    }

    public function get(int $id, $meta = 0): ?Specialitem{
        return $this->list[$id."-".$meta] ?? null;
    }

    public function register(): void{
        foreach ($this->list as $name => $item){
            $this->add($item);
            Server::getInstance()->getLogger()->notice(TextFormat::DARK_AQUA."[ITEM] §aRegistering item: " . $item->getName());
            ItemFactory::getInstance()->register($item, true);
        }
    }
}
<?php

namespace UnknowL\Games;

class GameProperties{

    public array $properties = [];

    public function getProperties(string $name): mixed{
        return $this->properties[strtolower($name)] ?? null;
    }

    public function setProperties(string $name, $value): self{
        $this->properties[$name] = $value;
        return $this;
    }

    public function removeProperties(string $name): self{
        unset($this->properties[$name]);
        return $this;
    }

    public function getPropertiesList(): array{
        return $this->properties;
    }

    public function setBaseProperties(): void{
     $this->properties  = [
         "Starting" => false,
         "Ending" => false,
         "Running" => false,
         "RunningFor" => 0,
         "TimeLeft" => 0,
     ];
    }
}
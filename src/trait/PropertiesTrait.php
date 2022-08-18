<?php

namespace Polaris\trait;

trait PropertiesTrait
{

    public array $properties = [];

    public function getProperties(string $name): mixed{
        return $this->properties[strtolower($name)] ?? null;
    }

    public function setProperties(string $name, $value): self{
        $this->properties[strtolower($name)] = $value;
        return $this;
    }

    public function setNestedProperties($nameey, $value) : void{
        $vars = explode(".", $nameey);
        $base = array_shift($vars);

        if(!isset($this->properties[$base])){
            $this->properties[$base] = [];
        }

        $base = &$this->properties[$base];

        while(count($vars) > 0){
            $baseKey = array_shift($vars);
            if(!isset($base[$baseKey])){
                $base[$baseKey] = [];
            }
            $base = &$base[$baseKey];
        }
        $base = $value;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getNestedProperties(string $name): mixed{
        if(isset($this->properties[$name])){
            return $this->properties[$name];
        }

        $vars = explode(".", $name);
        $base = strtolower(array_shift($vars));
        if(isset($this->properties[$base])){
            $base = $this->properties[$base];
        }else{
            return null;
        }

        while(count($vars) > 0){
            $basek = array_shift($vars);
            if(is_array($base) && isset($base[$basek])){
                $base = $base[$basek];
            }else{
                return null;
            }
        }

        return $this->properties[$name] = $base;
    }

    public function removeProperties(string $name): self{
        unset($this->properties[$name]);
        return $this;
    }

    public function getPropertiesList(): array{
        return $this->properties;
    }

    public function setBaseProperties(array $properties): void{
        $this->properties  = $properties;
    }
}
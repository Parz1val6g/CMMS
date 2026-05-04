<?php

namespace App\Core\Forms\Traits;

/**
 * Trait para simplificar getter/setter com suporte a encadeamento fluente.
 * 
 * Reduz verbosidade eliminando repetição de getters/setters padrão.
 * Pode ser usado com __get/__set magic methods, mas recomenda-se especificar
 * tipos explícitos quando necessário.
 */
trait FluentProperties
{
    /**
     * Define propriedade dinamicamente.
     * 
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException se propriedade não existe
     */
    public function __set(string $name, mixed $value): void
    {
        if (!property_exists($this, $name)) {
            throw new \InvalidArgumentException("Propriedade não existe: $name");
        }
        $this->$name = $value;
    }

    /**
     * Obtém propriedade dinamicamente.
     * 
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException se propriedade não existe
     */
    public function __get(string $name): mixed
    {
        if (!property_exists($this, $name)) {
            throw new \InvalidArgumentException("Propriedade não existe: $name");
        }
        return $this->$name;
    }
}

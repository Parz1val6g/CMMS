<?php

namespace App\Core\Forms\Fields;
use App\Core\Forms\FormField;
class MapInput extends FormField
{
    protected ?string $type = 'map';

    /**
     * Define API key para Google Maps (ou outro provider).
     *
     * @param string $apiKey
     * @return $this
     */
    public function apiKey(string $apiKey): static
    {
        $this->meta('apiKey', $apiKey);
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->getMeta('apiKey');
    }

    /**
     * Define o nome do campo de latitude no formulário (default: 'latitude').
     *
     * @param string $field
     * @return $this
     */
    public function latField(string $field): static
    {
        $this->meta('latField', $field);
        return $this;
    }

    /**
     * Define o nome do campo de longitude no formulário (default: 'longitude').
     *
     * @param string $field
     * @return $this
     */
    public function lngField(string $field): static
    {
        $this->meta('lngField', $field);
        return $this;
    }

    /**
     * Atalho para definir ambos os campos de coordenadas de uma vez.
     *
     * @param string $latField  Nome do campo latitude (default: 'latitude')
     * @param string $lngField  Nome do campo longitude (default: 'longitude')
     * @return $this
     */
    public function coordinates(string $latField = 'latitude', string $lngField = 'longitude'): static
    {
        $this->meta('latField', $latField);
        $this->meta('lngField', $lngField);
        return $this;
    }
}


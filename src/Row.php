<?php

namespace OptimistDigital\NovaSimpleRepeatable;

use JsonSerializable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;

class Row implements JsonSerializable
{
    protected $fields;
    protected $attributes;

    public function __construct($fields = null, $attributes = [])
    {
        $this->fields = new FieldCollection($fields);
        $this->attributes = $attributes;
    }

    /**
     * Get a cloned instance with set values
     *
     * @param array $attributes
     * @return Row
     */
    public function duplicateAndHydrate(array $attributes = [])
    {
        return new static ($this->fields->map(function ($field) {
            return $this->cloneField($field);
        }), $attributes);
    }

    /**
     * Resolve fields using given attributes.
     *
     * @return void
     */
    public function resolve()
    {
        $this->fields->each->resolve($this->attributes);
    }

    /**
     * Create a working field clone instance
     *
     * @param \Laravel\Nova\Fields\Field $original
     * @return \Laravel\Nova\Fields\Field
     */
    protected function cloneField(Field $original)
    {
        $field = clone $original;

        $callables = ['displayCallback', 'resolveCallback', 'fillCallback', 'requiredCallback'];

        foreach ($callables as $callable) {
            if (!is_a($field->$callable ?? null, \Closure::class)) continue;
            $field->$callable = $field->$callable->bindTo($field);
        }

        return $field;
    }

    public function jsonSerialize()
    {
        return [
            'fields' => $this->fields->jsonSerialize(),
        ];
    }
}

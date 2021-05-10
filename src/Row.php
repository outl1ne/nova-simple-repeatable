<?php

namespace OptimistDigital\NovaSimpleRepeatable;

use JsonSerializable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;

class Row implements JsonSerializable
{
    protected $fields;
    protected $attributes;

    public function __construct(FieldCollection $fields, $attributes = [])
    {
        $this->fields = $fields;
        $this->attributes = $attributes;
    }

    /**
     * Get a cloned instance with set values
     *
     * @param array $attributes
     * @return Row
     */
    public static function make(FieldCollection $fields, array $attributes = [])
    {
        $newFields = clone $fields;
        return new static($newFields->map(function ($field) {
            return static::cloneField($field);
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

    public function resolveForDisplay()
    {
        $this->fields->each->resolveForDisplay($this->attributes);
    }

    /**
     * Create a working field clone instance
     *
     * @param \Laravel\Nova\Fields\Field $original
     * @return \Laravel\Nova\Fields\Field
     */
    protected static function cloneField(Field $original)
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

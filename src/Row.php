<?php

namespace Outl1ne\NovaSimpleRepeatable;

use JsonSerializable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;

class Row implements JsonSerializable
{
    protected FieldCollection $fields;
    protected array $attributes;

    public function __construct(FieldCollection $fields, array $attributes = [])
    {
        $this->fields = $fields;
        $this->attributes = $attributes;
    }

    /**
     * Get a cloned instance with set values
     */
    public static function make(FieldCollection $fields, array $attributes = []): static
    {
        $newFields = clone $fields;
        return new static($newFields->map(function ($field) {
            return static::cloneField($field);
        }), $attributes);
    }

    /**
     * Resolve fields using given attributes.
     */
    public function resolve(): void
    {
        $this->fields->each->resolve($this->attributes);
    }

    public function resolveForDisplay(): void
    {
        $this->fields->each->resolveForDisplay($this->attributes);
    }

    /**
     * Create a working field clone instance
     */
    protected static function cloneField(Field $original): Field
    {
        $field = clone $original;
        $callables = ['displayCallback', 'resolveCallback', 'fillCallback', 'requiredCallback'];

        foreach ($callables as $callable) {
            if (!is_a($field->$callable ?? null, \Closure::class)) continue;
            $field->$callable = $field->$callable->bindTo($field);
        }

        return $field;
    }

    public function jsonSerialize(): array
    {
        return [
            'fields' => $this->fields->jsonSerialize(),
        ];
    }
}

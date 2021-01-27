<?php

namespace OptimistDigital\NovaSimpleRepeatable;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use JsonSerializable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;

class Row implements Arrayable, JsonSerializable
{
    use HasAttributes, HidesAttributes;

    protected $fields;

    public function __construct($fields = null, $attributes = [])
    {
        $this->fields = new FieldCollection($fields);
        $this->setRawAttributes($attributes);
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
        $this->fields->each(function ($field) {
            $field->resolve(collect($this));
        });
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

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    protected function getDates()
    {
        return [];
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        return [];
    }

    public function jsonSerialize()
    {
        return [
            'fields' => $this->fields->jsonSerialize(),
        ];
    }
}

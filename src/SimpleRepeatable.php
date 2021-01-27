<?php

namespace OptimistDigital\NovaSimpleRepeatable;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\PerformsValidation;

class SimpleRepeatable extends Field
{
    use PerformsValidation;

    public $component = 'simple-repeatable';

    protected $fields = [];
    protected $rows = [];

    public function __construct($name, $attribute = null, $fields = [], $attributes = [])
    {
        parent::__construct($name, $attribute, null);

        $this->attributes = $attributes;
        $this->fields($fields);
        $this->canAddRows(true);
        $this->canDeleteRows(true);
        $this->hideFromIndex();
    }

    public function canAddRows($canAddRows = true)
    {
        return $this->withMeta(['canAddRows' => $canAddRows]);
    }

    public function fields($fields = [])
    {
        $this->fields = FieldCollection::make($fields);
    }

    public function maxRows($maxRows = null)
    {
        return $this->withMeta(['maxRows' => $maxRows]);
    }

    public function canDeleteRows($canDeleteRows = true)
    {
        return $this->withMeta(['canDeleteRows' => $canDeleteRows]);
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $value = $request->input($requestAttribute) ?? null;
        $value = json_decode($value, true);

        // Do validation
        $rules = static::formatRules(
            $request,
            collect($this->fields)->reject(function ($field) use ($request) {
                return $field->isReadonly($request);
            })->mapWithKeys(function ($field) use ($request) {
                return $field->getUpdateRules($request);
            })->mapWithKeys(function ($value, $key) {
                return ["{$this->attribute}.*.$key" => $value];
            })->all()
        );
        Validator::make([$this->attribute => $value], $rules)->validate();

        $model->{$attribute} = $value;
    }

    public function resolve($resource, $attribute = null)
    {
        $attribute = $attribute ?? $this->attribute;
        $this->rows = $this->buildRows($resource, $attribute);

        // Resolve rows
        $this->rows->each(function ($row) {
            $row->resolve();
        });

        $this->withMeta(['rows' => $this->rows]);
    }

    public function buildRows($resource, $attribute)
    {
        $value = $this->extractValueFromResource($resource, $attribute);
        return collect($value)->map(function ($item) {
            return (new Row($this->fields))->duplicateAndHydrate((array)$item);
        })->filter()->values();
    }

    protected function extractValueFromResource($resource, $attribute)
    {
        $value = data_get($resource, str_replace('->', '.', $attribute)) ?? [];

        if ($value instanceof Collection) {
            $value = $value->toArray();
        } else if (is_string($value)) {
            $value = json_decode($value) ?? [];
        }

        // Fail silently in case data is invalid
        if (!is_array($value)) return [];

        return array_map(function ($item) {
            return is_array($item) ? (object)$item : $item;
        }, $value);
    }
}

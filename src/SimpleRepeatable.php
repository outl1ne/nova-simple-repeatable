<?php

namespace OptimistDigital\NovaSimpleRepeatable;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\PerformsValidation;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Http\Requests\NovaRequest;

class SimpleRepeatable extends Field
{
    use PerformsValidation;

    public $component = 'simple-repeatable';

    protected $fields = [];

    public function __construct($name, $attribute = null, $fields = [])
    {
        parent::__construct($name, $attribute, null);

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
        $this->fields = $fields;
        return $this->withMeta(['repeatableFields' => $fields]);
    }

    public function minRows($minRows = null)
    {
        return $this->withMeta(['minRows' => $minRows]);
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
}
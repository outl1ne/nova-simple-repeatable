<?php

namespace OptimistDigital\NovaSimpleRepeatable;

use Exception;
use Laravel\Nova\Nova;
use Laravel\Nova\Fields\Field;
use Illuminate\Support\Collection;
use Laravel\Nova\PerformsValidation;
use Laravel\Nova\Fields\FieldCollection;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Http\Requests\NovaRequest;
use ReflectionMethod;

class SimpleRepeatable extends Field
{
    use PerformsValidation;

    public $component = 'simple-repeatable';

    protected $fields = [];
    protected $rows = [];

    public function __construct($name, $attribute = null, $fields = [])
    {
        parent::__construct($name, $attribute, null);

        $this->fields($fields);
        $this->canAddRows(true);
        $this->canDeleteRows(true);
        $this->hideFromIndex();
        $this->addRowLabel(__('simpleRepeatable.addRow'));
    }

    public function fields($fields = [])
    {
        $this->fields = FieldCollection::make($fields);
    }

    public function minRows($minRows = null)
    {
        return $this->withMeta(['minRows' => $minRows]);
    }

    public function maxRows($maxRows = null)
    {
        return $this->withMeta(['maxRows' => $maxRows]);
    }

    public function canAddRows($canAddRows = true)
    {
        return $this->withMeta(['canAddRows' => $canAddRows]);
    }

    public function canDeleteRows($canDeleteRows = true)
    {
        return $this->withMeta(['canDeleteRows' => $canDeleteRows]);
    }

    public function addRowLabel($label)
    {
        return $this->withMeta(['addRowLabel' => $label]);
    }
    /**
     *
     * Validate and hydrate the given attribute on the model based on the incoming request.
     *
     * @param NovaRequest $request
     * @param $requestAttribute
     * @param $model
     * @param $attribute
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $value = $request->input($requestAttribute) ?? null;
        $value = json_decode($value, true);

        // Do validation
        $rules = $this->getFormattedRules($request);
        Validator::make([$this->attribute => $value], $rules)->validate();

        $model->{$attribute} = $value;
    }

    public function fill(NovaRequest $request, $model)
    {
        if (get_class($model) === 'Whitecube\NovaFlexibleContent\Layouts\Layout') {
            $value = $request->input($this->attribute) ?? null;
            $value = json_decode($value, true);

            // Do validation
            $this->resource = $request->findModelOrFail();

            // Explicity resolve fields to get valid nova-translatable rules
            $this->fields->each->resolve($request);

            $rules = $this->getFormattedRules($request);
            Validator::make([$this->attribute => $value], $rules)->validate();
        }

        parent::fill($request, $model);
    }

    protected function getFormattedRules(NovaRequest $request)
    {
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

        if ($this->resource) {
            try {
                $resource = Nova::resourceForModel($this->resource);
                $formatRules = new ReflectionMethod($resource, 'formatRules');
                $formatRules->setAccessible(true);
                $newRules = $formatRules->invoke(new $resource($this->resource), $request, $rules);
                if ($newRules) $rules = $newRules;
            } catch (Exception $e) {
            }
        }

        return $rules;
    }

    /**
     * Resolve the field's and it's rows value.
     *
     * @param mixed $resource
     * @param string|null $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        $novaRequest = app()->make(NovaRequest::class);
        $resolveForDisplay = $novaRequest->isResourceIndexRequest() || $novaRequest->isResourceDetailRequest();

        $attribute = $attribute ?? $this->attribute;
        $this->rows = $this->buildRows($resource, $attribute);

        // Resolve rows
        if ($resolveForDisplay) {
            $this->rows->each->resolveForDisplay();
        } else {
            $this->rows->each->resolve();
        }

        $this->withMeta([
            'rows' => $this->rows,
            'fields' => $this->fields->resolve(null) // Empty fields
        ]);

        return parent::resolve($resource, $attribute);
    }

    /**
     * Define the field's actual rows (as "base models") based
     * on the field's current model & attribute
     *
     * @param mixed $resource
     * @param string $attribute
     * @return Illuminate\Support\Collection
     */
    public function buildRows($resource, $attribute)
    {
        $value = $this->extractValueFromResource($resource, $attribute);
        return collect($value)->map(function ($rowValue) {
            return Row::make($this->fields, (array) $rowValue);
        })->filter()->values();
    }

    /**
     * Find the attribute's value in the given resource
     *
     * @param mixed $resource
     * @param string $attribute
     * @return array
     */
    protected function extractValueFromResource($resource, $attribute)
    {
        $value = $this->resolveAttribute($resource, $attribute);

        if (is_callable($this->resolveCallback)) {
            $value = call_user_func($this->resolveCallback, $value, $resource, $attribute);
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        } else if (is_string($value)) {
            $value = json_decode($value, true) ?? [];
        }

        // Fail silently in case data is invalid
        if (!is_array($value)) return [];

        return array_map(function ($item) {
            return is_array($item) ? (object)$item : $item;
        }, $value);
    }
}

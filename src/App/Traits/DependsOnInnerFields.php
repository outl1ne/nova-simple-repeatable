<?php

namespace Outl1ne\NovaSimpleRepeatable\App\Traits;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Controllers\CreationFieldSyncController;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Outl1ne\NovaSimpleRepeatable\SimpleRepeatable;

trait DependsOnInnerFields
{
    // When to append the merging logic
    protected array $callerClasses = [
        CreationFieldSyncController::class
    ];

    /**
     * @throws Exception
     */
    public function creationFields(NovaRequest $request)
    {
        $creationFields = parent::creationFields($request);

        return $this->shouldAppendInnerFields()
            ? $creationFields->merge($this->loadInnerFields($request))
            : $creationFields;
    }

    /**
     * We should dynamically load fields within SimpleRepeatable only when the request is coming
     * from certain endpoints/controllers, because we don't want to change the way plain
     * fields() method within a resource functions.
     *
     * @return string|null
     */
    protected function shouldAppendInnerFields(): ?string
    {
        $backtrace = debug_backtrace();
        $classes = Arr::pluck($backtrace, 'class');

        foreach ($classes as $class) {
            if (in_array($class, $this->callerClasses)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    protected function loadInnerFields(NovaRequest $request): array
    {
        $innerFields = [];

        /** @var Resource $this */
        foreach ($this->fields($request) as $parentField) {
            if (get_class($parentField) !== SimpleRepeatable::class) {
                continue;
            }

            $reqField = $request->get('field');
            $elementNumber = Str::afterLast($reqField, '-');

            $parentAttribute = $parentField->attribute;

            $childFields = $parentField->getFields()->all();

            /** @var Field $childField */
            foreach ($childFields as $childField) {

                $setAttribute = "$parentAttribute.{$childField->attribute}";
                $childField->attribute = "$parentAttribute---{$childField->attribute}---$elementNumber";
                $innerFields[] = $childField;

                $value = $request->get($childField->attribute);

                $request->query->set($setAttribute, $value);
                $request->query->set('component', $childField->dependentComponentKey());
            }
        }

        return $innerFields;
    }
}

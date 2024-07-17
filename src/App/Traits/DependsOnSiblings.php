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

trait DependsOnSiblings
{
    protected string $frontendSeparator = '---';

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

        return $this->shouldAppendSiblings()
            ? $creationFields->merge($this->loadSiblings($request))
            : $creationFields;
    }

    /**
     * We should dynamically load fields within SimpleRepeatable only when the request is coming
     * from certain endpoints/controllers, because we don't want to change the way plain
     * fields() method within a resource functions.
     *
     * @return string|null
     */
    protected function shouldAppendSiblings(): ?string
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
    protected function loadSiblings(NovaRequest $request): array
    {
        $siblings = [];
        // If triggered within SimpleRepeatable, this will come in xxx---xxx---0 format
        $affectedField = $request->get('field');
        $elementNumber = Str::afterLast($affectedField, '-');

        $triggerFields = $this->getTriggerFields($request, $affectedField);

        // Prevent entering if dependsOn is triggered by outer fields
        if (!str_contains($affectedField, $this->frontendSeparator)) {
            return $siblings;
        }

        /** @var Resource $this */
        foreach ($this->fields($request) as $parentField) {
            if (get_class($parentField) !== SimpleRepeatable::class) {
                continue;
            }

            $parentAttribute = $parentField->attribute;

            /** @var Field $childField */
            foreach ($parentField->getFields()->all() as $childField) {
                // Need to change original attribute to the frontend dashed equivalent so Nova knows which
                // field will be affected by changing its dependant
                $childField->attribute = "$parentAttribute---{$childField->attribute}---$elementNumber";
                $siblings[] = $childField;

                $this->setAffected($childField, $affectedField, $request);
                $this->setTrigger($childField, $triggerFields, $parentAttribute, $request);
            }
        }

        return $siblings;
    }

    protected function getTriggerFields(NovaRequest $request, mixed $affectedField): array
    {
        // Fetching input without query string parameters
        $input = $request->json()->all();

        // Filter out the specific field
        $filteredInputs = array_filter($input, function ($value, $key) use ($affectedField) {
            return $key !== $affectedField;
        }, ARRAY_FILTER_USE_BOTH);

        return array_keys($filteredInputs);
    }

    protected function extractFieldAttribute(string $field): string
    {
        return Str::betweenFirst($field, $this->frontendSeparator, $this->frontendSeparator);
    }

    /**
     * Need to replace original component in the query since we changed the original attribute
     * as we can't change it afterward without overriding Nova routes & controllers which
     * seemed like an unnecessary complexity.
     *
     * @param Field $childField
     * @param mixed $affectedField
     * @param NovaRequest $request
     * @return void
     */
    protected function setAffected(Field $childField, string $affectedField, NovaRequest $request): void
    {
        if ($childField->attribute === $affectedField) {
            $request->query->set('component', $childField->dependentComponentKey());
        }
    }

    /**
     * When we encounter a trigger field, we need to make sure to extract value it is providing for the
     * dependent field, and attach it to the request in the same format as expected on the backend
     *
     * @param Field $childField
     * @param array $triggerFields
     * @param string $parentAttribute
     * @param NovaRequest $request
     * @return void
     */
    protected function setTrigger(Field $childField, array $triggerFields, string $parentAttribute, NovaRequest $request): void
    {
        if (!in_array($childField->attribute, $triggerFields)) {
            return;
        }

        $triggerAttribute = $this->extractFieldAttribute($childField->attribute);

        // Prepare dot-notation backend key to be available through request
        $setAttribute = "$parentAttribute.$triggerAttribute";
        $value = $request->get($childField->attribute);

        \Log::info("Setting $setAttribute to $value");

        $request->query->set($setAttribute, $value);
    }
}

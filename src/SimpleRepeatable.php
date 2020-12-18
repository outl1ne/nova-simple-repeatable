<?php

namespace OptimistDigital\NovaSimpleRepeatable;

use Laravel\Nova\Fields\Field;

class SimpleRepeatable extends Field
{
    public $component = 'simple-repeatable-field';

    protected $storedWithVat = true;

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);
    }
}

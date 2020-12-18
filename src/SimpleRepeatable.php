<?php

namespace OptimistDigital\NovaSimpleRepeatable;

use Laravel\Nova\Fields\Field;

class SimpleRepeatable extends Field
{
    public $component = 'simple-repeatable';

    protected $fields = [];

    public function __construct($name, $attribute = null, $fields = [])
    {
        parent::__construct($name, $attribute, null);

        $this->fields = $fields;

        $this->withMeta(['repeatableFields' => $fields]);
    }
}

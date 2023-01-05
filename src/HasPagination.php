<?php

namespace Outl1ne\NovaSimpleRepeatable;

use Laravel\Nova\Fields\SupportsDependentFields;

trait HasPagination
{
    use SupportsDependentFields;

    protected ?int $perPage = null;
    protected ?int $currentPage = 1;
    protected ?int $totalCount = 0;


    public function paginate(int $perPage = 50): self
    {
        $this->perPage = $perPage;
        $this->registerPaginationDependency();

        return $this;
    }

    public function setCurrentPage(?int $currentPage = 1): void
    {
        $this->currentPage = $currentPage;
    }

    public function setTotalCount(?int $totalCount = 0): void
    {
        $this->totalCount = $totalCount;
    }

    protected function isPaginated(): bool
    {
        return !is_null($this->perPage) && $this->perPage > 0;
    }

    protected function registerPaginationDependency(): void
    {
        $uniqueAttribute = "current_{$this->attribute}_page";

        $this->dependsOn([$uniqueAttribute], function ($field, $request, $formData) use ($uniqueAttribute) {
            $currentPage = $formData->{$uniqueAttribute} ;
            $isPaginated = $field->isPaginated();

            if ($isPaginated && isset($currentPage)) {
                $field->setCurrentPage($currentPage);
                $field->rows = $field->buildRows($field->resource, $field->attribute);
                $field->rows->each->resolve();
            }

            $field->withMeta([
                'rows' => $field->rows,
                'perPage' => $field->perPage,
                'currentPage' => $field->currentPage,
                'currentPageCount' => count($field->rows),
                'totalCount' => $field->totalCount,
                'isPaginated' => $isPaginated,
            ]);
        });
    }
}

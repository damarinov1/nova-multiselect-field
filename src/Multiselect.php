<?php

namespace OptimistDigital\MultiselectField;

use Closure;
use Laravel\Nova\Fields\Field;

class Multiselect extends Field
{
    public $component = 'multiselect-field';

    protected $pageResponseResolveCallback;

    /** @var Closure|null */
    private $beforeSerialize;

    /**
     * Sets the options available for select.
     *
     * @param array $options
     * @return Multiselect
     **/
    public function options($options = [])
    {
        return $this->withMeta([
            'options' => collect($options)->map(function ($label, $value) {
                return is_array($label) ? $label + ['value' => $value] : ['label' => $label, 'value' => $value];
            })->values()->all(),
        ]);
    }

    /**
     * Sets the max number of options the user can select.
     *
     * @param int $max
     * @return Multiselect
     **/
    public function max($max)
    {
        return $this->withMeta(['max' => $max]);
    }

    /**
     * @param bool $flag
     *
     * @return Multiselect
     */
    public function allowEmpty($flag)
    {
        return $this->withMeta(['allowEmpty' => (bool)$flag]);
    }

    /**
     * Sets the placeholder value displayed on the field.
     *
     * @param string $placeholder
     * @return Multiselect
     **/
    public function placeholder($placeholder)
    {
        return $this->withMeta(['placeholder' => $placeholder]);
    }

    /**
     * Sets the maximum number of options displayed at once.
     *
     * @param int $optionsLimit
     * @return Multiselect
     **/
    public function optionsLimit($optionsLimit)
    {
        return $this->withMeta(['optionsLimit' => $optionsLimit]);
    }

    public function resolveResponseValue($value, $templateModel)
    {
        $parsedValue = isset($value) ? json_decode($value) : null;
        return is_callable($this->pageResponseResolveCallback)
            ? call_user_func($this->pageResponseResolveCallback, $parsedValue, $templateModel)
            : $parsedValue;
    }

    public function resolveForPageResponseUsing(callable $resolveCallback)
    {
        $this->pageResponseResolveCallback = $resolveCallback;
        return $this;
    }

    /**
     * @param bool $searchable
     * @return Multiselect
     */
    public function searchable(bool $searchable = true): Multiselect
    {
        return $this->withMeta(['searchable' => $searchable]);
    }

    /**
     * @param string|null $searchUrl
     * @return Multiselect
     */
    public function searchUrl(?string $searchUrl): Multiselect
    {
        return $this->withMeta(['searchUrl' => $searchUrl]);
    }

    /**
     * @param string|null $noOptions
     * @return Multiselect
     */
    public function noOptions(?string $noOptions): Multiselect
    {
        return $this->withMeta(['noOptions' => $noOptions]);
    }

    /**
     * @param string|null $noResults
     * @return Multiselect
     */
    public function noResults(?string $noResults): Multiselect
    {
        return $this->withMeta(['noResults' => $noResults]);
    }

    /**
     * @param string|null $template
     * @return Multiselect
     */
    public function template(?string $template): Multiselect
    {
        return $this->withMeta(['template' => $template]);
    }

    /**
     * @param bool $multiple
     * @return Multiselect
     */
    public function multiple(bool $multiple): Multiselect
    {
        return $this->withMeta(['multiple' => $multiple]);
    }

    public function jsonSerialize(): array
    {
        if ($this->beforeSerialize) {
            call_user_func($this->beforeSerialize, $this);
        }

        return parent::jsonSerialize();
    }

    /**
     * @param Closure|null $beforeSerialize
     * @return Multiselect
     */
    public function beforeSerialize(?Closure $beforeSerialize): self
    {
        $this->beforeSerialize = $beforeSerialize;

        return $this;
    }

}

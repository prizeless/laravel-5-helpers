<?php

namespace Laravel5Helpers\Definitions;

use  Laravel5Helpers\Exceptions\ValidationError;
use Illuminate\Support\Facades\Validator;

abstract class Definition
{
    protected $validators;

    public function __construct(array $attributes = [])
    {
        $this->setAttributes($attributes);
        $this->setValidators();
    }

    private function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            if (empty($value) === false) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * @throws ValidationError
     */
    public function validate()
    {
        $validator = Validator::make($this->valuesToArray(), $this->getValidators());

        if ($validator->fails()) {
            foreach ($validator->messages()->toArray() as $message => $data) {
                throw new ValidationError($data[0]);
            }
        }
    }

    abstract protected function setValidators();

    /**
     * @return array
     */
    public function getAttributes()
    {
        $reflect = new \ReflectionClass($this);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        $properties = [];

        foreach ($props as $property) {
            $properties[$property->name] = $this->{$property->name};
        }

        return $properties;
    }

    /**
     * @return array
     */
    public function valuesToArray()
    {
        $attributes = $this->getAttributes();
        $values = [];
        foreach ($attributes as $attribute => $defaults) {
            $this->assignValue($attribute, $values);
        }

        return $values;
    }

    private function assignValue($attribute, &$values)
    {
        $values[$attribute] = $this->{$attribute};
    }

    public function getValidators()
    {
        return $this->validators;
    }
}

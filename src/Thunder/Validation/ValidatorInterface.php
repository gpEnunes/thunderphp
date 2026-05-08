<?php

namespace Thunder\Validation;

interface ValidatorInterface{
    public function validate(array $rules):void;
    public function errors():array;
}
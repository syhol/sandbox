<?php

class Entity
{
    private $schema = [
        'first_name' => 'required|string|min:2|max:30|alphaspace',
        'last_name' => 'required|string|min:2|max:30|alphaspace',
        'full_name' => 'read_only|from:getFullName',
        'dob' => 'required|date|min:01/01/1900|max:now -10 years',
        'age' => 'read_only|from:getAge',
        'canBuyAlcohol' => 'read_only|from:canBuyAlcohol',
        'price' => 'required|price',
        'children' => 'array',
        'children.*.name' => 'required|string|min:2|max:30|alphaspace'
    ];
    
    public function getFullName()
    {
        return $this->get('first_name') . ' ' . $this->get('last_name');
    }
    
    public function getAge()
    {
        return (new DateTime())->format('y') - $this->get('dob')->format('Y');
    }
    
    public function canBuyAlcohol()
    {
        return $this->get('age') >= 18;
    }
}

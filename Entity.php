<?php

class Entity
{
    private $schema = [
        'first_name' => 'required|string|min:2|max:30|alphaspace',
        'last_name' => 'required|string|min:2|max:30|alphaspace',
        'full_name' => 'read_only|from:getFullName',
        'age' => 'required|int|min:18|max:200',
        'price' => 'required|price',
        'children' => 'array',
        'children.*.name' => 'required|string|min:2|max:30|alphaspace'
    ];
    
    public function getFullName()
    {
        return $this->get('first_name') . ' ' . $this->get('last_name');
    }
}

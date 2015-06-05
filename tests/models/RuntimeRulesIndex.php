<?php

namespace rockunit\models;


class RuntimeRulesIndex extends RuntimeIndex
{
    public function rules()
    {
        return [
            [
                'type_id', 'required', 'int'
            ],
        ];
    }
} 
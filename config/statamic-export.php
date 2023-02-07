<?php

// either: csv, json, xlsx

return [
    'default' => 'csv',
    'collections' => [
        // 'collection_name' => 'xlsx'
    ],
    'excluded_columns' => [
        'blueprint',
        'updated_by',
    ]
];

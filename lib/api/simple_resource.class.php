<?php
namespace api;

class simple_resource extends resource {

    protected $_data                = [
        'id'                        => null,
        'name'                      => null
    ];

    protected $_default_options     = [
        'get'                       => [
            'with'                  => [],
            'filter'                => [
                'id'                => [],
                'name'              => [],
                'search'            => [],
                'offset_start'      => 0,
                'offset_end'        => 100
            ]
        ],

        'post'                      => [],
        'delete'                    => []
    ];
}

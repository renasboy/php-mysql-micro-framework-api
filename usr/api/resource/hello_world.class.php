<?php
namespace api\resource;

class hello_world extends \api\simple_resource {

    // this is the data structure for the resource
    protected $_data                	= [
        'id'                          	=> null,
        'created'                   	=> null,
        'modified'                   	=> null,
        'seo'                       	=> null,
        'name'                       	=> null
    ];

    // these are default request options
    protected $_default_options     	= [
        'get'                       	=> [
            'with'                  	=> [],
            'filter'                	=> [
                'id'                	=> [],
                'created_before'        => [],
                'created_after'         => [],
                'seo'               	=> [],
                'search'            	=> [],
                'active'            	=> [1],
                'offset_start'      	=> 0,
                'offset_end'        	=> 100
            ]
        ],
        'post'                      	=> [
            'fields'                	=> [
                'id'                	=> null,
                'seo'               	=> null,
                'name'               	=> null,
                'created'               => true,
                'modified'              => null,
                'active'            	=> 1
            ],
            'relations'             	=> []
        ],
        'delete'                    	=> [
            'fields'                	=> [
                'id'                	=> null,
                'seo'               	=> null
            ]
        ]
    ];
}

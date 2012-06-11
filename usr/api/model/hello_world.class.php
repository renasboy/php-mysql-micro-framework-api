<?php
namespace api\model;

class hello_world extends \api\simple_model {

    // this is the fields/types for the model
    protected $_fields              = [
        'id'                        => 'number',
        'created'                   => 'datetime',
        'modified'                  => 'datetime',
        'seo'                       => 'seo',
        'name'                      => 'text',
        'active'                    => 'flag',
    ];

    // these are the relations for the model
    protected $_relations           = [
        'many_one'                  => [],
        'one_many'                  => [],
        'many_many'                 => []
    ];

    protected $_dependencies        = [
    ];

    protected $_validate            = [
        'save'                      => [
            'relation'              => [],
            'field'                 => [
                'seo',
                'name'
            ]
        ]
    ];

    // FIND
    // each of these methods maps to the
    // list of "with" options in the
    // resource get method
    /*
    public function find_relations () {
        // get dependency model object
        $model      = $this->_dependencies['relation'];

        // compose options for the find call
        $options    = [
            'hello_world_id'      => $this->id,
            'active'        => 1,
            'offset_start'  => 0,
            'offset_end'    => 1000
        ];

        // execute find call and return result
        return $model->find($options);
    }
    */

    // SAVE
    // each of these methods maps to the
    // list of "relations" options in the
    // resource post method. Those methods
    // can be used to save object aggregations

    // This method is used if the relations row 
    // already exists in the foreigb table
    /*
    public function save_relation_id ($options) {
        // get dependency model object
        $model      = $this->_dependencies['hello_world_relation'];

        // populate its own dependencies
        $model->dependencies($this->_dependencies);

        // add this models id to save options
        $options['fields']['hello_world_id'] = $this->id;

        // validate save options
        $options    = $model->validate_save($options);
        
        // execute save call and return results
        return $model->save($options['fields']);
    }

    // TODO see if it is necessary to call resource->post_relations
    // This method is used if the relation row 
    // does not exists in the foreigb table
    // then it calls the _id method to insert the N-N row
    public function save_relation ($options) {
        // get dependency model object
        $model      = $this->_dependencies['relation'];

        // populate its own dependencies
        $model->dependencies($this->_dependencies);

        // add this models id to save options
        $options['relations']['hello_world_id'] = $this->id;

        // validate save options
        $options    = $model->validate_save($options);

        // execute save call and store result
        $model_id   = $model->save($options['fields']);

        // if save first object succeed we go for the relations
        if ($model_id) {

            // create save options for the relations
            // with the id of the object saved and add
            // the n fields to it
            $options                = [
                'fields'            => [
                    'relation_id'   => $model_id,
                    'description'   => $options['fields']['hello_world_description'],
                    'private'       => 0,
                    'sort'          => 0
                ]
            ];

            // call save relation that is a method defined above
            $result     = $this->save_relation_id($options);

            // if it fails break it all
            if (!$result) {
                // TODO should do something better here
                // as delete just inserted object
                // or return a proper message to user
                // this will be seen as a complete failure
                // while it could be partial failure
                return false;
            }
        }

        // return main object id
        return $model_id;
    }
    */
}

<?php
namespace api;

class simple_model extends model {

    protected $_fields          = [
        'id'                    => 'number',
        'name'                  => 'text'
    ];

    // validate save action fields based
    // on the _validate array
    public function validate_save ($data) {
        foreach ($this->_validate['save'] as $type => $fields) {
            $method = '_validate_' . $type;
            foreach ($fields as $field) {
                // This is to make sure we are *ONLY* validating
                // *ALL* fields during insert and not during update
                if ((array_key_exists('id', $data['fields']) &&
                    ((array_key_exists($field, $data['fields']) &&
                    $data['fields'][$field]) ||
                    (array_key_exists($field, $data['relations']) &&
                    $data['relations'][$field]))) ||
                    !array_key_exists('id', $data['fields'])) {
                    $data = $this->$method($field, $data);
                }
            }
        }
        return $data;
    }

    // validates a many to one relation
    // using id and name. It checks for the
    // relation_id first, then for the
    // relation name, makes sure it exists
    // and re-assign the options values
    protected function _validate_relation ($relation, $data) {
        if (!$data['fields'][$relation . '_id']) {
            if (!$data['fields'][$relation]) {
                $this->_error->bad_request('Missing paramenter: ' . $relation);
            }
            $option_value = $data['fields'][$relation];
            $options = ['name' => $option_value];
        }
        else {
            $option_value = $data['fields'][$relation . '_id'];
            $options = ['id' => $option_value];
        }
        $model = $this->_dependencies[$relation];
        $model = $model->find(['offset_start' => 0, 'offset_end' => 1] + $options);
        if (!$model) {
            $this->_error->bad_request('Unknown ' . $relation . ': ' . $option_value);
        }
        $data['fields'][$relation . '_id']  = $model->id;
        if (!empty($model->name)) {
            $data['fields'][$relation]      = $model->name;
        }
        return $data;
    }

    // validates a field by checking
    // its presence in the options
    protected function _validate_field ($field, $data) {
        if (!$data['fields'][$field]) {
            $this->_error->bad_request('Missing paramenter: ' . $field);
        }
        return $data;
    }
}

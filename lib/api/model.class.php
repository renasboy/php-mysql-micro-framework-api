<?php
namespace api;

class model {

    // this is the fields/types for the model
    protected $_fields              = [];

    // these are the relations for the model
    protected $_relations           = [
        'many_one'                  => [],
        'one_many'                  => [],
        'many_many'                 => []
    ];

    // these are the model dependencies
    protected $_dependencies        = [];

    // these are the dependency objects library
    protected $_dao                 = null;
    protected $_validator           = null;
    protected $_error               = null;
    protected $_logger              = null;

    public function __construct (
        dao         $dao,
        validator   $validator,
        error       $error,
        logger      $logger) {
        $this->_dao         = $dao;
        $this->_validator   = $validator;
        $this->_error       = $error;
        $this->_logger      = $logger;
    }

    public function find ($options) {
        $result = $this->_dao->read($options);
        $method = 'read_many';
        if (array_key_exists('offset_start', $options) &&
            $options['offset_start'] == 0 &&
            array_key_exists('offset_end', $options) &&
            $options['offset_end'] == 1) {
                $method = 'read_one';
        }
        return $this->_dao->$method(
            $result,
            get_class($this),
            [$this->_dao, $this->_validator, $this->_error, $this->_logger],
            $this->dependencies()
        );
    }

    // converts the fields options into
    // query fields using the model->_fields
    // and call the write in the dao
    public function save ($fields) {
        // TODO here is where empty fields get removed, booleans as well
        // that is why we always pass 'false' string
        $query_fields = array_filter(array_intersect_key($fields, $this->_fields));
        return $this->_dao->write($query_fields);
    }

    public function remove ($options) {
        return $this->_dao->erase($options);
    }

    // Validates the fields set in the
    // model data based on model definition
    public function validate () {
        foreach ($this->_fields as $field => $type) {
            if (array_key_exists($field, $this->_data) && 
                !$this->_validator->{'is_' . $type}($this->_data[$field])) {
                $this->_logger->error('Invalid field [' . $field . '] value [' . $this->_data[$field] . '] type [' . $type . ']');
                $this->_error->bad_request('Invalid value for field: ' . $field);
            }
        }
        return true;
    }

    // retrieve the relation dependency if no
    // value is specified otherwise sets the
    // new relation objects
    public function dependencies ($value = null) {
        if ($value === null) {
            return $this->_dependencies;
        }
        $this->_dependencies = $value;
    }

    public function relations () {
        return $this->_relations;
    }
}

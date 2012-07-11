<?php
namespace api;

class resource {

    // this is the data structure for the resource
    protected $_data                = [];

    // these are default request options
    protected $_default_options     = [
        'get'                       => [
            'with'                  => [],
            'filter'                => []
        ],
        'post'                      => [
            'fields'                => [],
            'relations'             => []
        ],
        'delete'                    => [
            'fields'                => []
        ]
    ];

    // this is the current options passed
    protected $_options             = [];

    // these are the dependency objects library
    protected $_model               = null;
    protected $_request             = null;
    protected $_conf                = null;
    protected $_logger              = null;
    protected $_error               = null;

    public function __construct (
        model       $model,
        request     $request,
        conf        $conf,
        logger      $logger,
        error       $error
    ) {
        $this->_model       = $model;
        $this->_request     = $request;
        $this->_conf        = $conf;
        $this->_logger      = $logger;
        $this->_error       = $error;

        $this->_options     = $this->_options($this->_default_options[$this->_request->method()]);

        if ($this->_request->is_unique()) {
            // TODO field name should not be mentioned
            // THIS is what is causing resources with unique parameters to be always only one
            // CHANGED the request is_unique not to approve array values of unique parameter
            // ACTIVE CANNOT be used here, please remove
            $options        = ['active' => [0, 1], 'offset_start' => 0, 'offset_end' => 1] +
                $this->_request->get_unique() +
                $this->_default_options['get']['filter'];
            $current        = $this->_model->find($options);
            if ($current) {
                $this->_model = $current;
            }

        }
    }

    // main get method called once
    // an HTTP GET is issued
    // it identifies the uniqueness
    // and call the appropriated
    // get method for one or many
    public function get () {
        if ($this->_request->is_unique() ||
            (array_key_exists('offset_start', $this->_options['filter']) &&
            $this->_options['filter']['offset_start'] == 0 &&
            array_key_exists('offset_end', $this->_options['filter']) &&
            $this->_options['filter']['offset_end'] == 1)) {
            return $this->_get_one();
        }
        return $this->_get_many();
    }

    // select objects from the model
    // based on the "filter" options
    // also call populate using the
    // "with" options
    private function _get_many () {
        $models = $this->_model->find($this->_options['filter']);
        return $this->_populate($models, $this->_data, $this->_options['with']);
    }
    
    // call pouplate using the "with" options
    // in the current data
    private function _get_one () {
        // TODO check if this can be better
        // the whole confusion with is_unique and offset_end=1
        // TODO avoid the use of ID here
        if (empty($this->_model->id)) {
            $this->_model = $this->_model->find($this->_options['filter']);
        }
        if (empty($this->_model->id)) {
            $this->_error->not_found('Specified unique resource does not exist.');
        }
        return $this->_populate([$this->_model], $this->_data, $this->_options['with']);
    }

    // main post method called once
    // an HTTP POST is issued
    // it identifies the uniqueness
    // and call save in the model 
    public function post () {
        $this->_request->validate_unique();
        // TODO check on how not to mention field names (ID) here
        if (!empty($this->_model->id)) {
            $this->_options['fields']['id'] = $this->_model->id;
        }
        $this->_options = $this->_model->validate_save($this->_options);
        $result = $this->_model->save($this->_options['fields']);
        if ($result) {
            // We need to check for true because update
            // returns true instead of the entity id
            if ($result !== true) {
                // TODO avoid ID if possible, seems not to
                $this->_model->id = $result;
            }
            $this->_post_relations($this->_options['relations']);
        }
        return $result;
        // TODO check on how to return 201 HTTP code
    }

    // for all filled relations attempt to save it
    private function _post_relations ($relations) {
        foreach (array_filter($relations) as $relation => $objects) {
            // TODO convert relations to options
            $options = $this->_options(
                $this->_default_options['post']['relations'][$relation],
                $objects);
            $this->_model->{'save_' . $relation}($options);
        }
    }

    // main delete method called once
    // an HTTP DELETE is issued.
    // it validates the uniqueness
    // and call remove in the model
    public function delete () {
        $this->_request->validate_unique();
        $response = true;
        // TODO check on how not to mention field names (ID) here
        if (!empty($this->_model->id)) {
            $response = $this->_model->remove(['id' => $this->_model->id]);
        }
        return $response;
    }

    // populate resource object from model
    // based on the data array also call 
    // the populate relations
    private function _populate ($objects, $data, $options = null) {
        $resources          = [];
        foreach ($objects as $object) {
            // This array_filter is where all empty parameters get
            // removed from returned resource, booleans and zeros
            // get removed as well
            $resource       = array_filter(array_intersect_key((array)$object, $data));
            if ($options === null) {
                $resources[]    = $resource;
            }
            else {
                $resources[]    = $this->_populate_relations($resource, $object, $options);
            }
        }
        return $resources;
    }

    // populate resource relations from model
    // based on the "with" options, also populate
    // the fields in the relation based on data[option]
    private function _populate_relations ($resource, $object, $options) {
        foreach ($options as $option => $value) {
            if ($value !== false) {
                // Passing filter here to find_* is mainly used by
                // find_count_* into some models to get the created_after
                // Those should be the filter for main model not relations
                $resource[$option] = $this->_populate(
                    $object->{'find_' . $option}($this->_options['filter']),
                    $this->_data[$option]
                );
            }
        }
        return $resource;
    }

    // get options per request method
    // otherwise get default value from
    // options array
    private function _options ($default_options, $options_source = []) {
        $options            = [];
        foreach ($default_options as $option => $variants) {
            $options[$option] = [];
            foreach ($variants as $variant => $default_value) {
                // TODO check if this is true, that for relations
                // we can not to apply the defaults
                if ($option != 'relations') {
                    $options[$option][$variant] = $default_value;
                }
                if ($options_source) {
                    if (array_key_exists($variant, $options_source)) {
                        $options[$option][$variant] = $options_source[$variant];
                    }
                }
                else if ($this->_request->get($variant)) {
                    $options[$option][$variant] = $this->_request->get($variant);
                }
            }
        }
        return $options;
    }

    public function to_string ($data) {
        // check for accept once we decide
        // to support other protocol
        //if (strstr($this->_request->accept(), 'json')) {
            // TODO remove JSON_PRETTY_PRINT
            return json_encode($data, JSON_HEX_QUOT | JSON_PRETTY_PRINT);
        //}
    }
}

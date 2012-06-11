<?php
namespace api;

class request extends \core\request {

    const DEFAULT_CONTENT   = 'application/json';

    public function resource () {
        $resource = $this->_conf->get('default.resource');
        if ($this->uri()) {
            $resource = explode('/', $this->uri())[0];
        }
        return $resource;
    }

    public function resources () {
        return array_keys($this->_conf->get());
    }

    public function resource_uri () {
        return $this->_conf->get($this->resource() . '.resource_uri');
    }

    public function methods () {
        // TODO read from conf
        return ['get', 'post', 'put', 'delete'];
    }

    public function accept () {
        $accept = self::DEFAULT_CONTENT;
        if (array_key_exists('HTTP_ACCEPT', $this->_server)) {
            $accept = $this->_server['HTTP_ACCEPT'];
        }
        return $accept;
    }

    protected function _append_params () {
        // TODO commented since there is no HTTP calls yet
        // input from APP local calls are bleeding into the API
        // this means that request parameters from the APP are 
        // being mixed up with request for the API
        //$this->_append_body_params ();
        $this->_append_uri_params();
    }

    // add the parameters from input to the _REQUEST
    private function _append_body_params ($input = null) {
        if ($input === null) {
            // For testing purposes the input
            // can be passed as parameter
            $input = file_get_contents('php://input');
        }
        if (!empty($input)) {
            if (strstr($this->accept(), 'json')) {
                $json           = json_decode($input, true);
                if ($json === null) {
                    $this->_error->bad_request('Invalid json data');
                }
                $this->_request += $json;
            }
            else {
                parse_str($input, $this->_request);
            }
        }
    }
    
    // add parameters from uri to the _REQUEST using
    // the resource mapping in the configuration file
    private function _append_uri_params () {
        $request_uri    = array_slice(explode('/', $this->uri()), 1);
        $resource_uri   = explode('/', $this->resource_uri());

        // if the url has more parameters then expected 
        if (count($resource_uri) < count($request_uri)) {
            $this->_error->not_found('Resource not found ' . $this->uri());
        }
        $this->_request += array_combine($resource_uri, array_pad($request_uri, count($resource_uri), null));
    }
}
?>

<?php
namespace api;

class api {

    // these are the dependency objects library
    private $_resource          = null;
    private $_model             = null;
    private $_dao               = null;
    private $_validator         = null;
    private $_query             = null;
    private $_db                = null;
    private $_request           = null;
    private $_error             = null;
    private $_logger            = null;
    private $_conf              = null;

    public function dispatch ($_request, $_server) {

        $this->_conf            = new \api\conf(API_CONF);

        $this->_logger          = new \api\logger(
            $this->_conf->get('logger_root'),
            $this->_conf->get('logger_level')
        );

        $this->_logger->debug(sprintf(
            'Started logger at %s and level %s' ,
            $this->_conf->get('logger_root'),
            $this->_conf->get('logger_level')));

        $this->_error           = new \api\error(
            $this->_conf->get('error_reporting'),
            $this->_logger
        );

        $this->_request         = new \api\request(
            $_request,
            $_server,
            new \api\conf($this->_conf->get('conf_request'), true),
            $this->_error
        );

        $resource               = $this->_request->resource();
        $this->_logger->debug(sprintf('Resource: %s', $resource));
        if (!in_array($resource, $this->_request->resources())) {
            $this->_error->not_found('Resource not found: ' . $resource);
        }

        $method                 = $this->_request->method();
        $this->_logger->debug(sprintf('Method: %s', $method));
        if (!in_array($method, $this->_request->methods())) {
            $this->_error->method_not_allowed('Method: ' . $method);
        }

        $this->_db              = new \api\db(
            $this->_conf->get('db_host'),
            $this->_conf->get('db_port'),
            $this->_conf->get('db_user'),
            $this->_conf->get('db_pass'),
            $this->_conf->get('db_name'),
            $this->_logger,
            $this->_error
        );

        $this->_query           = new \api\query($this->_db);
        $this->_validator       = new \api\validator();

        $dao                    = '\api\dao\\' . $resource;
        $this->_dao             = new $dao(
            $this->_db,
            $this->_query
        );

        $model                  = '\api\model\\' . $resource;
        $this->_model           = new $model(
            $this->_dao,
            $this->_validator,
            $this->_error,
            $this->_logger
        );

        $this->_inject_dependencies();

        $resource               = '\api\resource\\' . $resource;
        $this->_resource        = new $resource(
            $this->_model,
            $this->_request,
            $this->_conf,
            $this->_logger,
            $this->_error
        );

        return $this->_resource->$method();
    }

    private function _inject_dependencies () {
        $dependencies = $this->_model->dependencies();
        foreach ($dependencies as $name => $object) {
            if ($object === null) {
                $dao        = '\api\dao\\' . $name;
                $dao        = new $dao(
                    $this->_db,
                    $this->_query
                );
                $model      = '\api\model\\' . $name;
                $dependencies[$name] = new $model(
                    $dao,
                    $this->_validator,
                    $this->_error,
                    $this->_logger
                );
            }
        }
        $this->_model->dependencies($dependencies);
    }
}

<?php
namespace api;

abstract class dao {

    // these are the dependency objects library
    protected $_db          = null;
    protected $_query       = null;

    // abstract methods to be implemented
    // by the concrete dao class
    abstract protected function _read_query ($options);
    abstract protected function _write_query ($options);
    abstract protected function _erase_query ($options);

    public function __construct (
        db      $db,
        query   $query
    ) {
        $this->_db          = $db;
        $this->_query       = $query;
    }
    
    public function read ($options) {
        return $this->_execute($this->_read_query($options));
    }

    public function write ($options) {
        return $this->_execute($this->_write_query($options));
    }

    public function erase ($options) {
        return $this->_execute($this->_erase_query($options));
    }

    protected function _execute ($query) {
        return $this->_db->query($query);
    }
}

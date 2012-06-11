<?php
namespace api;

class db {

    // these are the dependency parameters
    private $_connection        = null;
    private $_host              = null;
    private $_port              = null;
    private $_user              = null;
    private $_pass              = null;
    private $_name              = null;

    // these are the dependency objects library
    private $_logger            = null;
    private $_error             = null;

    public function __construct (
        $host,
        $port,
        $user,
        $pass,
        $name,
        logger $logger,
        error $error
    ) {
        $this->_host            = $host;
        $this->_port            = $port;
        $this->_user            = $user;
        $this->_pass            = $pass;
        $this->_name            = $name;
        $this->_logger          = $logger;
        $this->_error           = $error;
    }

    public function query ($query) {
        $this->_init();
        $this->_logger->debug('DB - Query: [' . $query . ']');
        $result = mysql_query($query);
        if (!$result) {
            $this->_logger->error('DB - Query failed: [' . $query . '] with error [' . $this->_error() . ']');
            if (in_array($this->_errno(), $this->_safe_errors())) {
                return true;
            }
            $this->_error->internal_server_error('Failed to communicate to database.');
        }
        else if (strpos($query, 'INSERT') === 0) {
            $last_id = $this->_last_id();
            if ($last_id) {
                $result = $last_id;
            }
        }
        return $result;
    }

    public function next ($result, $object = null, $params = []) {
        return mysql_fetch_object($result, $object, $params);
    }

    public function escape ($string) {
        $this->_init();
        return mysql_real_escape_string($string);
    }

    private function _init () {
        if ($this->_connection !== null) {
            return;
        }
        $this->_logger->debug('DB - Attempt to connect to database.');
        $this->_connection = mysql_pconnect(
            $this->_host . ':' . $this->_port,
            $this->_user,
            $this->_pass
        );
        if (!$this->_connection) {
            $this->_logger->error('DB - Failed to connect to database.');
            $this->_error->internal_server_error('Failed to connect to database.');
        }
        $db = mysql_select_db($this->_name, $this->_connection);
        if (!$db) {
            $this->_logger->error('DB - Failed to select database.');
            $this->_error->internal_server_error('Failed to select database.');
        }
        $this->_logger->debug('DB - Connected to database.');
        $this->_pos_connect();
    }

    private function _pos_connect() {
        $this->_logger->debug('DB - Set UTF8 charset');
        mysql_query('SET NAMES UTF8');
    }

    private function _last_id () {
        return mysql_insert_id();
    }

    private function _errno () {
        return mysql_errno();
    }

    private function _error() {
        return mysql_error();
    }

    private function _safe_errors () {
        // 1062 Dupplicated entry
        return [1062];
    }
}

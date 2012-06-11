<?php
namespace api;

trait read_only_resource {

    public function post () {
        $this->_error->method_not_allowed('This is a read only resource');
    }

    public function delete () {
        $this->_error->method_not_allowed('This is a read only resource');
    }
}

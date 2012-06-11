<?php
namespace api\dao;

class hello_world extends \api\simple_dao {

    protected function _read_query ($filter) {
        $query = 'SELECT hello_world.* FROM hello_world';

        $filters    = [];
        $filters[]  = $this->_condition_query('hello_world.id', 'is', $filter, 'id');
        $filters[]  = $this->_condition_query('hello_world.seo', 'is', $filter, 'seo');
        $filters[]  = $this->_condition_query('hello_world.name', 'is', $filter, 'name');
        $filters[]  = $this->_condition_query('hello_world.active', 'is', $filter, 'active');

        $or         = [];
        $or[]       = $this->_condition_query('hello_world.seo', 'like', $filter, 'search');
        $or[]       = $this->_condition_query('hello_world.name', 'like', $filter, 'search');
        $filters[]  = $this->_logical_condition_query('or', $or);

        $where      = $this->_logical_condition_query('and', $filters);

        $query .= ' WHERE ' . $where;
        $query .= ' GROUP BY hello_world.id';

        $query .= ' ORDER BY hello_world.id DESC';
        $query .= $this->_limit_query($filter);
        return $query;
    }
}

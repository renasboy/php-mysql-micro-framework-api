<?php
namespace api;

class simple_dao extends dao {

    // return single object record
    public function read_one ($result, $object = null, $params = [], $dependencies) {
        $one = $this->_db->next($result, $object, $params);
        if ($one) {
            $one->dependencies($dependencies);
        }
        return $one;
    }

    // return many object records as array
    public function read_many ($result, $object, $params = [], $dependencies) {
        $many = [];
        while ($record = $this->read_one($result, $object, $params, $dependencies)) {
            $many[] = $record;
        }
        return $many;
    }

    // return simple array of ids
    public function read_ids ($result) {
        $ids = [];
        while ($record = $this->read_one($result)) {
            $ids[] = $record->id;
        }
        return $ids;
    }

    // return simple array with id => name 
    public function read_list ($result) {
        $list = [];
        while ($record = $this->read_one($result)) {
            $list[$record->id] = $record->name;
        }
        return $list;
    }

    // simple select_query
    protected function _read_query ($filter) {
        $query = 'SELECT * FROM ' . $this->_table();

        $filters    = [];
        $filters[]  = $this->_condition_query($this->_table() . '.id', 'is', $filter, 'id');
        $filters[]  = $this->_condition_query($this->_table() . '.name', 'is', $filter, 'name');
        $filters[]  = $this->_condition_query($this->_table() . '.name', 'like', $filter, 'search');
        $filters[]  = $this->_condition_query($this->_table() . '.active', 'is', $filter, 'active');
        $where      = $this->_logical_condition_query('and', $filters);

        if ($where) {
            $query .= ' WHERE ' . $where;
        }
        $query .= ' ORDER BY ' . $this->_table() . '.id';
        $query .= $this->_limit_query($filter);
        return $query;
    }

    // simple insert_query
    protected function _write_query ($fields) {
        $where = null;
        $set = [];
        foreach ($fields as $field => $value) {
            if ($field == 'id' && $value) {
                // Had to add null here to be able to insert new models
                // where the unique resoure url needs to be created
                if ($value != 'null') {
                    $where = ' WHERE ' . $this->_condition_query($field, 'is', $fields, $field);
                }
                continue;
            }
            $set[$field] = $this->_condition_query($field, 'is', $fields, $field);
        }
        $set    = array_filter($set);
        if ($where) {
            $query = 'UPDATE ';
            // TODO check on a better way to do that
            // also the resource definition has a created=true
            // TODO remove this
            if (array_key_exists('created', $fields)) {
                unset($set['created']);
            }
        }
        else {
            $query = 'INSERT INTO ';
            // TODO check on a better way to do that
            // also the resource definition has a created=true
            if (array_key_exists('created', $fields)) {
                $fields['created'] = date('Y-m-d H:i:s');
                $set['created'] = $this->_condition_query('created', 'is', $fields, 'created');
            }
        }
        $query .= $this->_table() . ' SET ';
        $query .= implode(', ', $set);
        $query .= $where;
        return $query;
    }

    protected function _erase_query ($options) {
        $query = 'DELETE FROM ' . $this->_table();
        $query .= ' WHERE ' . $this->_condition_query('id', 'is', $options, 'id');
        return $query;
    }

    // this is a proxy to the query object mainly
    // to check for options before add it to query
    protected function _condition_query ($field, $condition, $filter, $key) {
        if (!array_key_exists($key, $filter) ||
            empty($filter[$key])) {
            return null;
        }
        if ($condition == 'is' && $filter[$key] == 'NULL') {
            return $this->_query->isnull($field);
        }
        return $this->_query->$condition($field, $filter[$key]);
    }

    // this is a proxy to the query object mainly
    // to check for options before add it to query
    protected function _logical_condition_query ($logic, $conditions) {
        $conditions = array_filter($conditions);
        if (empty($conditions)) {
            return null;
        }
        return $this->_query->{$logic . '_clause'}($conditions);
    }

    // this is a proxy to the query object mainly
    // to check for options before add it to query
    protected function _limit_query ($filter) {
        if (!array_key_exists('offset_start', $filter)) {
            $filter['offset_start'] = 0;
        }
        if (!array_key_exists('offset_end', $filter)) {
            $filter['offset_end'] = 100;
        }
        return $this->_query->limit($filter['offset_start'], $filter['offset_end']);
    }

    // get table name (dao name) out of namespace
    protected function _table () {
        return explode('\\', get_class($this))[2];
    }
}

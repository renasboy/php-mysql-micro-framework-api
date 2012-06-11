<?php
namespace api;

class query {

    // these are the dependency objects library
    private $_db            = null;

    public function __construct (db $db) {
        $this->_db          = $db;
    }

    /*
    // TODO use this method to create full queries
    public function select (
        $select,
        $from,
        $where,
        $order,
        $limit
    ) {
        $query = null;
        return $query;
    }

    // TODO use this method to create full queries
    public function insert () {
        $query = null;
        return $query;
    }

    // TODO use this method to create full queries
    public function update () {
        $query = null;
        return $query;
    }

    // TODO use this method to create full queries
    public function delete () {
        $query = null;
        return $query;
    }
    */

    public function is ($column, $value) {
        if (is_array($value)) {
            if (count($value) > 1) {
                return $this->in($column, $value);
            }
            $value = array_shift($value);
        }
        return sprintf('%s = "%s"', $column, $this->_db->escape($value));
    }

    public function is_not ($column, $value) {
        if (is_array($value)) {
            if (count($value) > 1) {
                return $this->not_in($column, $value);
            }
            $value = array_shift($value);
        }
        return sprintf('%s != "%s"', $column, $this->_db->escape($value));
    }

    // is_null already exists, so isnull is the way to go
    public function isnull ($column) {
        return sprintf('%s IS NULL', $column);
    }

    public function in ($column, $values) {
        array_map([$this->_db, 'escape'], $values);
        return sprintf('%s IN ("%s")', $column, implode('","', $values));
    }

    public function not_in ($column, $values) {
        array_map([$this->_db, 'escape'], $values);
        return sprintf('%s NOT IN ("%s")', $column, implode('","', $values));
    }

    public function like ($column, $value) {
        if (is_array($value)) {
            if (count($value) > 1) {
                $like = [];
                foreach ($value as $_value) {
                    $like[] = $this->like($column, $_value);
                }
                return $this->or_clause($like);
            }
            $value = array_shift($value);
        }
        return sprintf('%s LIKE "%%%s%%"', $column, $this->_db->escape($value));
    }

    public function greater ($column, $value) {
        return sprintf('%s > "%s"', $column, $this->_db->escape($value));
    }

    public function smaller ($column, $value) {
        return sprintf('%s < "%s"', $column, $this->_db->escape($value));
    }

    public function between ($column, $min, $max) {
        return sprintf('%s BETWEEN %s AND %s', $column, intval($min), intval($max));
    }

    public function and_clause ($parts) {
        return '(' . implode(' AND ', $parts) . ')';
    }

    public function or_clause ($parts) {
        return '(' . implode(' OR ', array_filter($parts)) . ')';
    }

    public function limit ($min = 0, $max = 100) {
        return sprintf(' LIMIT %d, %d', intval($min), intval($max));
    }
}

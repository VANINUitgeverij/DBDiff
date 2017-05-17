<?php namespace DBDiff\SQLGen\DiffToSQL;

use DBDiff\SQLGen\SQLGenInterface;


class InsertDataSQL implements SQLGenInterface {

    function __construct($obj, $params) {
        $this->obj = $obj;
        $this->params = $params;
    }

    public function getUp() {
        $table = $this->obj->table;
        $values = $this->obj->diff['diff']->getNewValue();

        $columns = '';
        if ($this->params->autoincrement) {
            $keys = $this->obj->diff['keys'];
            $values = array_diff_key($values, $keys);
            $columns = array_keys($values);

            if (!empty($columns)) {
                $columns = "(`".implode('`,`', $columns)."`)";
            }
        }

        $values = array_map(function ($el) {
            if(!is_null($el)) {
                return "'" . addslashes($el) . "'";
            }
            else {
                return 'NULL';
            }
        }, $values);
        return "INSERT INTO `$table` $columns VALUES(".implode(',', $values).");";
    }

    public function getDown() {
        $table = $this->obj->table;

        $keys = $this->obj->diff['keys'];

        if ($this->params->autoincrement) {
            $values = $this->obj->diff['diff']->getNewValue();
            $keys = array_diff_key($values, $keys);
        }

        array_walk($keys, function(&$value, $column) {
            $value = '`'.$column."` = '".addslashes($value)."'";
        });
        $condition = implode(' AND ', $keys);
        return "DELETE FROM `$table` WHERE $condition;";
    }

}

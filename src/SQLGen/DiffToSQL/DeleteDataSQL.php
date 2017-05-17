<?php namespace DBDiff\SQLGen\DiffToSQL;

use DBDiff\SQLGen\SQLGenInterface;


class DeleteDataSQL implements SQLGenInterface {

    function __construct($obj, $params) {
        $this->obj = $obj;
        $this->params = $params;
    }

    public function getUp() {
        $table = $this->obj->table;
        $keys = $this->obj->diff['keys'];

        if ($this->params->autoincrement) {
            $values = $this->obj->diff['diff']->getOldValue();
            $keys = array_diff_key($values, $keys);
        }

        array_walk($keys, function(&$value, $column) {
            $value = '`'.$column."` = '".addslashes($value)."'";
        });
        $condition = implode(' AND ', $keys);
        return "DELETE FROM `$table` WHERE $condition;";
    }

    public function getDown() {
        $table = $this->obj->table;
        $values = $this->obj->diff['diff']->getOldValue();

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
            return "'".addslashes($el)."'";
        }, $values);
        return "INSERT INTO `$table` $columns VALUES(".implode(',', $values).");";
    }

}

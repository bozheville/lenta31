<?php

class DB {
    public $db = null;
    private $client = null;

    public function __construct($name = null) {
        $this->init();
        if ((boolean) $name) $this->useDB($name);
    }

    private function init() {
        $this->client = new Mongo();
    }

    public function useDB($name) {
        $this->db = $this->client->selectDB($name);
    }

    public function find($collection, $condition = array(), $limit = 0, $skip = 0, $sort = array(), $use_keys = true) {
        $cursor = $this->db->$collection->find($condition);
        if ((boolean) $sort) $cursor = $cursor->sort($sort);
        if ((int) $skip > 0) $cursor = $cursor->skip((int) $skip);
        if ((int) $limit > 0) $cursor = $cursor->limit((int) $limit);
        if (!$use_keys) {
            $result = iterator_to_array($cursor, $use_keys);
        } else {
            $result = array();
            foreach ($cursor as $doc) {
                $result[$doc["_id"]] = $this->processCursor($doc);
            }
        }
        return $result;
    }

    public function count($collection, $condition = array()) {
        return $this->db->$collection->count($condition);
    }

    public function findOne($collection, $condition = array()) {
        $doc = $this->processCursor($this->db->$collection->findOne($condition));
        if (empty($doc["_id"])) {
            $doc = null;
        }
        return $doc;
    }

    private function processCursor($doc) {
        $id = '$id';
        if (isset($doc["_id"]->$id)) {
            $doc["_id"]->$id;
        } else {
            $_id = $doc["_id"];
        }
        $doc["_id"] = $_id;
        return $doc;
    }

    public function insert($collection, $insert) {
        $this->db->$collection->insert($insert);
    }

    public function update($collection, $update, $condition, $upsert = true, $multi = false) {
        $this->db->$collection->update($condition, $update, array("upsert" => (boolean) $upsert, 'multiple' => (boolean) $multi));
    }

    public function drop($collection) {
        $this->db->drop($collection);
    }

    public function getVal($collection, $condition, $field = "_id") {
        $field = explode("::", $field);
        $return = null;
        $data = $this->findOne($collection, $condition);
        while ($key = array_shift($field)) {
            $data = $data[$key];
        }
        return $data;
    }

    /**
     * Generats the unique _id, that is not in DB.
     * @param string $collection Collection
     * @return Returns new unique ID
     */
    public function getNewId($collection, $len) {
        while ($_id = getRandomString($len, 2, "lun")) if (!$this->findOne($collection, array("_id" => $_id))) return $_id;
    }

    public function distinct($collection, $field, $condition = array()) {
        $cursor = $this->db->$collection->distinct($field, $condition);
        return $cursor;
    }

    public function aggregate($collection, $pipeline) {
        $cursor = $this->db->$collection->aggregate($pipeline);
        return $cursor["ok"] ? $cursor["result"] : false;
    }
}
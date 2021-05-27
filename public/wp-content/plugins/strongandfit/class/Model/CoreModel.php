<?php

namespace StrongAndFit\Model;

// this class will allow us to mutualise certain processing operations on the database

abstract class CoreModel
{
    
    protected $database;

    abstract public static function tableName();

    public function __construct()
    {
        global $wpdb;

        // $wpdb is the object managed by wordpress allowing us to communicate with the database
        $this->database = $wpdb;
    }

    public function getTableName()
    {
        // here we get the wp_ prefix
        $prefix = $this->database->prefix;
        // and we add the prefix to the tableName
        return $prefix . $this->tableName();
    }

    public function execute($sql, $parameters = [])
    {
        // IMPORTANT run an SQL query and retrieve the results (when the query does not have a variable part)
        if(empty($parameters)) {
            $results = $this->database->get_results($sql);
            return $results;
        }
        else {
            // IMPORTANT construction of a prepared statement by security
            // DOC wpdb prepare query : https://developer.wordpress.org/reference/classes/wpdb/prepare/
            $preparedStatement = $this->database->prepare($sql, $parameters);
            $results = $this->database->get_results($preparedStatement);
            return $results;
        }
    }

}
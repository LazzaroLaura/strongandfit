<?php

namespace StrongAndFit\Model;

class ParticipationModel extends CoreModel
{
    public $id;
    public $user_id;
    public $program_id;
    public $created_at;
    public $updated_at;


    public static function tableName()
    {
        return 'participation';
    }

    // this function will create the table in database
    public function createTable()
    {
        
        // TIPS $wpdb->prefix allows to retrieve the prefix of the wordpress table
        $sql = "
            CREATE TABLE IF NOT EXISTS `" . $this->getTableName(). "` (
                `id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(8) UNSIGNED NOT NULL,
                `program_id` INT(8) UNSIGNED NOT NULL,
                `created_at` DATETIME,
                `updated_at` DATETIME,
                PRIMARY KEY(`id`)
            );
        ";

        // we need a require by hand of this library in order to be able to use the dbDelta function
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        // table creation, DOC https://developer.wordpress.org/reference/functions/dbdelta/
        dbDelta($sql);
    }

    public function insert()
    {
        $this->database->insert(

            $this->getTableName(),

            // second argument: the values ​​we give to the different columns of the table
            [
                'user_id' => $this->user_id,
                'program_id' => $this->program_id,
                'created_at' => date('Y-m-d H:i:s')
            ]
        );
    }

}
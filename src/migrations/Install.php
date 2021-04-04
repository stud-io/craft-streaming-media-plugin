<?php
namespace StudIO\StreamingMedia\migrations;

use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        if (!$this->db->tableExists('{{%stream_assets}}')) {
          // create the stream asset table
          $this->createTable('{{%stream_assets}}', [
              'id' => $this->integer()->notNull(),
              'source_url' => $this->char(512)->notNull(),
              'backend_transcode' => $this->integer(),
              'backend_storage' => $this->integer(),
              'dateCreated' => $this->dateTime()->notNull(),
              'dateUpdated' => $this->dateTime()->notNull(),
              'uid' => $this->uid(),
              'PRIMARY KEY(id)',
          ]);
      
          // give it a foreign key to the elements table
          $this->addForeignKey(
              $this->db->getForeignKeyName('{{%stream_assets}}', 'id'),
              '{{%stream_assets}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        }
    }

    public function safeDown()
    {
        // ...
    }
}

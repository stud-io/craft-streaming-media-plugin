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
              'draft' => $this->boolean()->notNull(),
              'source_url' => $this->string()->notNull(),
              'transcoding_backend' => $this->integer(),
              'transcoding_backend_status' => $this->string(),
              'transcoding_backend_reference' => $this->string(),
              'storage_backend' => $this->integer(),
              'storage_backend_status' => $this->string(),
              'storage_backend_reference' => $this->string(),
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

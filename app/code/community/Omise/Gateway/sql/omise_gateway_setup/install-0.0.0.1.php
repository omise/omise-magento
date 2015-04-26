<?php
$this->startSetup();

$table = new Varien_Db_Ddl_Table();
$table->setName($this->getTable('omise_gateway/omise'));

$table->addColumn('public_key', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array('nullable' => true));
$table->addColumn('secret_key', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array('nullable' => true));

$table->setOption('type', 'InnoDB');
$table->setOption('charset', 'utf8');

$this->getConnection()->createTable($table);

$this->endSetup();
<?php
$this->startSetup();

$table = new Varien_Db_Ddl_Table();

//create omise_gateway/transaction
$table->setName($this->getTable('omise_gateway/transaction'));

$table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array( 'identity'  => true,
                                                                        'unsigned'  => true,
                                                                        'nullable'  => false,
                                                                        'primary'   => true));

$table->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array('nullable' => false));
$table->addColumn('transaction_id', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array('nullable' => false));

$table->setOption('type', 'InnoDB');
$table->setOption('charset', 'utf8');

$this->getConnection()->createTable($table);

$this->endSetup();
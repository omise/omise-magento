<?php

$this->startSetup();

$table = new Varien_Db_Ddl_Table();

//create omise_transaction/omise
$table->setName($this->getTable('omise_transaction/omise'));
$table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array( 'identity'  => true,
                                                                        'unsigned'  => true,
                                                                        'nullable'  => false,
                                                                        'primary'   => true));

$table->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array('nullable' => false));
$table->addColumn('transaction_id', Varien_Db_Ddl_Table::TYPE_TEXT, 45, array('nullable' => false));


$this->getConnection()->createTable($table);

$this->endSetup();
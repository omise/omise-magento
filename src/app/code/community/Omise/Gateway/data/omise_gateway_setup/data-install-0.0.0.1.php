<?php
$installer = $this;

$model = Mage::getModel('omise_gateway/config');

$dataRows = array(array('id'              => 1,
                        'public_key'      => 'pkey',
                        'secret_key'      => 'skey',
                        'public_key_test' => 'pkey_test',
                        'secret_key_test' => 'skey_test',
                        'test_mode'       => 0));

foreach ($dataRows as $row) {
    $model->setData($row)
          ->setOrigData()
          ->save();
}

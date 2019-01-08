<?php

require_once 'lib/Omise.php';

define('OMISE_PUBLIC_KEY', 'pkey_test_58z1dq6yak7rtm1ivgt');
define('OMISE_SECRET_KEY', 'skey_test_58z1dq6yjtafzovb706');

$account = OmiseAccount::retrieve();
print_r($account);

$capabilities = OmiseCapabilities::retrieve();
print_r($capabilities);


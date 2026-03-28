<?php

namespace Magento\Framework\DB;

interface AdapterInterface
{
    public function update($table, $data, $where);
}

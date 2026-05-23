<?php

namespace App\Database;

use Illuminate\Database\MariaDbConnection as BaseMariaDbConnection;
use App\Database\Schema\Grammars\MariaDbSchemaGrammar;

class MariaDbConnection extends BaseMariaDbConnection
{
    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar()
    {
        $grammar = new MariaDbSchemaGrammar($this);
        
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }
}

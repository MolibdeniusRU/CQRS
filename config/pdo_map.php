<?php

return [
    'db2'        => 'ibm_db2',
    'mssql'      => 'pdo_sqlsrv',
    'mysql'      => 'pdo_mysql',
    'mysql2'     => 'pdo_mysql', // Amazon RDS, for some weird reason
    'postgres'   => 'pdo_pgsql',
    'postgresql' => 'pdo_pgsql',
    'pgsql'      => 'pdo_pgsql',
    'sqlite'     => 'pdo_sqlite',
    'sqlite3'    => 'pdo_sqlite',
];
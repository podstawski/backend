<?php

require_once __DIR__.'/json_config.php';
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;
use google\appengine\api\cloud_storage\CloudStorageTools;



function backend_migrate($config_file,$classpath,$ver=null)
{
    $config=json_config($config_file);

    spl_autoload_register(array('Doctrine', 'autoload'));
    
    try {
        
        $dsn=explode(';dbname=',$config['db.dsn']);
        $dbname=$dsn[1];
        $user=$config['db.user'];
        $pass=$config['db.pass'];
        if (isset($_SERVER['SERVER_SOFTWARE']) && strstr(strtolower($_SERVER['SERVER_SOFTWARE']),'engine'))
        {
            $user='root';
            $pass=null;
        }
        
        $db=new PDO($dsn[0],$user,$pass);
        $conn = Doctrine_Manager::connection($db);
        $dbtable=$conn->fetchOne("SELECT schema_name FROM information_schema.schemata WHERE schema_name='".$dbname."'");
        if (!$dbtable) {
            
            $sql="CREATE DATABASE `".$dbname."` CHARACTER SET utf8;
                    CREATE USER '".$config['db.user']."'@'localhost' IDENTIFIED BY '".$config['db.pass']."';
                    GRANT ALL PRIVILEGES ON ".$dbname.".* TO '".$config['db.user']."'@'%' WITH GRANT OPTION;
            ";
            
            $db->exec($sql);
        }
        $conn->close();
        
        $db = new PDO($config['db.dsn'],$config['db.user'],$config['db.pass']);
        $conn = Doctrine_Manager::connection($db);
        
        
        
        
        
        $migration = new Doctrine_Migration($classpath, $conn);
        $migration->setTableName('doctrine_migration_version');
    
    
        if (!is_null($ver)) $version = 0+$ver;
        else {
            $classesKeys = array_keys($migration->getMigrationClasses());
            $version = 0+array_pop($classesKeys);
        }
        
       
        if ($migration->getCurrentVersion() == $version) {
            echo 'Database at version ' . $version . PHP_EOL;
        } else {
            $migration->migrate($version);
            
            echo 'Migrated succesfully to version ' . $migration->getCurrentVersion() . PHP_EOL;
        }
       
         
        $conn->close();
        
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

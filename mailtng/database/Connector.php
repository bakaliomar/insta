<?php namespace ma\mailtng\database
{
    if (!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework       MailTng Framework
     * @version         1.1
     * @author          MailTng Team
     * @copyright       Copyright (c) 2015 - 2016.	
     * @license		
     * @link	
     */
    use ma\mailtng\core\Base as Base;
    use ma\mailtng\exceptions\types\ArgumentException as ArgumentException;
    use ma\mailtng\exceptions\types\SQLException as SQLException;
    use \ma\mailtng\exceptions\types\DatabaseException as DatabaseException;
    /**
     * @name            Connector.class 
     * @description     The main connector class that all upcoming connectors have to extends from it.
     * @package		ma\mailtng\database
     * @category        Database Class
     * @author		MailTng Team			
     */
    class Connector extends Base
    {       
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_defaultDatabase;
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_currentDatabase;
        
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_availableServices;
        
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_databasesTypes = array("sqlite2","sqlite3","sqlsrv","mssql","mysql","pg","ibm","dblib","odbc","oracle","ifmx","fbd");

        /** 
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_services;
        
	/** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_lastErrorMessage = "";

        /** 
         * @readwrite
         * @access protected 
         * @var integer
         */
        protected $_lastInsertedId = 0;
        
        /** 
         * @readwrite
         * @access protected 
         * @var integer
         */
        protected $_affectedRowsCount = 0;
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_charset = "utf8";

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_engine = "InnoDB";

        /**
         * @name _isValidService
         * @description checks if the service is a connected and it's a valid instance service
         * @access protected
         * @return boolean
         */
        protected function _isValidService($database = null)
        {
            $database = $database == null || $database == '' ? $this->_currentDatabase : $database; 
            $service = count($this->_services) && key_exists($database,$this->_services) ? $this->_services[$database] : null;

            if ($service!= null && $service instanceof \PDO) 
            {
                return true;
            }
            return false;
        }

        /**
         * @name connect
         * @description connects to a database
         * @access public
         * @return Connector
         * @throws DatabaseException
         */
        public function connectAllDatabases()
        {
            if(count($this->_availableServices))
            {
                foreach ($this->_availableServices as $key => $service) 
                {
                    $this->connect($key,$service);
                }
            }

            return $this;
        }

        /**
         * @name connect
         * @description connects to a database
         * @access public
         * @return Connector
         * @throws DatabaseException
         */
        public function connect($key,$service)
        {
            if(isset($key) && count($service))
            {
                if (!$this->_isValidService($key) && !$this->isConnected($key)) 
                {
                    if(in_array($service['type'], $this->_databasesTypes))
                    {
                        try
                        {
                            switch($service['type'])
                            {
                                case "mssql":
                                {
                                    $this->_services[$key] = new \PDO("mssql:host=". $service['host'] .";dbname=". $service['dbname'] , $service['user'], $service['password']);
                                    break;
                                }      
                                case "sqlsrv":
                                {
                                    $this->_services[$key] = new \PDO("sqlsrv:server=". $service['host'] .";database=". $service['dbname'] , $service['user'], $service['password']);
                                    break;
                                }     
                                case "ibm": 
                                {
                                    $this->_services[$key] =new \PDO("ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=". $service['dbname'] ."; HOSTNAME=". $service['host'] .";PORT=". $service['port'] .";PROTOCOL=TCPIP;", $service['user'], $service['password']);
                                    break;
                                }                           
                                case "dblib": 
                                {
                                    $this->_services[$key] = new \PDO("dblib:host=". $service['host'] .":". $service['port'] .";dbname=". $service['dbname'] ,$service['user'],$service['password']);
                                    break;
                                }

                                case "odbc":
                                {
                                    $this->_services[$key] = new \PDO("odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=". $service['host'] .";Uid=".$service['user']);
                                    break;
                                }           
                                case "oracle":
                                {
                                    $this->_services[$key] = new \PDO("OCI:dbname=". $service['dbname'] .";charset=UTF-8", $service['user'], $service['password']);
                                    break;
                                }      
                                case "ifmx":
                                {
                                    $this->_services[$key] = new \PDO("informix:DSN=InformixDB", $service['user'], $service['password']);
                                    break;
                                }      
                                case "fbd":
                                {
                                    $this->_services[$key] = new \PDO("firebird:dbname=". $service['host'] .":". $service['dbname'] , $service['user'], $service['password']);
                                    break;
                                }      
                                case "mysql":
                                {
                                    if(is_numeric($service['port']))
                                    {
                                        $this->_services[$key] = new \PDO("mysql:host=". $service['host'] .";port=". $service['port'] .";dbname=". $service['dbname'] , $service['user'], $service['password']);
                                    }
                                    else
                                    {
                                        $this->_services[$key] = new \PDO("mysql:host=". $service['host'] .";dbname=". $service['dbname'] , $service['user'], $service['password']);
                                    }
                                    break;
                                }                     
                                case "sqlite2":
                                {
                                    $this->_services[$key] = new \PDO("sqlite:". $service['host']);
                                    break;
                                }       
                                case "sqlite3":
                                {
                                    $this->_services[$key] = new \PDO("sqlite::memory");
                                    break;
                                }        
                                case "pg":
                                {
                                    if(is_numeric($service['port']))
                                    {
                                        $this->_services[$key] = new \PDO("pgsql:dbname=". $service['dbname'] .";port=". $service['port'] .";host=". $service['host'] , $service['user'], $service['password']);
                                    }
                                    else
                                    {
                                        $this->_services[$key] = new \PDO("pgsql:dbname=". $service['dbname'] .";host=". $service['host'] , $service['user'], $service['password']);
                                    }
                                    break;
                                }
                                default:
                                {
                                    $this->_services[$key] = null;
                                    break;
                                }          
                            }


                            $this->_services[$key]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                        }
                        catch(\PDOException $e)
                        {
                            $this->_lastErrorMessage = $e->getMessage();
                            throw new DatabaseException($this->_lastErrorMessage,500,$e);
                        }
                    }
                    else
                    {
                        $this->_lastErrorMessage = "Error in params or database not supported";
                        throw new ArgumentException($this->_lastErrorMessage);
                    }
                }
            }

            return $this;
        }
        
        /**
         * @name disconnectAllDatabases
         * @description disconnects from database
         * @access public
         * @return Connector
         * @throws DatabaseException
         */
        public function disconnectAllDatabases()
        {
            if (count($this->_services))
            {
                foreach ($this->_services as $service) 
                {
                    unset($service);
                }
                
                $this->_services = array();
            }
            
            return $this;
        }
        
        /**
         * @name disconnect
         * @description disconnects from a database
         * @access public
         * @return Connector
         * @throws DatabaseException
         */
        public function disconnect($key)
        {
            if (count($this->_services) && key_exists($key,$this->_services))
            {
                unset($this->_services[$key]);
            }
            
            return $this;
        }

        /**
         * @name isConnected
         * @description returns if it's connected or not
         * @access public
         * @return boolean
         */
        public function isConnected($database = null)
        {
            if ($this->_isValidService($database))
            {
                $database = $database == null || $database == '' ? $this->_currentDatabase : $database; 
                $service = $this->_services[$database];
                
                try 
                {
                    return (bool) $service->query('SELECT 1+1');
                } 
                catch (\PDOException $e) 
                {
                    Logger::error($e);
                }
            }
            
            return false;
        }

        /**
         * @name getProperties
         * @description returns the PDO connection properties
         * @access public
         * @return array
         */
        public function getProperties($database = null)
        {
            if ($this->_isValidService())
            {
                $database = $database == null || $database == '' ? $this->_currentDatabase : $database; 
                $service = $this->_services[$database];
                
                return array(
                    'driver' => $service->getAttribute(\PDO::ATTR_DRIVER_NAME),
                    'server' => $service->getAttribute(\PDO::ATTR_SERVER_VERSION),
                    'status' => $service->getAttribute(\PDO::ATTR_CONNECTION_STATUS),
                    'client' => $service->getAttribute(\PDO::ATTR_CLIENT_VERSION),
                    'information' => $service->getAttribute(\PDO::ATTR_SERVER_INFO)
                );
            }
            
            return array();
	}
                         
        /**
         * @name query
         * @description returns a corresponding query instance
         * @access public
         * @return mixed
         */
        public function query()
        {
            $type = $this->_availableServices[$this->_currentDatabase]['type'];
            
            switch ($type)
            {
                case "mysql": 
                {
                    return new drivers\queries\MysqlQuery(array("connector" => $this));
                }
                case "pg": 
                {
                    return new drivers\queries\PgsqlQuery(array("connector" => $this));
                }
                default: 
                {
                    return new drivers\queries\MysqlQuery(array("connector" => $this));
                }
            }
        }

        /**
         * @name executeQuery
         * @description executes the provided SQL statement
         * @access public
         * @param string $sql
         * @param boolean $fetchRows
         * @param integer $fetchType
         * @param boolean $fetchAll
         * @param string $sequence
         * @return mixed 
         * @throws DatabaseException
         */
        public function executeQuery($sql,$fetchRows = false,$fetchType = \PDO::FETCH_ASSOC,$fetchAll = true,$sequence = null)
        {
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }
            
            $this->_lastErrorMessage = "";
            
            try
            {
                $statement = $this->_services[$this->_currentDatabase]->query($sql);
                $this->_affectedRowsCount = $statement->rowCount();
                
                if($fetchRows)
                {
                    if($fetchAll)
                    {
                        $result = $statement->fetchAll($fetchType);
                        return $result == FALSE ? NULL : $result ;
                    }
                    else
                    {
                        $result = $statement->fetch($fetchType);
                        return $result == FALSE ? NULL : $result ;
                    }
                }
                
                if ($sequence != null && !empty($sequence))
                {
                    if (is_numeric($this->_services[$this->_currentDatabase]->lastInsertId($sequence)))
                    {
                        $this->_lastInsertedId = $this->_services[$this->_currentDatabase]->lastInsertId($sequence);
                        return $this->_lastInsertedId;
                    } 
                }

                return $this->_affectedRowsCount;
            }
            catch(\PDOException $e)
            {
                $this->_lastErrorMessage = $e->getMessage();
                throw new SQLException($this->_lastErrorMessage,500,$e);
            }
        }

        /**
         * @name executeSecureQuery
         * @description executes the provided SQL statement It prevents and avoids sql injections.
         * @access public
         * @param string $sql        Query string to execute.
         * @param string $params     Are necessary for query execute.
         * @param string $fetchRows  true if you need the result set.
         * @param string $unnamed    Only if your params are annonymous.
         * @param string $delimiter  You can specify another delimiter.
         * @param string $fetchAll   Contains result set.
         * @return mixed 
         * @throws DatabaseException
         */
        public function executeSecureQuery($sql,$params,$fetchRows = false,$unnamed = false, $delimiter = "|",$fetchType = \PDO::FETCH_ASSOC,$fetchAll = true)
        {
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }
            
            $this->_lastErrorMessage = "";
            try
            {
                $preparedStatement = $this->_services[$this->_currentDatabase]->prepare($sql);
                
                if(!$unnamed)
                {
                    for($i=0;$i<count($params);$i++)
                    {
                            $paramsSplit = explode($delimiter,$params[$i]);
                            (trim($paramsSplit[2]) == "INT") ? $preparedStatement->bindParam($paramsSplit[0], $paramsSplit[1], PDO::PARAM_INT) 
                                                             : $preparedStatement->bindParam($paramsSplit[0], $paramsSplit[1], PDO::PARAM_STR);
                    }
                    try
                    {
                        $preparedStatement->execute();
                    }
                    catch(\PDOException $e)
                    {
                        $this->_lastErrorMessage = $e->getMessage();
                        throw new DatabaseException($this->_lastErrorMessage,500,$e);
                    }
                }
                else
                {
                    try
                    {
                        $preparedStatement->execute($params);
                        $this->_affectedRowsCount = $preparedStatement->rowCount();
                    } 
                    catch(\PDOException $e)
                    {
                        $this->_lastErrorMessage = $e->getMessage();
                        throw new SQLException($this->_lastErrorMessage,500,$e);
                    }
                }
                if ($fetchRows)
                {
                    if($fetchAll)
                    {
                        return $preparedStatement->fetchAll($fetchType);
                    }
                    else
                    {
                        return $preparedStatement->fetch($fetchType);
                    } 
                }       
                if (is_numeric($this->_services[$this->_currentDatabase]->lastInsertId()))
                {
                    $this->_lastInsertedId = $this->_services[$this->_currentDatabase]->lastInsertId();
                    return $this->_lastInsertedId;
                }   
                return true;
            }
            catch(\PDOException $e)
            {
                $this->_lastErrorMessage = $e->getMessage();
                throw new SQLException($this->_lastErrorMessage,500,$e);
            }
        }

        /**
         * @name getDrivers
         * @description returns PDO available drivers
         * @access public
         * @return mixed
         */
        public function getDrivers()
        {
            return \PDO::getAvailableDrivers(); 
	}

        /**
         * @name transaction
         * @description executes the transactional operations.
         * @access public
         * @param string $type Shortcut for transaction to execute. i.e: B = begin, C = commit & R = rollback.
         * @return mixed
         */
        public function transaction($type) 
        {
            # check if we're still connected
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }
            
            $this->_lastErrorMessage = "";
            try 
            {
                if ($type == "B")
                {
                    $this->_services[$this->_currentDatabase]->beginTransaction();
                }  
                elseif ($type == "C")
                {
                    $$this->_services[$this->_currentDatabase]->commit();
                }
                elseif ($type == "R")
                {
                    $this->_services[$this->_currentDatabase]->rollBack();
                }
                else 
                {
                    $this->_lastErrorMessage = "The passed param is wrong! just allow [B=begin, C=commit or R=rollback]";
                    throw new ArgumentException($this->_lastErrorMessage);
                }
            } 
            catch (\PDOException $e) 
            {
                $this->_lastErrorMessage = $e->getMessage();
                throw new DatabaseException($this->_lastErrorMessage,500,$e);
            } 
        }

        /**
         * @name getAvailableTables
         * @description retrieves all tables in the database
         * @access public
         * @param string $schema must specify schema for get tables.
         * @return mixed
         */
        public function getAvailableTables($schema = null)
        {  
            $result = array();
            
            # check if we're still connected
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }

            $sql = "";
            $this->_lastErrorMessage = "";
            $type = $this->_availableServices[$this->_currentDatabase]['type'];
            $database = $this->_availableServices[$this->_currentDatabase]['dbname'];
            
            if(in_array($type,array("sqlsrv","mssql","ibm","dblib","odbc","sqlite2","sqlite3")))
            {
                $sql = "SELECT name FROM sysobjects WHERE xtype='U';";
            }
            elseif($type == "oracle")
            {
                $sql = "SELECT table_name AS name FROM cat;";
            }
            elseif($type == "ifmx" || $type == "fbd")
            {
                $sql = 'SELECT RDB$RELATION_NAME AS name FROM RDB$RELATIONS WHERE RDB$SYSTEM_FLAG = 0 AND RDB$VIEW_BLR IS NULL ORDER BY RDB$RELATION_NAME;';
            }
            elseif($type == "mysql")
            {
                if($database != "") $complete = " FROM {$database}";
                $sql = "SHOW tables ".$complete.";";
            }
            elseif($type == "pg")
            {
                if($schema != null && $schema != '')
                {
                    $sql = "SELECT relname AS name FROM pg_stat_user_tables WHERE schemaname = '$schema' ORDER BY relid;";
                }
                else
                {
                    $sql = "SELECT schemaname || '.' || relname AS name FROM pg_stat_user_tables ORDER BY relid;";
                }
            }

            try
            {            
                $tables = $this->executeQuery($sql,true);
                
                if(count($tables))
                {
                    foreach ($tables as $table) 
                    {
                        if(count($table))
                        {
                            $result[] = $table['name'];
                        }
                    }
                }
            }
            catch(\Exception $e)
            {
                $this->_lastErrorMessage = $e->getMessage();
                throw new SQLException($this->_lastErrorMessage,500,$e);
            }
            
            return $result;
	}

        /**
         * @name getAvailableDatabases
         * @description retrieves all databases in the scheme.
         * @access public
         * @return mixed
         */
        public function getAvailableDatabases()
        {
            # check if we're still connected
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }

            $sql = "";
            $this->_lastErrorMessage = "";
            $type = $this->_availableServices[$this->_currentDatabase]['type'];
            
            if(in_array($type,array("sqlsrv","mssql","ibm","dblib","odbc","sqlite2","sqlite3")))
            {
                $sql = "SELECT name FROM sys.Databases;";
            }
            elseif($type == "oracle")
            {
                $sql = 'SELECT * FROM v$database;';
            }
            elseif($type == "ifmx" || $type == "fbd")
            {
                $sql = '';
            }
            elseif($type == "mysql")
            {
                $sql = "SHOW DATABASES;";
            }
            elseif($type == "pg")
            {
                $sql = "SELECT datname AS name FROM pg_database;";
            }

            try
            {
                return $this->executeQuery($sql,true);
            }
            catch(\Exception $e)
            {
                $this->_lastErrorMessage = $e->getMessage();
                throw new SQLException($this->_lastErrorMessage,500,$e);
            }
	}
        
        /**
         * @name checkIfTableExists
         * @description checks if table exists in the database
         * @access public
         * @param string $schema must specify schema .
         * @param string $table must specify table .
         * @return mixed
         */
        public function checkIfTableExists($schema,$table)
        {  
            $result = 'false';
            
            # check if we're still connected
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }

            $sql = "";
            $this->_lastErrorMessage = "";
            $type = $this->_availableServices[$this->_currentDatabase]['type'];
            
            if(in_array($type,array("sqlsrv","mssql","ibm","dblib","odbc","sqlite2","sqlite3")))
            {
                $sql = "";
            }
            elseif($type == "oracle")
            {
                $sql = '';
            }
            elseif($type == "ifmx" || $type == "fbd")
            {
                $sql = '';
            }
            elseif($type == "mysql")
            {
                $sql = "";
            }
            elseif($type == "pg")
            {
                $sql = "SELECT EXISTS (SELECT 1 FROM pg_catalog.pg_class c JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace WHERE  n.nspname = '$schema' AND c.relname = '$table' AND c.relkind = 'r');";
            }

            try
            {            
                $checkResults = $this->executeQuery($sql,true);
                
                if(count($checkResults))
                {
                    if(key_exists('exists',$checkResults[0]))
                    {
                        if($checkResults[0]['exists'] == '1')
                        {
                            $result = 'true';
                        }
                    }
                }

            }
            catch(\Exception $e)
            {
                $this->_lastErrorMessage = $e->getMessage();
                throw new SQLException($this->_lastErrorMessage,500,$e);
            }
            
            return $result;
	}
        
        /**
         * @name dropTable
         * @description cdrops table from database
         * @access public
         * @param string $schema must specify schema .
         * @param string $table must specify table .
         * @return mixed
         */
        public function dropTable($schema,$table)
        {  
            $result = 0;
            
            # check if we're still connected
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }

            $sql = "";
            $this->_lastErrorMessage = "";
            $type = $this->_availableServices[$this->_currentDatabase]['type'];
            
            if(in_array($type,array("sqlsrv","mssql","ibm","dblib","odbc","sqlite2","sqlite3")))
            {
                $sql = "";
            }
            elseif($type == "oracle")
            {
                $sql = "DROP TABLE $schema.$table;";
            }
            elseif($type == "ifmx" || $type == "fbd")
            {
                $sql = '';
            }
            elseif($type == "mysql")
            {
                $sql = "DROP TABLE $table;";
            }
            elseif($type == "pg")
            {
                $sql = "DROP TABLE $schema.$table;";
                $sql2 = "DROP SEQUENCE $schema.seq_id_$table;";
            }

            try
            {            
                $result = $this->executeQuery($sql);
                
                if(isset($sql2) && strlen($sql2) > 0)
                {
                    $result = $this->executeQuery($sql2);
                }
            }
            catch(\Exception $e)
            {
                $this->_lastErrorMessage = $e->getMessage();
                throw new SQLException($this->_lastErrorMessage,500,$e);
            }
            
            return $result;
	}

        /**
         * @name escape
         * @description escapes the provided value to make it safe for queries
         * @access public
         * @param string $value the string to be escaped
         * @return mixed
         * @throws DatabaseException
         */
        public function escape($value)
        {
            # check if we're still connected
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }
            return $this->_services[$this->_currentDatabase]->quote($value);
        }

        /**
         * @name getLastInsertId
         * @description returns the ID of the last row to be inserted
         * @access public
         * @return integer
         * @throws DatabaseException
         */
        public function getLastInsertId()
        {
            # check if we're still connected
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }
            return $this->_lastInsertedId;
        }

        /**
         * @name getAffectedRows
         * @description returns the number of rows affected by the last SQL query executed
         * @access public
         * @return integer
         * @throws DatabaseException
         */
        public function getAffectedRows()
        {
            # check if we're still connected
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }
            return $this->_affectedRowsCount;
        }

        /**
         * @name getLastError
         * @description returns the last error occured
         * @access public
         * @return mixed
         * @throws DatabaseException
         */
        public function getLastError()
        {
            # check if we're still connected
            if (!$this->_isValidService($this->_currentDatabase))
            {
                $this->_lastErrorMessage = "Connection to database lost";
                throw new DatabaseException($this->_lastErrorMessage);
            }
            return $this->_lastErrorMessage;
        } 
        
        public function __sleep()
        {
            Database::secureDisconnect();
        }

        public function __wakeup()
        {
            Database::secureConnect();
        }
    }
}
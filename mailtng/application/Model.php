<?php namespace ma\mailtng\application
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
    use ma\mailtng\database\Database as Database;
    use ma\mailtng\metadata\Inspector as Inspector;
    use ma\mailtng\database\Connector as Connector;
    use ma\mailtng\exceptions\types\ApplicationException as ApplicationException;
    use ma\mailtng\exceptions\types\DatabaseException as DatabaseException;
    /**
     * @name            Model.class
     * @description     The mother class of all the upcomming models 
     * @package		ma\mailtng\application
     * @category        Core Class
     * @author		MailTng Team			
     */
    class Model extends Base
    {
        /**
         * @readwrite
         * @access protected 
         * @var Connector
         */
        protected $_connector;

        /**
         * @read
         * @access protected 
         * @var array
         */
        protected $_types = array(
            "integer",
            "text",
            "date",
            "decimal",
            "boolean",
            "timestamp"
        );
        
        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_columns;
                
        /**
         * @readwrite
         * @access protected 
         * @var array
         */       
        protected $_primary;

        /**
         * @name __construct
         * @description the class constructor
         * @access public
         * @param array $options
         * @return Model
         */
        public function __construct($options = array())
        {
            # calling super constructor
            parent::__construct($options);

            $this->_connector = Database::getCurrentDatabaseConnector();
                
            # load a database record if the primary key value was provided 
            $this->load();
        }
 
        /**
         * @name load
         * @description loads a record if the primary column’s value has been provided
         * @access public
         * @return
         * @throws DatabaseException
         */
        public function load() 
        {
            $primary = $this->getPrimaryColumn();
            
            if (isset($primary)) 
            {
                if (!empty($this->{$primary["raw"]})) 
                {
                    $schema = $this->getSchema() != null ? $this->getSchema() . "." :  null;
                    $record = $this->_connector->query()->from($schema . $this->getTable())->where("{$primary["name"]} = ?",$this->{$primary["raw"]})->first();
                    
                    if ($record != null) 
                    {
                        $keys = array_keys($record);
                        
                        foreach ($keys as $key) 
                        {
                            if (!empty($record[$key])) 
                            {
                                $this->{"_{$key}"} = $record[$key];
                            }
                        }
                    }
                }
            }
        }
        
        /**
         * @name save
         * @description creates or updates a record base on the primary key 
         * @access public
         * @return integer
         * @throws DatabaseException
         */
        public function save($returningId = true) 
        {
            $primary = $this->getPrimaryColumn();
            $columns = $this->getColumns();
            $schema = $this->getSchema() != null ? $this->getSchema() . "." :  null;
            $query = $this->_connector->query()->from($schema . $this->getTable());
            $data = array();

            # primary column section
            if(isset($primary))
            {
                if (!empty($this->{$primary["raw"]})) 
                {
                    $query->where("{$primary["name"]} = ?", $this->{$primary["raw"]});
                }
                else
                {
                    $data["{$primary["name"]}"] = "no_quote : nextval('{$schema}seq_{$primary["name"]}_{$this->getTable()}')";
                }
            }

            # other columns 
            foreach ($columns as $key => $column) 
            {
                if ($column != $primary && $column) 
                {
                    $data[$key] = $this->{$column["raw"]};
                }
            }

            $result = $query->save($data,$schema,$returningId);
            
            if ($result > 0) 
            {
                $this->{$primary["raw"]} = $result;
            }

            return $result;
        }
        
        /**
         * @name delete
         * @description creates a query object, only if the primary key property value is not empty, and executes the query’s delete() method.
         * @access public
         * @return integer
         */
        public function delete()
        {
            $primary = $this->getPrimaryColumn();
            $raw = $primary["raw"];
            $name = $primary["name"];
            if (!empty($this->$raw))
            {
                $schema = $this->getSchema() != null ? $this->getSchema() . "." :  null;
                $result = $this->_connector->query()->from($schema . $this->getTable())->where("{$name} = ?",$this->$raw)->delete();
                return $result;
            }
        }
 
        /**
         * @name deleteAll
         * @description creates a query object and executes the query’s delete() method based on the where clause Note: it will delete all the records if where array is empty
         * @param array $where
         * @access static
         * @return integer
         */
        public static function deleteAll($where = array()) 
        {
            $instance = new static();
            $schema = $instance->getSchema() != null ? $instance->getSchema() . "." :  null;
            $query = $instance->getConnector()->query()->from($schema . $instance->getTable());
            
            if(count($where) > 1)
            {
                $query->where($where[0],$where[1]);
            }
            
            $result = $query->delete();
            return $result;
        }
 
        /**
         * @name all
         * @description public static wrapper of the protected method _all
         * @access static
         * @param array $array
         * @param array $where
         * @param array $fields
         * @param array $order
         * @param string $direction
         * @param integer $limit
         * @param integer $page
         * @return mixed
         */
        public static function all($array = false, $where = array(), $fields = array("*"), $order = null, $direction = null, $limit = null, $page = null) 
        {
            $model = new static();
            return $model->_all($array, $where, $fields, $order, $direction, $limit, $page);
        }

        /**
         * @name _all
         * @description creates a query, taking into account the various filters and ﬂags, to return all matching records.
         * @access protected
         * @param array $array
         * @param array $where
         * @param array $fields
         * @param array $order
         * @param string $direction
         * @param integer $limit
         * @param integer $page
         * @return mixed
         */
        protected function _all($array = false, $where = array(), $fields = array("*"), $order = null, $direction = null, $limit = null, $page = null) 
        {
            $schema = $this->getSchema() != null ? $this->getSchema() . "." :  null;
            $query = $this->_connector->query()->from($schema . $this->getTable(), $fields);
            
            if(count($where) > 1)
            {
                $query->where($where[0],$where[1]);
            }
            
            if ($order != null) 
            {
                $query->order($order, $direction);
            }
            
            if ($limit != null) 
            {
                $query->limit($limit, $page);
            }
            
            # retrieving the data
            $result = $query->all();
            $rows = array();
            
            if($array)
            {
                $rows = $result;
            }
            else
            {
                $class = get_class($this);
                foreach ($result as $row) 
                {
                    $rows[] = new $class($row);
                }
            }
            return $rows;
        }
        
        /**
         * @name first
         * @description a public static wrapper of the protected method _first
         * @access static
         * @param array $array
         * @param array $where
         * @param array $fields
         * @param array $order
         * @param string $direction
         * @return mixed
         */
        public static function first($array = false, $where = array(), $fields = array("*"),$order = null, $direction = null)
        {
            $model = new static();
            return $model->_first($array, $where, $fields, $order, $direction);
        }

        /**
         * @name _first
         * @description returns the first matched record
         * @access protected
         * @param array $array
         * @param array $where
         * @param array $fields
         * @param array $order
         * @param string $direction
         * @return mixed
         */
        protected function _first($array = false, $where = array(), $fields = array("*"), $order = null, $direction = null) 
        {
            $result = null; 
            
            $schema = $this->getSchema() != null ? $this->getSchema() . "." :  null;
            $query = $this->_connector->query()->from($schema . $this->getTable(), $fields);
            
            if(count($where) > 1)
            {
                $query->where($where[0],$where[1]);
            }
            
            if ($order != null)  
            {
                $query->order($order, $direction);
            }
            
            # retrieving the data
            $result = $query->first();
            
            if(!$array)
            {
                $class = get_class($this);
                if ($result) 
                {
                    $result = new $class($query->first());
                }
            }
            
            return $result;
        }
        
        /**
         * @name count
         * @description a public static wrapper of the protected method _count
         * @access static
         * @param array $where
         * @return integer
         */
        public static function count($where = array())
        {
            $model = new static();
            return $model->_count($where);
        }
 
        /**
         * @name _count
         * @description returns a count of the matched records. 
         * @access protected
         * @param array $where
         * @return integer
         */
        protected function _count($where = array()) 
        {
            $schema = $this->getSchema() != null ? $this->getSchema() . "." :  null;
            $query = $this->_connector->query()->from($schema . $this->getTable());
            
            if(count($where) > 1)
            {
                $query->where($where[0],$where[1]);
            }
            
            $result = $query->count();
            return $result;
        }
        
        /**
         * @name getColumns
         * @description gets all the columns that are defined in the model and have @column flag 
         * @access public
         * @return array
         * @throws ApplicationException
         */
        public function getColumns() 
        {
            if (empty($this->_columns)) 
            {
                $primaries = 0;
                $columns = array();
                $class = get_class($this);
                $types = $this->types;
                $properties = Inspector::getClassProperties($class);
                $first = function($array, $key) 
                        {
                            if (!empty($array[$key]) && sizeof($array[$key]) == 1) 
                            {
                                return $array[$key][0];
                            }
                            return null;
                        };
                foreach ($properties as $property) 
                {
                    $propertyMeta = Inspector::getPropertyMetaData($class,$property);
                    if (!empty($propertyMeta["@column"])) 
                    {
                        $name = preg_replace("#^_#", "", $property);
                        $primary = !empty($propertyMeta["@primary"]);
                        $autoIncrement = !empty($propertyMeta["@autoincrement"]);
                        $type = $first($propertyMeta, "@type");
                        $length = $first($propertyMeta,"@length");
                        $nullable = $first($propertyMeta, "@nullable");
                        $nullable = !empty($nullable) ? $nullable : false;
                        $readwrite = !empty($propertyMeta["@readwrite"]);
                        $read = !empty($propertyMeta["@read"]) || $readwrite;
                        $write = !empty($propertyMeta["@write"]) || $readwrite;
                        $label = $first($propertyMeta, "@label");
                        if (!in_array($type, $types)) 
                        {
                            throw new ApplicationException("{$type} is not a valid type");
                        }
                        if ($primary) 
                        {
                            $primaries++;
                        }
                        $columns[$name] = array(
                            "raw" => $property,
                            "name" => $name,
                            "primary" => $primary,
                            "autoincrement" => $autoIncrement,
                            "type" => $type,
                            "nullable" => $nullable,
                            "length" => $length,
                            "read" => $read,
                            "write" => $write,
                            "label" => $label
                        );
                    }
                }
                if ($primaries !== 1) 
                {
                    throw new ApplicationException("{$class} must have exactly one @primary column");
                }
                $this->_columns = $columns;
            }
            return $this->_columns;
        }
        
        /**
         * @name getColumn
         * @description gets a column by its name
         * @access public
         * @return array
         */
        public function getColumn($name) 
        {
            if (!empty($this->_columns[$name])) 
            {
                return $this->_columns[$name];
            }
            return null;
        }

        /**
         * @name getPrimaryColumn
         * @description gets the primary column
         * @access public
         * @return array
         */
        public function getPrimaryColumn() 
        {
            if (!isset($this->_primary)) 
            {
                $primary = NULL;
                foreach ($this->columns as $column) 
                {
                    if ($column["primary"]) 
                    {
                        $primary = $column;
                        break;
                    }
                }
                $this->_primary = $primary;
            }
            return $this->_primary;
        }

        /**
         * @name synchronizeWithDatabase
         * @description converts the class/properties into a valid SQL query and ultimately into a physical database table.
         * @access static
         * @param string $tableName
         * @param string $schema
         * @return integer
         * @throws ArgumentException
         * @throws SQLException
         */
        public static function synchronizeWithDatabase($tableName = null,$schema = null) 
        {
            $calledClass = get_called_class();
            $result = 0;
            
            if(isset($calledClass) && class_exists($calledClass))
            {
                $model = new $calledClass();
                $result = $model->_connector->query()->createTable($model, $tableName, $schema);
            } 
            return $result;
        }

        /**
         * @name getModelColumns
         * @description gets all the columns of this model
         * @access static
         * @return array
         */
        public static function getModelColumns()
        {
            $calledClass = get_called_class();
            
            $columns = array();
            
            if(isset($calledClass) && class_exists($calledClass))
            {
                $model = new $calledClass();
                
                if(isset($model))
                {
                    # get all the columns names 
                    $columns = $model->getColumns(); 
                }
            }
            
            return $columns;
        }
    }
}
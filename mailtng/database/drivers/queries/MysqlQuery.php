<?php namespace ma\mailtng\database\drivers\queries
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
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\exceptions\types\DatabaseException as DatabaseException;
    /**
     * @name            MysqlQuery.class 
     * @description     The mysql queries class
     * @package		ma\mailtng\database\drivers\queries
     * @category        Database Class
     * @author		MailTng Team			
     */
    class MysqlQuery extends Base
    {   
        /** 
         * @readwrite
         * @access protected 
         * @var \ma\mailtng\database\Connector
         */
        protected $_connector;
        
        /** 
         * @read
         * @access protected 
         * @var array
         */
        protected $_from;
    
        /** 
         * @read
         * @access protected 
         * @var array
         */
        protected $_fields;
      
        /** 
         * @read
         * @access protected 
         * @var array
         */
        protected $_limit;
     
        /** 
         * @read
         * @access protected 
         * @var array
         */
        protected $_offset;
      
        /** 
         * @read
         * @access protected 
         * @var array
         */
        protected $_order;
       
        /** 
         * @read
         * @access protected 
         * @var array
         */
        protected $_direction;
        
        /** 
         * @read
         * @access protected 
         * @var array
         */
        protected $_join = array();

        /** 
         * @read
         * @access protected 
         * @var array
         */
        protected $_where = array();

        /** 
         * @read
         * @access static 
         * @var array
         */
        public static $LEFT_JOIN  = 'LEFT JOIN';
        
        /** 
         * @read
         * @access static 
         * @var array
         */
        public static $RIGHT_JOIN = 'RIGHT JOIN';
        
        /** 
         * @read
         * @access static 
         * @var array
         */
        public static $INNER_JOIN = 'INNER JOIN';
        
        /** 
         * @read
         * @access static 
         * @var array
         */
        public static $FULL_OUTER_JOIN = 'FULL OUTER JOIN';

        /**
         * @name from
         * @description the from part of the query 
         * @access public
         * @param string $from the table name
         * @param array $fields columns to select ( * by default )
         * @return MysqlQuery
         * @throws DatabaseException
         */
        public function from($from, $fields = array("*")) 
        {
            if (empty($from)) 
            {
                throw new DatabaseException("Invalid argument");
            }
            $this->_from = $from;
            if ($fields) 
            {
                $this->_fields[$from] = $fields;
            }
            return $this;
        }
         
        /**
         * @name join
         * @description the join part of the query 
         * @access public
         * @param string $join the table name 
         * @param string $on the condition
         * @param array $fields the fileds to select
         * @param string $type the join type
         * @return MysqlQuery
         * @throws DatabaseException
         */
        public function join($join,$on,$fields = array(),$type = 'LEFT JOIN') 
        {
            if (empty($join)) 
            {
                throw new DatabaseException("Invalid argument");
            }
            
            if (empty($on)) 
            {
                throw new DatabaseException("Invalid argument");
            }
            
            $this->_fields += array($join => $fields);
            $this->_join[] = "{$type} {$join} ON {$on}";
            return $this;
        }
        
        /**
         * @name limit
         * @description the limit part of the query 
         * @access public
         * @param  integer $limit
         * @param  integer $page
         * @return MysqlQuery
         * @throws DatabaseException
         */
        public function limit($limit, $page = 1) 
        {
            if (empty($limit)) 
            {
                throw new DatabaseException("Invalid argument");
            }
            $this->_limit = $limit;
            $this->_offset = $limit * ($page - 1);
            return $this;
        }
        
        /**
         * @name order
         * @description the order part of the query 
         * @access public
         * @param  string $order
         * @param  string $direction
         * @return MysqlQuery
         * @throws DatabaseException
         */
        public function order($order, $direction = "asc") 
        {
            if (empty($order)) 
            {
                throw new DatabaseException("Invalid argument");
            }
            $this->_order = $order;
            $this->_direction = $direction;
            return $this;
        }

        /**
         * @name order
         * @description the where part of the query 
         * @access public
         * @param  string $condition
         * @param  array $parameters
         * @return MysqlQuery
         * @throws DatabaseException
         */
        public function where($condition,$parameters) 
        {
            if (strlen($condition) < 1) 
            {
                throw new DatabaseException("Invalid argument");
            }
            $arguments = array();
            $i = 0;
            $arguments[$i] = preg_replace("#\?#", "%s",$condition);
            
            # if the parameters is just a string 
            if(!is_array($parameters))
            {
               $arguments[1] = (Strings::startsWith($parameters,'no_quote{')) ? str_replace(array('no_quote{','}'),array('',''),$parameters) : $this->_quote($parameters);
            }
            else
            {
                foreach ($parameters as $parameter) 
                {
                    $arguments[++$i] = (Strings::startsWith($parameter,'no_quote{')) ? str_replace(array('no_quote{','}'),array('',''),$parameter) : $this->_quote($parameter);   
                }
            }
            
            $this->_where[] = call_user_func_array("sprintf",$arguments);
            return $this;
        }
            
        /**
         * @name all
         * @description gets all the rows retrieved by the SELECT sql statement
         * @access public
         * @return MysqlQuery
         * @throws DatabaseException
         */
        public function all() 
        {
            return $this->_connector->executeQuery($this->_buildSelect(),true,\PDO::FETCH_ASSOC,true);
        }
               
        /**
         * @name save
         * @description saves (insert or updates) data in the database
         * @access public
         * @param array $data the data to be inserted / updated with  
         * @param boolean $returnId
         * @return integer
         * @throws DatabaseException
         */
        public function save($data,$returnId = true) 
        {
            $isInsert = sizeof($this->_where) == 0;
            if ($isInsert)
            {
                $sql = $this->_buildInsert($data);
            }
            else
            {
                $sql = $this->_buildUpdate($data);
            }
            
            $this->_connector->executeQuery($sql);
            
            if ($isInsert && $returnId)
            {
                return $this->_connector->getLastInsertId();
            }
            else
            {
                return $this->_connector->getAffectedRows();
            }
            return 0;
        }
     
        /**
         * @name delete
         * @description deletes data from the database
         * @access public 
         * @return integer
         * @throws DatabaseException
         */
        public function delete() 
        {
            $this->_connector->executeQuery($this->_buildDelete());
            return $this->_connector->getAffectedRows();
        }
          
        /**
         * @name first
         * @description gets the first row retrieved by the SELECT sql statement
         * @access public 
         * @return array
         * @throws DatabaseException
         */
        public function first() 
        {
            $limit = $this->_limit;
            $offset = $this->_offset;
            $this->limit(1);
            $first = $this->_connector->executeQuery($this->_buildSelect(),true,\PDO::FETCH_ASSOC,false);
            if ($limit) 
            {
                $this->_limit = $limit;
            }
            if ($offset) 
            {
                $this->_offset = $offset;
            }
            return $first;
        }
 
        /**
         * @name count
         * @description retrieves the row count of query result
         * @access public 
         * @return mixed
         * @throws DatabaseException
         */
        public function count() 
        {
            $limit = $this->limit;
            $offset = $this->offset;
            $fields = $this->fields;
            $this->_fields = array($this->from => array("COUNT(1)" => "rows"));
            $this->limit(1);
            $row = $this->first();
            $this->_fields = $fields;
            if ($fields) 
            {
                $this->_fields = $fields;
            }
            
            if ($limit) 
            {
                $this->_limit = $limit;
            }
            
            if ($offset) 
            {
                $this->_offset = $offset;
            }
            return $row["rows"];
        }
        
        /**
         * @name createTable
         * @description creates a new table
         * @access public 
         * @return mixed
         * @throws DatabaseException
         */
        public function createTable($model,$tableName = null) 
        {
            if(isset($model))
            {
                $lines = array();
                $columns = $model->getColumns();

                # join the schema to the table name
                $table = $tableName != null ? $tableName : $model->getTable();

                $template = "CREATE TABLE %s (\n%s\n);";

                foreach ($columns as $column) 
                {
                    $name = $column["name"];
                    $type = $column["type"];
                    $length = $column["length"];
                    $nullable = trim($column["nullable"]) == "true" ? " DEFAULT NULL " : " NOT NULL ";

                    switch ($type) 
                    {
                        case "integer": 
                        {
                            $line = "{$name} integer {$nullable}";
                            
                            if ($column["primary"]) 
                            {
                                $line .= " PRIMARY KEY ";
                            }

                            if ($column["autoincrement"]) 
                            {
                                $line .= " AUTO_INCREMENT ";
                            }
                            
                            $lines[] = $line;
                            break;
                        }
                        case "decimal": 
                        {
                            $lines[] = "{$name} decimal {$nullable}";
                            break;
                        }

                        case "text": 
                        {
                            if ($length !== null && $length <= 255) 
                            {
                                $lines[] = "{$name} varchar({$length}) {$nullable}";
                            } 
                            else 
                            {
                                $lines[] = "{$name} text {$nullable}";
                            }
                            break;
                        }
                        case "boolean": 
                        {
                            $lines[] = "{$name} boolean ";
                            break;
                        }
                        case "timestamp": 
                        {
                            $lines[] = "{$name} timestamp {$nullable}";
                            break;
                        }
                        case "date": 
                        {
                            $lines[] = "{$name} date {$nullable}";
                            break;
                        }
                    }
                }

                # create the table query
                $tableQuery = sprintf($template,$table, join(",\n", $lines), $this->_connector->getEngine(), $this->_connector->getCharset());

                # connect to the database
                if(!$this->_connector->isConnected()) $this->_connector->connect();

                # droping the table if exists
                $this->_connector->executeQuery("DROP TABLE IF EXISTS {$table};");  

                # creating a new table
                $result = $this->_connector->executeQuery($tableQuery);

                return $result;
            }
        }
           
        /**
         * @name _quote
         * @description wraps the $value passed to it in the applicable quotation marks, so that it can be added to the applicable query in a syntactically 
         * @access protected 
         * @param string $value
         * @return string
         */
        protected function _quote($value) 
        {
            if (is_string($value)) 
            {
                $escaped = $this->_connector->escape($value);
                return "{$escaped}";
            }
            
            if (is_array($value)) 
            {
                $buffer = array();
                foreach ($value as $i) 
                {
                    array_push($buffer, $this->_quote($i));
                }
                $buffer = join(", ", $buffer); 
                return "({$buffer})";
            }
            
            if (is_null($value)) 
            {
                return "NULL";
            }
            
            if (is_bool($value)) 
            { 
                return (int) $value;
            }
            return $this->_connector->escape($value);
        }
        
        /**
         * @name _buildSelect
         * @description builds a Mysql compatible SQL query, from the ground up. it declares the template for our SELECT statement.
         * @access protected 
         * @param string $value
         * @return string
         */
        protected function _buildSelect()
        {
            $fields = array();
            $where = $order = $limit = $join = "";
            $template = "SELECT %s FROM %s %s %s %s %s";
            
            foreach ($this->_fields as $_fields)
            {
                foreach ($_fields as $field => $alias)
                {
                    if (is_string($field))
                    {
                        $fields[] = "{$field} AS {$alias}";
                    }
                    else
                    {
                        $fields[] = $alias;
                    }
                }
            }
            
            $fields = join(", ", $fields);
            
            # join case
            if (!empty($this->_join))
            {
                $join = join(" ", $this->_join);
            }
            
            # where case
            if (!empty($this->_where))
            {
                $joined = join(" AND ", $this->_where);
                $where = "WHERE {$joined}";
            }
            
            # order case
            if (!empty($this->_order)) 
            {
                $order = "ORDER BY {$this->_order} {$this->_direction}";
            }
            
            # limit case
            if (!empty($this->_limit)) 
            {
                if ($this->_offset) 
                {
                    $limit = "LIMIT {$this->_limit}, {$this->_offset}";
                } 
                else 
                {
                    $limit = "LIMIT {$this->_limit}";
                }
            }
            return sprintf($template, $fields, $this->_from, $join, $where, $order, $limit);
        }
 
        /**
         * @name _buildInsert
         * @description creates an "INSERT INTO" query
         * @access protected 
         * @param array $data
         * @return string
         */
        protected function _buildInsert($data) 
        { 
            $fields = array();
            $values = array();
            $template = "INSERT INTO %s (%s) VALUES (%s)";
            foreach ($data as $field => $value) 
            {
                $fields[] = $field;
                $values[] = $this->_quote($value);
            }
            $fields = join(", ", $fields);
            $values = join(", ", $values);
            return sprintf($template, $this->_from, $fields, $values);
        }
      
        /**
         * @name _buildUpdate
         * @description creates an "UPDATE" query
         * @access protected 
         * @param array $data
         * @return string
         */
        protected function _buildUpdate($data) 
        {
            $parts = array();
            $where = $limit = "";
            $template = "UPDATE %s SET %s %s %s";
            foreach ($data as $field => $value) 
            {
                $parts[] = "{$field} = " . $this->_quote($value);
            }
            
            $parts = join(", ", $parts);
            
            # where case
            if (!empty($this->_where)) 
            {
                $joined = join(", ", $this->_where);
                $where = "WHERE {$joined}";
            }
            
            # limit case
            if (!empty($this->_limit)) 
            {
                $limit = "LIMIT {$this->_limit} {$this->_offset}";
            }
            return sprintf($template, $this->_from, $parts, $where, $limit);
        }

        /**
         * @name _buildDelete
         * @description creates an "DELETE FROM" query
         * @access protected 
         * @param array $data
         * @return string
         */
        protected function _buildDelete() 
        {
            $where = $limit = "";
            $template = "DELETE FROM %s %s %s";
            
            # where case
            if (!empty($this->_where)) 
            {
                $joined = join(", ", $this->_where);
                $where = "WHERE {$joined}";
            }
            
            # limit case
            if (!empty($this->_limit)) 
            {
                $limit = "LIMIT {$this->_limit} {$this->_offset}";
            }
            return sprintf($template, $this->_from, $where, $limit);
        }
    }
}
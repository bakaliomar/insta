<?php namespace ma\mailtng\types
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
    use ma\mailtng\exceptions\types\BackendException as BackendException;
    use ma\mailtng\exceptions\types\ArgumentException as ArgumentException;
    /**
     * @name            Arrays.class 
     * @description     It is a class that contains a bunch of Arrays management functions to apply usual routines such as cleaning, conversions, droping nulls, key sort, reindexing after poping, swap, and more ... 
     * @package		ma\mailtng\types
     * @category        DataTypes Class
     * @author		MailTng Team			
     */
    class Arrays extends Base
    { 
        /**
         * @name getElement
         * @description gets an element from an array
         * @access public | static 
         * @param array $array
         * @param mixed $key
         * @param mixed $default
         * @return array
         */
        public static function getElement($array,$key,$default = null) 
        {
            if(isset($key) && array_key_exists($key, $array))
            {
                return $array[$key];
            }
            return $default;
        }
        
        /**
         * @name setElement
         * @description set an new element to an array
         * @access public | static 
         * @param array $array
         * @param mixed $key
         * @param mixed $value
         * @return array
         */
        public static function setElement($array,$key,$value) 
        {
            return array_filter($array, function($item){
                return !empty($item);           
            });
        }
        
        /**
         * @name clean
         * @description converts a string to its singular form
         * @access public | static 
         * @param array $array
         * @return array
         */
        public static function clean($array) 
        {
            return array_filter($array, function($item){
                return !empty($item);           
            });
        }

        /**
         * @name trim
         * @description removes spaces from all elements from an array
         * @access public | static 
         * @param array $array
         * @return array
         */
        public static function trim($array) 
        {
            return array_map(function($item){
                return trim($item);
            }, $array);
        }

        /**
         * @name swap
         * @description uses the array given and swaps the value at index 1 with the value at index 2.
         * @access public | static 
         * @param array $array the array to use for the swap
         * @param mixed $index1 the index of the first value to be swapped.
         * @param mixed $index2 the index of the second value to be swapped.
         * @return string
         */
        public static function swap(&$array, $index1, $index2) 
        {
            $temp = $array[$index1];
            $array[$index1] = $array[$index2];
            $array[$index2] = $temp;
        }

        /**
         * @name reindex
         * @description reindexes the supplied array from 0 to number of values - 1.
         * @access public | static 
         * @param array $array
         * @return
         */
        public static function reindex(&$array) 
        {
            $temp = $array;
            $array = array();
            foreach ($temp as $value) 
            {
                $array[] = $value;
            }
        }

        /**
         * @name first
         * @description returns the first element in $array
         * @access public | static 
         * @param array $array
         * @return mixed
         */
        public static function first($array) 
        {
            if (is_array($array)) 
            {
                if(count($array) > 0)
                {
                    $tempArray = array_values($array);
                    return $tempArray[0];
                }
                
                return NULL;
            } 
            else 
            {
                throw new ArgumentException();
            } 
        }
        
        /**
         * @name last
         * @description returns the last element in $array
         * @access public | static 
         * @param array $array
         * @return mixed
         * @throws BackendException
         */
        public static function last($array) 
        {
            if (is_array($array)) 
            {
                return end($array);
            } 
            else 
            {
                throw new BackendException("I did not recieve an array!");
            } 
        }

        /**
         * @name moveElement
         * @description repositions an array element one slot up or down its current position.
         * @access public | static 
         * @param array $array associative array to shove new cell into
         * @param string $index index of the element to be moved
         * @param mixed $element value of the element to be moved (not required - use if you don't know what $index is ($index=NULL))
         * @param boolean $moveup direction to move. Defaults to moving up (true).
         * @param boolean $assocArray type of array. Numeric arrays get re-indexed.
         * @return array
         * @throws ArgumentException
         */
        public static function moveElement($array, $index = NULL, $element = NULL, $moveup = true, $assocArray = true) 
        {
            # parameter housekeeping 
            if (!is_array($array))
            {
                throw new ArgumentException("moveElement received a/an " . gettype($array) . " variable, not an array");
            }

            # if only the value was given, search for the key & delete it 
            if ($index == NULL) 
            {
                $index = array_search($element, $array);
                if (!$index)
                {
                    throw new ArgumentException("moveElement received " . gettype($array) . ", a value that doesn't exist in the array");
                }     
            }

            $element = $array[$index];

            # process the array 
            if ($moveup)
            {
                $array = array_reverse($array, TRUE);
            }

            foreach ($array as $key => $value) 
            {
                if ($key == $index)
                {
                    $buffer[$key] = $element;
                } 
                else 
                {
                    $newArray[$key] = $value;
                    if (isset($buffer)) 
                    {
                        $newArray[$index] = $element;
                        unset($buffer);
                    }
                }
            }

            if ($moveup)
            {
                $newArray = array_reverse($newArray, TRUE);
            }

            # reindex the result 
            if (!$assocArray && count($array)) 
            {
                foreach ($newArray as $newVal)
                {
                    $numerical_array[] = $newVal;
                }

                $newArray = $numerical_array;
            }

            return $newArray;
        }

        /**
         * @name removeElement
         * @description converts a string to its singular form
         * @access public | static 
         * @param array $array the array to remove that element from.
         * @param mixed $key the key of the element 
         * @param mixed $value the value to check whether is correct
         * @return array
         */
        public static function removeElement($array, $key = NULL, $value = NULL) 
        {
            if (is_array($array)) 
            {
                if (isset($key) && isset($value)) 
                {
                    if ($array[$key] == $value)
                        $newKey = $key;
                    else
                        return NULL;
                } 
                elseif (isset($key)) 
                {
                    if (is_string($key) && !is_numeric($key)) 
                    {
                        unset($array[$key]);
                        return $array;
                    }
                    else
                    {
                        $newKey = $key;
                    }     
                } 
                elseif (isset($value))
                {
                    $newKey = array_search($value, $array);
                } 
                else
                {
                    return NULL;
                }

                unset($array[$newKey]);

                # reindex the result 
                if (count($array)) 
                {
                    foreach ($array as $newValue)
                    {
                        $newArray[] = $newValue;
                    }

                    return $newArray;
                }
                else
                {
                    return $array;
                }     
            }
            else
            {
                throw new ArgumentException("removeElement: Variable sent to removeelement() was not an array");
            }     
        }

        /**
         * @name arrayToObject
         * @description converts an array into an object
         * @access public | static 
         * @param  object $object The object to copy variables from
         * @param  array  $array  The array to copy variables to
         * @return mixed
         */
        public static function arrayToObject($array) 
        {
            $result = new \stdClass();
            foreach ($array as $key => $value) 
            {
                if (is_array($value)) 
                {
                    $result->{$key} = self::arrayToObject($value);
                } 
                else 
                {
                    $result->{$key} = $value;
                }
            }
            return $result;
        }

        /**
         * @name dropNulls
         * @description unsets array rows where the value is NULL
         * @access public | static 
         * @param array $array
         * @return
         * @throws ArgumentException
         */
        public static function dropNulls(&$array) 
        {
            if (is_array($array)) 
            {
                reset($array);
                foreach ($array as $key => $value) 
                {
                    if (is_null($value)) 
                    {
                        unset($array[$key]);
                    }
                }
            }
            else
            {
                throw new ArgumentException("dropNulls: Array not received!");
            }     
        }

        /**
         * @name ksort
         * @description copy of php's ksort with a descending mode
         * @access public | static 
         * @param array $array
         * @param string $mode
         * @return
         */
        public static function ksort(&$array, $mode = "SORT_ASC") 
        {
            if ($mode == "SORT_DESC")
            {
                uksort($array, "cmp");
            }
            else
            {
                ksort($array);
            }    
        }

        /**
         * @name interval
         * @description creates an array of numbers from start to final in increments of the interval variable
         * @access public | static 
         * @param integer $final final number of the count
         * @param integer $start number to begin counting on (not from)
         * @param integer $interval number to increment the count
         * @return string
         * @throws ArgumentException
         */
        public static function interval($final, $start = 0, $interval = 1) 
        {
            $count = 0;
            if ($interval && $final > $start) 
            {
                for ($i = $start; $i <= $final; $i = $i + $interval) 
                {
                    $count++;
                    $result[] = $i;
                    if ($count > 10000)
                    {
                        throw new ArgumentException("interval: Interval is too large");
                    }     
                }
                return $result;
            }
            else
            {
                return NULL;
            }     
        }

        /**
         * @name getElementIndex
         * @description gets the index of an element inside an array
         * @access public | static 
         * @param mixed $element The element to search for its position
         * @param  array $array The array to search in
         * @return mixed
         * @throws ArgumentException
         */
        public static function getElementIndex($element,$array) 
        {
            if (isset($element) && isset($array)) 
            {
                if (is_array($element))
                {
                    foreach ( $array as $key => $value ) 
                    {
                        if(!is_string($element))
                        {
                            if ($element === $value) 
                            {
                                return $key;
                            }
                        }
                        else if (strpos ($element,$value) !== FALSE) 
                        {
                            return $key;
                        }
                    }
                }
                else
                {
                    throw new ArgumentException("getElementIndex: Array not received!");
                }
            }
            else
            {
                return NULL;
            }     
        }

        /**
         * @name getMaxValue
         * @description gets the max value of an array
         * @access public | static 
         * @param  array $array The array to search in
         * @return array
         */
        public static function getMaxValue($array)
        {    
            $max = $array[0];
            $index = 0;
            foreach($array as $key => $val)
            {
                if($val > $max)
                {
                    $max = $val;
                    $index = $key;
                }
            }   
            return array("index"=>$index, "value"=>$max);
        } 

        /**
         * @name getMinValue
         * @description gets the min value of an array
         * @access public | static 
         * @param array $array
         * @return array
         */
        public static function getMinValue($array)
        {    
            $min = $array[0];
            $index = 0;
            foreach($array as $key => $val)
            {
                if($val < $min)
                {
                    $min = $val;
                    $index = $key;
                }
            }   
            return array("index"=>$index, "value"=>$min);
        } 

        /**
         * @name getSum
         * @description gets the the sum of an array values
         * @access public | static 
         * @param array $array
         * @return array
         */
        public static function getSum($array)
        {    
            $sum = 0;
            foreach($array as $val)
            {
                $sum += $val;
            }   
            return $sum; 
        } 

        /**
         * @name flatten
         * @description converts multidimensional arrays into a unidimensional arrays
         * @access public | static 
         * @param array $array
         * @return array
         */
        public static function flatten($array)
        {
            $return = array();
                    
            foreach ($array as $key => $value)
            {
                if (is_array($value) || is_object($value))
                {
                    $return = self::flatten($value, $return);
                }
                else
                {
                    $return[] = $value;
                }
            }
            return $return;
        }
    }
}
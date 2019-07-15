<?php namespace ma\mailtng\html
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
    /**
     * @name            Tables.class 
     * @description     It's a class that deals with HTML Tables methods
     * @package		ma\mailtng\html
     * @category        Helper Class
     * @author		MailTng Team			
     */
    class Tables extends Base
    {
        /**
         * @name createHTMLTable
         * @description creates an HTML table
         * @access static
         * @param array $columns
         * @param array $data
         * @param array $attributes
         * @return
         */  
        public static function createHTMLTable($columns,$data,$attributes = array())
        {
            $table = '';
            
            if(isset($columns) && count($columns))
            {
                $table = '<table ';
                
                if(count($attributes))
                {
                    foreach ($attributes as $attribute => $value) 
                    {
                        $table .= $attribute . '="' . $value . '" ';
                    }
                }
                
                $table .= '><thead><tr>';
                
                # header columns 
                foreach ($columns as $column) 
                {
                   $table .= '<th>' . ucfirst(str_replace('_',' ',strtolower($column))) . '</th>';
                }
                
                $table .= '</tr></thead>';  
                
                # body
                $table .= '<tbody>'; 
                
                foreach ($data as $row) 
                {
                    if(count($row))
                    {
                        foreach ($columns as $column) 
                        {
                            $value = is_numeric($row[$column]) ? '<center>' . $row[$column] . '</center>' : $row[$column];
                            $table .= '<td>' . $value . '</td>';
                        }
                    }
                }
                
                $table .= '</tbody></table>'; 
            }
            
            return $table;
        }   
    }
}
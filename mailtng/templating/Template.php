<?php namespace ma\mailtng\templating
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
    use ma\mailtng\types\Arrays as Arrays;
    use ma\mailtng\exceptions\types\TemplateException as TemplateException;
    /**
     * @name            Template.class 
     * @description     It's a class that deals with interfaces ( parses templates files and replaces placeholders with data then displays the page )
     * @package		ma\mailtng\templating
     * @category        Templating Class
     * @author		MailTng Team			
     */
    class Template extends Base 
    {
        /** 
         * @readwrite
         * @access protected 
         * @var Implementation
         */
        protected $_implementation;
 
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_header = "\nif(is_array(\$_data) && sizeof(\$_data))\nextract(\$_data); \n\$_text = array();\n";

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_footer = "\nreturn implode(\$_text);\n";

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_code;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_function;

        /**
         * @name parse
         * @description creates a working function for the template 
         * @access public
         * @param string $template the template content 
         * @return Template
         * @throws TemplateException
         */
        public function parse($template) 
        {
            if (!$this->_implementation instanceof Implementation) 
            {
                throw new TemplateException('Unsupported Implementation');
            }

            $array = $this->_array($template);
            $tree = $this->_tree($array["all"]);
            $this->_code = $this->header . $this->_script($tree) . $this->footer;
            $this->_function = create_function("\$_data", $this->code);
            return $this;
        }

        /**
         * @name process
         * @description checks for the existence of the protected $_function property and throws a TemplateException exception if it is not present. 
         * @notice it then tries to execute the generated function with the $data passed to it , if the function errors, another TemplateException exception is thrown.
         * @access public
         * @param array $data the data passed to the template
         * @return string
         * @throws TemplateException
         */
        public function process($data = array()) 
        {
            if ($this->_function == null) 
            {
                throw new TemplateException('No function defined in the parser');
            }
            
            try 
            {
                $function = $this->_function;
                return $function($data);
            } 
            catch (\Exception $exception) 
            {
                throw new TemplateException($exception->getMessage(),500,$exception);
            }
        }
        
        /**
         * @name _arguments
         * @description returns the bits between the {...} characters in a neat associative array if the expression has a specific argument format (such as for, foreach, or macro).
         * @access protected
         * @param string $source the chunk of template
         * @param string $expression the expression to check for arguments in
         * @return array
         */
        protected function _arguments($source, $expression) 
        {
            $args = $this->_array($expression, array(
                $expression => array(
                    "opener" => "{",
                    "closer" => "}"
                )
            ));
            $tags = $args["tags"];
            $arguments = array();
            $sanitized = Strings::sanitize($expression, "()[],.<>*$@");

            foreach ($tags as $i => $tag) 
            {
                $sanitized = str_replace($tag, "(.*)", $sanitized);
                $tags[$i] = str_replace(array("{", "}"), "", $tag);
            }
            
            $matches = array();
            if (preg_match("#{$sanitized}#", $source, $matches)) 
            {
                foreach ($tags as $i => $tag) 
                {
                    $arguments[$tag] = $matches[$i + 1];
                }
            }
            return $arguments;
        }

        /**
         * @name _tag
         * @description checks if the chunk of template passed , is a tag or a plain string.
         * @notice it will return false for a nonmatch , it then extracts all the bits between the opener and closer strings.
         * @access protected
         * @param string $source the chunk of template
         * @return mixed
         */
        protected function _tag($source) 
        {
            $tag = null;
            $arguments = array();

            $match = $this->_implementation->match($source);
            
            if ($match == null) 
            {
                return false;
            }

            $delimiter = $match["delimiter"];
            $type = $match["type"];

            $start = strlen($type["opener"]);
            $end = strpos($source, $type["closer"]);
            $extract = substr($source, $start, $end - $start);

            if (isset($type["tags"])) 
            {
                $tags = implode("|", array_keys($type["tags"]));
                $regex = "#^(/){0,1}({$tags})\s*(.*)$#";
                
                $matches = array();
                if (!preg_match($regex, $extract, $matches)) 
                {
                    return false;
                }
                $tag = $matches[2];
                $extract = $matches[3];

                $closer = !!$matches[1];
            }

            if ($tag && $closer) 
            {
                return array(
                    "tag" => $tag,
                    "delimiter" => $delimiter,
                    "closer" => true,
                    "source" => false,
                    "arguments" => false,
                    "isolated" => $type["tags"][$tag]["isolated"]
                );
            }

            if (isset($type["arguments"])) 
            {
                $arguments = $this->_arguments($extract, $type["arguments"]);
            } 
            else if ($tag && isset($type["tags"][$tag]["arguments"])) 
            {
                $arguments = $this->_arguments($extract, $type["tags"][$tag]["arguments"]);
            }

            return array(
                "tag" => $tag,
                "delimiter" => $delimiter,
                "closer" => false,
                "source" => $extract,
                "arguments" => $arguments,
                "isolated" => (!empty($type["tags"]) ? $type["tags"][$tag]["isolated"] : false)
            );
        }

        /**
         * @name _array
         * @description deconstructs a template string into arrays of tags, text, and a combination of the two
         * @access protected
         * @param string $source the chunk of template
         * @return array
         */
        protected function _array($source) 
        {
            $parts = array();
            $tags = array();
            $all = array();
            
            $type = null;
            $delimiter = null;

            while ($source) 
            {
                $match = $this->_implementation->match($source);

                $type = $match["type"];
                $delimiter = $match["delimiter"];

                $opener = strpos($source, $type["opener"]);
                $closer = strpos($source, $type["closer"]) + strlen($type["closer"]);

                if ($opener !== false) 
                {
                    $parts[] = substr($source, 0, $opener);
                    $tags[] = substr($source, $opener, $closer - $opener);
                    $source = substr($source, $closer);
                } 
                else 
                {
                    $parts[] = $source;
                    $source = "";
                }
            }

            foreach ($parts as $i => $part) 
            {
                $all[] = $part;
                if (isset($tags[$i])) 
                {
                    $all[] = $tags[$i];
                }
            }
            return array(
                "text" => Arrays::clean($parts),
                "tags" => Arrays::clean($tags),
                "all" => Arrays::clean($all)
            );
        }

        /**
         * @name _tree
         * @description it loops through the array of template segments, 
         * generated by the _array() method, and organizes 
         * them into a hierarchical structure. 
         * Plain text nodes are simply assigned as-is to 
         * the tree, while additional metadata is generated
         * and assigned with the tags. 
         * @notice certain statements have an isolated property. 
         * This specifies whether text is allowed before the statement.
         * When the loop gets to an isolated tag, it removes the preceding
         * segment (as long as it is a plain text segment), so that the resultant
         * function code is syntactically correct.
         * @access protected
         * @param array $array The array of template segments
         * @return array
         */
        protected function _tree($array) 
        {
            $root = array(
                "children" => array()
            );
            $current = & $root;

            foreach ($array as $i => $node) 
            {
                $result = $this->_tag($node);

                if ($result) 
                {
                    $tag = isset($result["tag"]) ? $result["tag"] : "";
                    $arguments = isset($result["arguments"]) ? $result["arguments"] : "";

                    if ($tag) 
                    {
                        if (!$result["closer"]) 
                        {
                            $last = Arrays::last($current["children"]);

                            if ($result["isolated"] && is_string($last)) 
                            {
                                array_pop($current["children"]);
                            }
                            
                            $current["children"][] = array(
                                "index" => $i,
                                "parent" => &$current,
                                "children" => array(),
                                "raw" => $result["source"],
                                "tag" => $tag,
                                "arguments" => $arguments,
                                "delimiter" => $result["delimiter"],
                                "number" => sizeof($current["children"])
                            );
                            $current = & $current["children"][sizeof($current["children"]) - 1];
                        } 
                        else if (isset($current["tag"]) && $result["tag"] == $current["tag"]) 
                        {
                            $start = $current["index"] + 1;
                            $length = $i - $start;
                            $current["source"] = implode(array_slice($array, $start, $length));
                            $current = & $current["parent"];
                        }
                    } 
                    else 
                    {
                        $current["children"][] = array(
                            "index" => $i,
                            "parent" => &$current,
                            "children" => array(),
                            "raw" => $result["source"],
                            "tag" => $tag,
                            "arguments" => $arguments,
                            "delimiter" => $result["delimiter"],
                            "number" => sizeof($current["children"])
                        );
                    }
                } 
                else 
                {
                    $current["children"][] = $node;
                }
            }
            return $root;
        }

        /**
         * @name _script
         * @description walks the hierarchy (generated by the _tree() method), parses plain text nodes, and indirectly invokes the handler for each valid tag. 
         * @access protected
         * @param array $tree The hierarchy tree array
         * @return string
         */
        protected function _script($tree) 
        {
            $content = array();

            if (is_string($tree)) 
            {
                $tree = addslashes($tree);
                return "\$_text[] = \"{$tree}\";";
            }

            if (sizeof($tree["children"]) > 0) 
            {
                foreach ($tree["children"] as $child) 
                {
                    $content[] = $this->_script($child);
                }
            }

            if (isset($tree["parent"])) 
            {
                return $this->_implementation->handle($tree, implode($content));
            }

            return implode($content);
        }
    }
}
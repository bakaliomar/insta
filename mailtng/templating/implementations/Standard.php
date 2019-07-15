<?php namespace ma\mailtng\templating\implementations
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
    use ma\mailtng\templating\Implementation as Implementation;
    /**
     * @name            Standard.class 
     * @description     An stndard implementation of our templates 
     * @package		ma\mailtng\templating\implementations
     * @category        Templating Class
     * @author		MailTng Team			
     */
    class Standard extends Implementation
    {
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_map = array(
            "echo" => array(
                "opener" => "{echo",
                "closer" => "}",
                "handler" => "_echo"
            ),
            "script" => array(
                "opener" => "{script",
                "closer" => "}",
                "handler" => "_script"
            ),
            "statement" => array(
                "opener" => "{",
                "closer" => "}",
                "tags" => array(
                    "foreach" => array(
                        "isolated" => false,
                        "arguments" => "{element} in {object}",
                        "handler" => "_each"
                    ),
                    "for" => array(
                        "isolated" => false,
                        "arguments" => "{element} in {object}",
                        "handler" => "_for"
                    ),
                    "if" => array(
                        "isolated" => false,
                        "arguments" => null,
                        "handler" => "_if"
                    ),
                    "elseif" => array(
                        "isolated" => true,
                        "arguments" => null,
                        "handler" => "_elseif"
                    ),
                    "else" => array(
                        "isolated" => true,
                        "arguments" => null,
                        "handler" => "_else"
                    ),
                    "macro" => array(
                        "isolated" => false,
                        "arguments" => "{name}({args})",
                        "handler" => "_macro"
                    ),
                    "literal" => array(
                        "isolated" => false,
                        "arguments" => null,
                        "handler" => "_literal"
                    )
                )
            )
        );

        /**
         * @name _echo
         * @description converts the string “{echo $hello}” to “$_text[] = $hello”, so that it is already optimized for our final evaluated function.
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _echo($tree, $content)
        {
            $raw = $this->_script($tree, $content);
            return "\$_text[] = {$raw}";
        }

        /**
         * @name _script
         * @description converts the string “{:$foo + = 1}” to “$foo + = 1”, so that it is already optimized for our final evaluated function.
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _script($tree, $content)
        {
            $raw = !empty($tree["raw"]) ? $tree["raw"] : "";
            return "{$raw};";
        }

        /**
         * @name _each
         * @description returns the code to perform a foreach loop through an array
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _each($tree, $content)
        {
            $object = $tree["arguments"]["object"];
            $element = $tree["arguments"]["element"];

            return $this->_loop($tree,
                "foreach ({$object} as {$element}_i => {$element}) {
                    {$content}
                }"
            );
        }

        /**
         * @name _for
         * @description produces the code to perform a for loop through an array
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _for($tree, $content)
        {
            $object = $tree["arguments"]["object"];
            $element = $tree["arguments"]["element"];

            return $this->_loop($tree, 
                "for ({$element}_i = 0; {$element}_i < sizeof({$object}); {$element}_i++) {
                        {$element} = {$object}[{$element}_i];
                        {$content}
                }"
            );
        }

        /**
         * @name _if
         * @description return code to perform an IF statement in the template
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _if($tree, $content)
        {
            $raw = $tree["raw"];
            return "if({$raw}) {{$content}}";
        }

        /**
         * @name _elseif
         * @description return code to perform an ELSEIF statement in the template
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _elseif($tree, $content)
        {
            $raw = $tree["raw"];
            return "elseif({$raw}) {{$content}}";
        }

        /**
         * @name _else
         * @description return code to perform an ELSE statement in the template
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _else($tree, $content)
        {
            return "else {{$content}}";
        }

        /**
         * @name _macro
         * @description creates the string representation of a function,based on the contents of a {macro...}...{/macro} tag set. 
         * @notice it is possible, using the {macro} tag, to define functions, which we then use within our templates.
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _macro($tree, $content)
        {
            $arguments = $tree["arguments"];
            $name = $arguments["name"];
            $args = $arguments["args"];

            return "function {$name}({$args}) {
            \$_text = array();
            {$content}
            return implode(\$_text);
            }";
        }

        /**
         * @name _literal
         * @description quotes any content within it. 
         * @notice the template parser only stops directly quoting the content when it finds a {/literal} closing tag.
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _literal($tree, $content)
        {
            $source = addslashes($tree["source"]);
            return "\$_text[] = \"{$source}\";";
        }

        /**
         * @name _loop
         * @description it augments the output of _for , _foreach with checks for the contents of the arrays used by those statements, as long as an {else} tag follows them.
         * @access protected
         * @param string $tree
         * @param string $content
         * @return string
         */
        protected function _loop($tree, $inner)
        {
            $number = $tree["number"];
            $object = $tree["arguments"]["object"];
            $children = $tree["parent"]["children"];

            if (!empty($children[$number + 1]["tag"]) && $children[$number + 1]["tag"] == "else")
            {
                return "if (is_array({$object}) && sizeof({$object}) > 0) {{$inner}}";
            }
            
            return $inner;
        }
    }
}
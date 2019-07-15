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
    /**
     * @name            Objects.class 
     * @description     It is a bunch of Strings management functions to apply usual routines such as emoving accents, conversions of html characters, genarating random text, and more 
     * @package		ma\mailtng\types
     * @category        DataTypes Class
     * @author		MailTng Team			
     */
    class Strings extends Base
    {
        /** 
         * @readwrite
         * @access private | static 
         * @var string
         */ 
        private static $_delimiter = "#";

        /** 
         * @readwrite
         * @access private | static 
         * @var string
         */ 
        private static $_singular = array(
            "(matr)ices$" => "\\1ix",
            "(vert|ind)ices$" => "\\1ex",
            "^(ox)en" => "\\1",
            "(alias)es$" => "\\1",
            "([octop|vir])i$" => "\\1us",
            "(cris|ax|test)es$" => "\\1is",
            "(shoe)s$" => "\\1",
            "(o)es$" => "\\1",
            "(bus|campus)es$" => "\\1",
            "([m|l])ice$" => "\\1ouse",
            "(x|ch|ss|sh)es$" => "\\1",
            "(m)ovies$" => "\\1\\2ovie",
            "(s)eries$" => "\\1\\2eries",
            "([^aeiouy]|qu)ies$" => "\\1y",
            "([lr])ves$" => "\\1f",
            "(tive)s$" => "\\1",
            "(hive)s$" => "\\1",
            "([^f])ves$" => "\\1fe",
            "(^analy)ses$" => "\\1sis",
            "((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$" => "\\1\\2sis",
            "([ti])a$" => "\\1um",
            "(p)eople$" => "\\1\\2erson",
            "(m)en$" => "\\1an",
            "(s)tatuses$" => "\\1\\2tatus",
            "(c)hildren$" => "\\1\\2hild",
            "(n)ews$" => "\\1\\2ews",
            "([^u])s$" => "\\1"
        );
        
        /** 
         * @readwrite
         * @access private | static 
         * @var string
         */ 
        private static $_plural = array(
            "^(ox)$" => "\\1\\2en",
            "([m|l])ouse$" => "\\1ice",
            "(matr|vert|ind)ix|ex$" => "\\1ices",
            "(x|ch|ss|sh)$" => "\\1es",
            "([^aeiouy]|qu)y$" => "\\1ies",
            "(hive)$" => "\\1s",
            "(?:([^f])fe|([lr])f)$" => "\\1\\2ves",
            "sis$" => "ses",
            "([ti])um$" => "\\1a",
            "(p)erson$" => "\\1eople",
            "(m)an$" => "\\1en",
            "(c)hild$" => "\\1hildren",
            "(buffal|tomat)o$" => "\\1\\2oes",
            "(bu|campu)s$" => "\\1\\2ses",
            "(alias|status|virus)" => "\\1es",
            "(octop)us$" => "\\1i",
            "(ax|cris|test)is$" => "\\1es",
            "s$" => "s",
            "$" => "s"
        );

        /**
         * @name singular
         * @description converts a string to its singular form
         * @access public | static 
         * @param string $string
         * @return string
         */
        public static function singular($string) 
        {
            $result = $string;
            foreach (self::$_singular as $rule => $replacement) 
            {
                $rule = self::_normalize($rule);
                if (preg_match($rule, $string)) 
                {
                    $result = preg_replace($rule, $replacement, $string);
                    break;
                }
            }
            return $result;
        }

        /**
         * @name plural
         * @description converts a string to its plural form
         * @access public | static 
         * @param string $string
         * @return string
         */
        public static function plural($string)
        {
            $result = $string;
            foreach (self::$_plural as $rule => $replacement)
            {
                $rule = self::_normalize($rule);
                if (preg_match($rule, $string))
                {
                    $result = preg_replace($rule, $replacement, $string);
                    break;
                }
            }
            return $result;
        }

        /**
         * @name removeAccents
         * @description removes accents from charachters in a text  
         * @access public | static 
         * @param string $text
         * @return string
         */
        public static function removeAccents($text) 
        {
            $text = (string) $text;
            $arrayWithAccents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
            $arrayWithoutAccents = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
            return str_replace($arrayWithAccents,$arrayWithoutAccents, $text);
        }
        
        /**
         * @name slug
         * @description replacing html special characters 
         * @access public | static 
         * @param string $text
         * @return mixed
         */
        public static function slug($text) 
        {
            return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'),array('', '-', ''), self::removeAccent($text)));
        }

        /**
         * @name match
         * @description matching for results in a text by a regex pattern
         * @access public | static 
         * @param string $string
         * @param string $pattern
         * @return mixed
         */
        public static function match($string, $pattern) 
        {
            $matches = array();
            preg_match_all(self::_normalize($pattern), $string, $matches, PREG_PATTERN_ORDER);
            if (!empty($matches[1])) 
            {
                return $matches[1];
            }
            if (!empty($matches[0])) 
            {
                return $matches[0];
            }
            return null;
        }
   
        /**
         * @name split
         * @description splitting a text by a regex pattern
         * @access public | static 
         * @param string $string
         * @param string $pattern
         * @param integer $limit
         * @return mixed
         */
        public static function split($string, $pattern, $limit = null) 
        {
            $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
            return preg_split(self::_normalize($pattern), $string, $limit, $flags);
        }

        /**
         * @name generateRandomText
         * @description generates random text 
         * @access public | static 
         * @param integer $size the size of generated text 
         * @param boolean $letters boolean value to tell the function whether use letters or not 
         * @param boolean $uppercase boolean value to tell the function whether use uppercase letters too or not 
         * @param boolean $numbers boolean value to tell the function whether use numbers or not
         * @param boolean $specialCharacters boolean value to tell the function whether use special characters or not
         * @return string
         */
        public static function generateRandomText($size = 5,$letters = true,$uppercase = true,$numbers = true,$specialCharacters = true)
        {
            $randomText = '';
            $characters = '';
            
            if($letters)
            {
                $characters .= 'abcdefghijklmnopqrstuvwxyz';
                if($uppercase)
                {
                    $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                }
            }
            
            if($numbers)
            {
                $characters .= '0123456789';
            }
            
            if($specialCharacters)
            {
                $characters = '@\\/_*$&-#[](){}';
            }
            
            for ($i = 0; $i <$size; $i++) 
            {
                 $randomText .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $randomText;
        }

        /**
         * @name sanitize
         * @description loops through the characters of a string, replacing them with regular expression friendly character representations.
         * @access public | static 
         * @param string $string
         * @param string $mask
         * @return string
         */
        public static function sanitize($string, $mask) 
        {
            if (is_array($mask)) 
            {
                $parts = $mask;
            } 
            else if (is_string($mask)) 
            {
                $parts = str_split($mask);
            } 
            else 
            {
                return $string;
            }
            
            foreach ($parts as $part) 
            {
                $normalized = self::_normalize("\\{$part}");
                $string = preg_replace("{$normalized}m", "\\{$part}", $string);
            }
            return $string;
        }

        /**
         * @name unique
         * @description eliminates all duplicated characters in a string 
         * @access public | static 
         * @param string $string
         * @return string
         */
        public static function unique($string) 
        {
            $unique = "";
            $parts = str_split($string);

            foreach ($parts as $part) 
            {
                if (!strstr($unique, $part)) 
                {
                    $unique .= $part;
                }
            }

            return $unique;
        }

        /**
         * @name indexOf
         * @description gets the index of a text inside another text
         * @access public | static 
         * @param string $string
         * @param string $substring
         * @param integer $offset
         * @return mixed
         */
        public static function indexOf($string, $substring, $offset = null) 
        {
            $position = strpos($string, $substring, $offset);
            if (!is_int($position)) 
            {
                return -1;
            }
            return $position;
        }

        /**
         * @name startsWith
         * @description check if a string starts with a given needle
         * @access public | static 
         * @param string $string
         * @param string $needle
         * @return mixed
         */
        public static function startsWith($string, $needle) 
        {
            # search backwards starting from haystack length characters from the end
            return $needle === "" || strrpos($string, $needle, -strlen($string)) !== FALSE;
        }

        /**
         * @name endsWith
         * @description get class name without namespace in it  
         * @access public | static 
         * @param string $string
         * @param string $needle
         * @return mixed
         */
        public static function endsWith($string, $needle) 
        {
            # search forward starting from end minus needle length characters
            return $needle === "" || (($temp = strlen($string) - strlen($needle)) >= 0 && strpos($string, $needle, $temp) !== FALSE);
        }

        /**
         * @name endsWith
         * @description get class name without namespace in it  
         * @access public | static 
         * @param string $string
         * @param string $needle
         * @return mixed
         */
        public static function niceNumber($number) 
        {
            $number = (0 + str_replace(",", "", $number));
            
            if ($number > 1000000000000) return str_replace(".",",",round(($number/1000000000000), 2) .' Tr');
            elseif ($number > 1000000000) return str_replace(".",",",round(($number/1000000000), 2).' B');
            elseif ($number > 1000000) return str_replace(".",",",round(($number/1000000), 2).' M');
            elseif ($number > 1000) return str_replace(".",",",round(($number/1000), 2).' T');
            if (!is_numeric($number)) return false;
            return number_format($number); 
        }

        /**
         * @name _normalize
         * @description triming the pattern (regex) by making sure that the delimeter is not declared twice in the pattern
         * @access private | static 
         * @param string $pattern
         * @return mixed
         */
        private static function _normalize($pattern)
        {
            return self::$_delimiter.trim($pattern, self::$_delimiter).self::$_delimiter;
        }
    }
}
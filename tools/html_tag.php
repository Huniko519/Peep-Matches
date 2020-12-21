<?php

class UTIL_HtmlTag
{

    /**
     * Generates and returns HTML tag code.
     *
     * @param string $tag
     * @param array $attrs
     * @param boolean $pair
     * @param string $content
     * @return string
     */
    public static function generateTag( $tag, $attrs = null, $pair = false, $content = null )
    {
        $attrString = '';
        if ( $attrs !== null && !empty($attrs) )
        {
            foreach ( $attrs as $key => $value )
            {
                $attrString .= ' ' . $key . '="' . $value . '"';
            }
        }

        return $pair ? '<' . $tag . $attrString . '>' . ( $content === null ? '' : $content ) . '</' . $tag . '>' : '<' . $tag . $attrString . ' />';
    }

    /**
     * Generates randow ID for HTML tags.
     *
     * @param string $prefix
     * @return string
     */
    public static function generateAutoId( $prefix = null )
    {
        $prefix = ( $prefix === null ) ? 'auto_id' : trim($prefix);

        return $prefix . '_' . rand(1, 100000000);
    }
    /**
     * @var Jevix
     */
    private static $jevix;

    /**
     * @return Jevix
     */
    private static function getJevix( $tagList = null, $attrList = null, $blackListMode = false, $mediaSrcValidate = true )
    {
        if ( self::$jevix === null )
        {
            require_once PEEP_DIR_LIB . 'jevix' . DS . 'jevix.class.php';

            self::$jevix = new Jevix();
        }

        $tagRules = array();
        $commonAttrs = array();

        if ( !empty($tagList) )
        {
            foreach ( $tagList as $tag )
            {
                $tagRules[$tag] = array(Jevix::TR_TAG_LIST => true);
            }
        }
        
        if ( $attrList !== null )
        {
            foreach ( $attrList as $attr )
            {
                if ( strstr($attr, '.') )
                {
                    $parts = explode('.', $attr);

                    $tag = trim($parts[0]);
                    $param = trim($parts[1]);

                    if( !strlen($tag) || !strlen($attr) )
                    {
                        continue;
                    }
                                        
                    if( $tag === '*' )
                    {
                        $commonAttrs[] = $param;
                        continue;
                    }
                    
                    if ( !isset($tagRules[$tag]) )
                    {
                        $tagRules[$tag] = array(Jevix::TR_TAG_LIST => true);
                    }
                    
                    if( !isset($tagRules[$tag][Jevix::TR_PARAM_ALLOWED]) )
                    {
                        $tagRules[$tag][Jevix::TR_PARAM_ALLOWED] = array();
                    }
                    
                    $tagRules[$tag][Jevix::TR_PARAM_ALLOWED][$param] = true;
                }
                else
                {
                    $commonAttrs[] = trim($attr);
                }
            }
        }

        $shortTags = array('img', 'br', 'input', 'embed', 'param', 'hr', 'link', 'meta', 'base', 'col');
        foreach ( $shortTags as $shortTag )
        {
            if ( !isset($tagRules[$shortTag]) )
            {
                $tagRules[$shortTag] = array();
            }

            $tagRules[$shortTag][Jevix::TR_TAG_SHORT] = true;
        }

        $cutWithContent = array('script', 'embed', 'object', 'style');

        foreach ( $cutWithContent as $cutTag )
        {
            if( !isset($tagRules[$cutTag]) )
            {
                $tagRules[$cutTag] = array();
            }

            $tagRules[$cutTag][Jevix::TR_TAG_CUT] = true;
        }

        self::$jevix->blackListMode = $blackListMode;
        self::$jevix->commonTagParamRules = $commonAttrs;
        self::$jevix->tagsRules = $tagRules;
        self::$jevix->mediaSrcValidate = $mediaSrcValidate;
        self::$jevix->mediaValidSrc = BOL_TextFormatService::getInstance()->getMediaResourceList();

        return self::$jevix;
    }

    /**
     * Removes all restricted HTML tags and attributes. Works with white and black lists.
     *
     * @param string $text
     * @param array $tagList
     * @param array $attributeList
     * @param boolean $nlToBr
     * @param boolean $blackListMode
     * @param boolean $autoLink
     *
     * @return string
     */
    public static function stripTags( $text, array $tagList = null, array $attributeList = null, $blackListMode = false, $mediaSrcValidate = true )
    {
        // style remove fix
        if( $blackListMode )
        {
            if( $tagList === null )
            {
                $tagList = array();
            }
            
            $tagList[] = 'style';
            
//            if( $attributeList === null )
//            {
//                $attributeList = array();
//            }
//            
//            $attributeList[] = '*.style';
        }
        else
        {
            if( is_array($tagList) )
            {
                if( in_array('style', $tagList) )
                {
                    $tagList = array_diff($tagList, array('style'));
                }
            }
            
//            if( is_array( $attributeList ) )
//            {
//                foreach ( $attributeList as $key => $item )
//                {
//                    if( strstr($item, 'style') )
//                    {
//                        unset($attributeList[$key]);
//                    }
//                }
//            }
        }
        // fix end
        
        $jevix = self::getJevix($tagList, $attributeList, $blackListMode, $mediaSrcValidate);
        return $jevix->parse($text);
    }

    /**
     * Removes <script> tags and JS event handlers.
     *
     * @param string $text

     * @return string
     */
    public static function stripJs( $text )
    {
        $tags = array('script');

        $attrs = array(
            'onchange',
            'onclick',
            'ondblclick',
            'onerror',
            'onfocus',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onload',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onreset',
            'onselect',
            'onsubmit',
            'onunload');

        $jevix = self::getJevix($tags, $attrs, true, false);
        return $jevix->parse($text);
    }

    /**
     * Sanitizes provided html code to escape unclosed tags and params.
     * @param string $text
     * @return string
     */
    public static function sanitize( $text )
    {
        $jevix = self::getJevix(null, null, true, false);
        return $jevix->parse($text);
    }

    /**
     * Replaces all urls with link tags in the provided text.
     *
     * @param string $text
     * @return string
     */
    public static function autoLink( $text )
    {
        $jevix = self::getJevix(array(), array(), true, false);
        $jevix->isAutoLinkMode = true;

        return $jevix->parse($text);
    }
}
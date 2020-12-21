<?php

final class BOL_TextFormatService
{
    const WS_BTN_BOLD = 'bold';
    const WS_BTN_ITALIC = 'italic';
    const WS_BTN_UNDERLINE = 'underline';
    const WS_BTN_UNORDERED_LIST = 'unorderedlist';
    const WS_BTN_ORDERED_LIST = 'orderedlist';
    const WS_BTN_LINK = 'link';
    const WS_BTN_IMAGE = 'image';
    const WS_BTN_VIDEO = 'video';
    const WS_BTN_HTML = 'html';
    const WS_BTN_SWITCH_HTML = 'switchHtml';
    const WS_BTN_MORE = 'more';

    const CONF_MEDIA_RESOURCE_LIST = 'tf_resource_list';
    const CONF_USER_INPUT_CUSTOM_HTML_DISABLE = 'tf_user_custom_html_disable';
    const CONF_USER_INPUT_RICH_MEDIA_DISABLE = 'tf_user_rich_media_disable';
    const CONF_COMMENTS_RICH_MEDIA_DISABLE = 'tf_comments_rich_media_disable';

    /**
     * @var array
     */
    private $videoParams;
    /**
     * @var array
     */
    private $htmlParams;
    /**
     * @var array
     */
    private $buttonTags;
    /**
     * Singleton instance.
     *
     * @var BOL_TextFormatService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_TextFormatService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->buttonTags = array(
            self::WS_BTN_BOLD => array('tags' => array('b', 'strong', 'span'), 'attrs' => array('span.style')),
            self::WS_BTN_ITALIC => array('tags' => array('i', 'em', 'span'), 'attrs' => array('span.style')),
            self::WS_BTN_UNDERLINE => array('tags' => array('u', 'span'), 'attrs' => array('span.style')),
            self::WS_BTN_ORDERED_LIST => array('tags' => array('ol', 'li'), 'attrs' => array()),
            self::WS_BTN_UNORDERED_LIST => array('tags' => array('ul', 'li'), 'attrs' => array()),
            self::WS_BTN_LINK => array('tags' => array('a'), 'attrs' => array('a.href', 'a.title', 'a.target', 'a.rel')),
            self::WS_BTN_IMAGE => array('tags' => array('img', 'a'), 'attrs' => array('img.src', 'img.width', 'img.height', 'img.alt', 'img.style', 'a.href', 'a.target')),
        );

        $this->videoParams = array(
            'tags' => array('object', 'iframe', 'param', 'embed'),
            'attrs' => array('id', 'width', 'height', 'data', 'type', 'name', 'value', 'src', 'allowfullscreen', 'frameborder', 'flashvars')
        );

        $this->htmlParams = array(
            'tags' => array(),
            'attrs' => array()
        );
    }

    /**
     * Returns flag if custom html is allowed for user input.
     * 
     * @return boolean
     */
    public function isCustomHtmlAllowed()
    {
        return (!(bool) PEEP::getConfig()->getValue('base', self::CONF_USER_INPUT_CUSTOM_HTML_DISABLE));
    }

    /**
     * Returns flag if rich media is allowed for user input.
     * 
     * @return boolean
     */
    public function isRichMediaAllowed()
    {
        return !((bool) PEEP::getConfig()->getValue('base', self::CONF_USER_INPUT_RICH_MEDIA_DISABLE));
    }

    /**
     * Returns flag if rich media is allowed for comments.
     * 
     * @return type 
     */
    public function isCommentsRichMediaAllowed()
    {
        return !((bool) PEEP::getConfig()->getValue('base', self::CONF_COMMENTS_RICH_MEDIA_DISABLE));
    }

    /**
     * Returns the list of allowed resources for media (iframes, objects, embed).
     * 
     * @return array
     */
    public function getMediaResourceList()
    {
        $list = PEEP::getConfig()->getValue('base', self::CONF_MEDIA_RESOURCE_LIST);

        if ( !empty($list) )
        {
            $list = json_decode($list);
        }

        return (!empty($list) && is_array($list) ) ? $list : array();
    }

    /**
     * @param null $type
     * @return array
     */
    public function getVideoParamList( $type = null )
    {
        if ( $type !== null && in_array($type, array('tags', 'attrs')) )
        {
            return $this->videoParams[$type];
        }

        return $this->videoParams;
    }

    public function processWsForOutput( $text, array $params = array() )
    {
        //hotfix for Chrome
//        $text = str_replace('<div><br></div>', '<br />', $text);
//        $text = str_replace(array('<div>', '</div>'), array('', '<br />'), $text);

        //hotfix for ie8
        $text = str_ireplace(array('<SPAN class=peep_ws_video>'), '<span class="peep_ws_video">', $text);
        
        if ( in_array(self::WS_BTN_HTML, $params['buttons']) )
        {
            $htmlResult = $this->processHtml($text);
            $text = $htmlResult['text'];
        }

        if ( in_array(self::WS_BTN_VIDEO, $params['buttons']) )
        {
            $videoResult = $this->processVideo($text);
            $text = $videoResult['text'];
        }

        // default tags list
        $tagsArray = array('br', 'span', 'blockquote', 'p');
        // default attrs list
        $attrsArray = array('class', 'p.style');

        foreach ( $params['buttons'] as $param )
        {
            if ( !empty($this->buttonTags[$param]['tags']) )
            {
                $tagsArray = array_merge($tagsArray, $this->buttonTags[$param]['tags']);
            }

            if ( !empty($this->buttonTags[$param]['attrs']) )
            {
                $attrsArray = array_merge($attrsArray, $this->buttonTags[$param]['attrs']);
            }
        }

        array_unique($tagsArray);
        array_unique($attrsArray);
        
        $text = UTIL_HtmlTag::stripTags($text, $tagsArray, $attrsArray);
        
        if ( !empty($htmlResult) )
        {
            $text = str_replace($htmlResult['search'], $htmlResult['replace'], $text);
        }
        
        if ( !empty($videoResult) )
        {
            $text = str_replace($videoResult['search'], $videoResult['replace'], $text);
        }

        if ( in_array(self::WS_BTN_MORE, $params['buttons']) )
        {
            $text = str_replace('&lt;!--more--&gt;', '<!--more-->', $text);
        }

        return $text;
    }

    public function processWsForInput( $text, array $params = array() )
    {
        //printVar($text);
//        if ( in_array('html', $params['buttons']) )
//        {
//            $htmlResult = $this->processHtml($text, false);
//            $text = str_replace($htmlResult['search'], $htmlResult['replace'], $htmlResult['text']);
//        }
//
//        if ( in_array('video', $params['buttons']) )
//        {
//            $videoResult = $this->processVideo($text, false);
//            $text = str_replace($videoResult['search'], $videoResult['replace'], $videoResult['text']);
//        }
        //printVar($videoResult);
        if ( in_array(self::WS_BTN_MORE, $params['buttons']) )
        {
            $text = str_replace('<!--more-->', '&lt;!--more--&gt;', $text);
        }
        //printVar($text);
        return $text;
    }

    public function isCodeResourceValid( $code )
    {
        return true;
    }

    private function processHtml( $text )
    {
        $searchArray = array();
        $replaceArray = array();

        $index = 1;

        while ( mb_strstr($text, '<span class="peep_ws_html">') )
        {
            $openSearchStr = '<span class="peep_ws_html">';
            $closeSearchStr = '</span>';

            $openPos = mb_stripos($text, $openSearchStr);
            $closePos = mb_stripos($text, $closeSearchStr, $openPos) + mb_strlen($closeSearchStr);
            $code = mb_substr($text, $openPos, $closePos - $openPos);

            $subCode = mb_substr($code, mb_strlen($openSearchStr));
            $subCode = mb_substr($subCode, 0, mb_strlen($subCode) - mb_strlen($closeSearchStr));

            $ph = '#h#' . $index . '#h#';
            $text = str_replace($code, $ph, $text);

            $searchArray[$index] = $ph;
            $replaceArray[$index] = $openSearchStr . UTIL_HtmlTag::stripJs($subCode) . $closeSearchStr;
            $index++;
        }

        return array('text' => $text, 'search' => $searchArray, 'replace' => $replaceArray);
    }

    private function processVideo( $text )
    {
        $searchArray = array();
        $replaceArray = array();

        $index = 1;

        while ( mb_strstr($text, '<span class="peep_ws_video">') )
        {
            $openSearchStr = '<span class="peep_ws_video">';
            $closeSearchStr = '</span>';

            $openPos = mb_stripos($text, $openSearchStr);
            $closePos = mb_stripos($text, $closeSearchStr, $openPos) + mb_strlen($closeSearchStr);
            $code = mb_substr($text, $openPos, $closePos - $openPos);

            $subCode = mb_substr($code, mb_strlen($openSearchStr));
            $subCode = mb_substr($subCode, 0, mb_strlen($subCode) - mb_strlen($closeSearchStr));

            $ph = '#v#' . $index . '#v#';
            $text = str_replace($code, $ph, $text);

            $searchArray[$index] = $ph;
            $replaceArray[$index] = $openSearchStr . UTIL_HtmlTag::stripTags($subCode, $this->videoParams['tags'], $this->videoParams['attrs']) . $closeSearchStr;
            $index++;
        }
        
        return array('text' => $text, 'search' => $searchArray, 'replace' => $replaceArray);
    }

    /**
     * Validates provided video code, stripping all restricted tags.
     *
     * @param string $code
     * @return string
     */
    public function validateVideoCode( $code )
    {
        return UTIL_HtmlTag::stripTags($code, $this->videoParams['tags'], $this->videoParams['attrs']);
    }
    
    /**
     * Adds parameter to embed code
     *
     * @param string $code
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function addVideoCodeParam( $code, $name = 'wmode', $value = 'transparent' )
    {
        $repl = $code;

        if ( preg_match("/<object/i", $code) )
        {
            $searchPattern = '<param';
            $pos = stripos($code, $searchPattern);
            if ( $pos )
            {
                $addParam = '<param name="' . $name . '" value="' . $value . '"></param><param';
                $repl = substr_replace($code, $addParam, $pos, strlen($searchPattern));
            }
        }

        if ( preg_match("/<embed/i", !empty($repl) ? $repl : $code) )
        {
            $repl = preg_replace("/<embed/i", '<embed ' . $name . '="' . $value . '"', isset($repl) ? $repl : $code);
        }
        
        $matches = array();
        if ( preg_match("/<iframe[^>]*src=['\"]([^'\"]+)['\"]/i", !empty($repl) ? $repl : $code, $matches) )
        {
            $src = null;
            if ( strpos($matches[1], "//") === 0 )
            {
                $src = "http:" . $matches[1];
                $src = PEEP::getRequest()->buildUrlQueryString($src, array($name => $value));
                $src = substr($src, 5);
            }
            else
            {
                $src = PEEP::getRequest()->buildUrlQueryString($matches[1], array($name => $value));
            }
            
            $repl = preg_replace("/(<iframe[^>]*)src=['\"]([^'\"]+)['\"]/i", "$1src=\"".$src."\"", $code);
        }

        return $repl;
    }
}
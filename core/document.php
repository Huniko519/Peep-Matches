<?php

abstract class PEEP_Document
{
    const HTML = 1;
    const AJAX = 2;
    const XML = 3;
    const JSON = 4;
//	const FEED = 3;
//	const PDF = 4;

    const APPEND_PLACEHOLDER = '###peep_postappend_placeholder###';

    /**
     * Document title.
     *
     * @var string
     */
    protected $title;

    /**
     * Document description.
     *
     * @var string
     */
    protected $description;

    /**
     * Document language.
     *
     * @var string
     */
    protected $language;

    /**
     * Document direction.
     *
     * @var string
     */
    protected $direction;

    /**
     * Document type.
     *
     * @var string
     */
    protected $type;

    /**
     * Document charset.
     *
     * @var string
     */
    protected $charset;

    /**
     * Document mime type.
     *
     * @var string
     */
    protected $mime;

    /**
     * Document assigned template
     *
     * @var string
     */
    protected $template;

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset( $charset )
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription( $description )
    {
        $description = str_replace(PHP_EOL, "", $description);
        $this->throwEvent("core.set_document_description", array("str" => $description));
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     */
    public function setDirection( $direction )
    {
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage( $language )
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param string $mime
     */
    public function setMime( $mime )
    {
        $this->mime = $mime;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle( $title )
    {
        $title = str_replace(PHP_EOL, "", $title);
        $this->throwEvent("core.set_document_title", array("str" => $title));
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType( $type )
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate( $template )
    {
        $this->template = $template;
    }

    protected function throwEvent( $name, $params = array() )
    {
        
    }

    abstract function render();
}

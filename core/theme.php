<?php

class PEEP_Theme
{
    /**
     * List of decorators available in theme.
     *
     * @var array
     */
    protected $decorators = array();
    /**
     * List of master pages available in theme.
     *
     * @var array
     */
    protected $masterPages = array();
    /**
     * List of overriden master pages.
     *
     * @var array
     */
    protected $documentMasterPages = array();
    /**
     * @var BOL_ThemeService
     */
    protected $themeService;
    /**
     * @var BOL_Theme
     */
    protected $dto;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct( BOL_Theme $dto )
    {
        $this->dto = $dto;
        $this->themeService = BOL_ThemeService::getInstance();
    }

    /**
     * Checks if theme has decorator.
     *
     * @param string $name
     * @return boolean
     */
    public function hasDecorator( $name )
    {
        return array_key_exists($name, $this->decorators);
    }

    /**
     * Checks if theme has master page.
     *
     * @param string $name
     * @return boolean
     */
    public function hasMasterPage( $name )
    {
        return array_key_exists($name, $this->masterPages);
    }

    /**
     * Returns path to decorator file.
     *
     * @param string $name
     * @return string
     */
    public function getDecorator( $name )
    {
        if ( !$this->hasDecorator($name) )
        {
            throw new InvalidArgumentException('There is no decorator `' . $name . '` in theme `' . $this->name . '` !');
        }

        return $this->decorators[$name];
    }

    /**
     * Returns path to master page file.
     *
     * @param string $name
     * @return string
     */
    public function getMasterPage( $name )
    {
        if ( !$this->hasMasterPage($name) )
        {
            throw new InvalidArgumentException('There is no master page `' . $name . '` in theme `' . $this->name . '` !');
        }

        return $this->masterPages[$name];
    }

    /**
     * Checks if theme overrides master page for document key.
     *
     * @param string $documentKey
     * @return boolean
     */
    public function hasDocumentMasterPage( $documentKey )
    {
        return array_key_exists(trim($documentKey), $this->documentMasterPages);
    }

    /**
     * Returns master page file path for document key.
     *
     * @param string $documentKey
     * @return string
     */
    public function getDocumentMasterPage( $documentKey )
    {
        if ( !$this->hasDocumentMasterPage($documentKey) )
        {
            throw new InvalidArgumentException('Cant find master page for document `' . $documentKey . '` in current theme!');
        }

        return $this->documentMasterPages[trim($documentKey)];
    }

    /**
     * Returns theme static dir path.
     *
     * @return string
     */
    public function getStaticDir( $mobile = false )
    {
        return $this->themeService->getStaticDir($this->dto->getName(), $mobile);
    }

    /**
     * Returns theme static url.
     *
     * @return string
     */
    public function getStaticUrl( $mobile = false )
    {
        return $this->themeService->getStaticUrl($this->dto->getName(), $mobile);
    }

    /**
     * Returns theme static images dir path.
     *
     * @return string
     */
    public function getStaticImagesDir( $mobile = false )
    {
        return $this->themeService->getStaticImagesDir($this->dto->getName(), $mobile);
    }

    /**
     * Returns theme static images url.
     *
     * @return string
     */
    public function getStaticImagesUrl( $mobile = false )
    {
        return $this->themeService->getStaticImagesUrl($this->dto->getName(), $mobile);
    }

    /**
     * Returns theme root dir path.
     *
     * @return string
     */
    public function getRootDir( $mobile = false )
    {
        return $this->themeService->getRootDir($this->dto->getName(), $mobile);
    }

    /**
     * Returns theme decorators dir path.
     *
     * @return string
     */
    public function getDecoratorsDir()
    {
        return $this->themeService->getDecoratorsDir($this->dto->getName());
    }

    /**
     * Returns theme master page dir path.
     *
     * @return string
     */
    public function getMasterPagesDir( $mobile = false )
    {
        return $this->themeService->getMasterPagesDir($this->dto->getName(), $mobile);
    }

    /**
     * Returns images dir path.
     *
     * @return string
     */
    public function getImagesDir( $mobile = false )
    {
        return $this->themeService->getImagesDir($this->dto->getName(), $mobile);
    }

    /**
     * @return BOL_Theme
     */
    public function getDto()
    {
        return $this->dto;
    }

    /**
     * @param array $decorators
     * @return PEEP_Theme
     */
    public function setDecorators( $decorators )
    {
        $this->decorators = $decorators;
        return $this;
    }

    /**
     * @param array $masterPages
     * @return PEEP_Theme
     */
    public function setMasterPages( $masterPages )
    {
        $this->masterPages = $masterPages;
        return $this;
    }

    /**
     * @param array $documentMasterPages
     * @return PEEP_Theme
     */
    public function setDocumentMasterPages( $documentMasterPages )
    {
        $this->documentMasterPages = $documentMasterPages;
        return $this;
    }
}
<?php


class GOOGLELOCATION_CMP_MapItem extends PEEP_Component
{
    protected $avatar = array();
    protected $content = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function setAvatar( $avatar )
    {
        $this->avatar = $avatar;
    }

    public function setContent( $content )
    {
        $this->content = $content;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('avatar', $this->avatar);
        $this->assign('content', $this->content);
    }
}
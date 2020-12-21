<?php

class BASE_CMP_OembedAttachment extends PEEP_Component
{
    protected $uniqId, $oembed;
    
    public function __construct( array $oembed, $delete = false )
    {
        parent::__construct();

        $this->oembed = $oembed;

        $this->assign('delete', $delete);
        $this->uniqId = uniqid("oe-");
        $this->assign("uniqId", $this->uniqId);
    }

    public function setDeleteBtnClass( $class )
    {
        $this->assign('deleteClass', $class);
    }

    public function setContainerClass( $class )
    {
        $this->assign('containerClass', $class);
    }

    public function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        
        $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($this->oembed["html"], "autoplay", 1);
        $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($code, "play", 1);
        
        $js->addScript('$(".peep_oembed_video_cover", "#" + {$uniqId}).click(function() { '
                . '$(".two_column", "#" + {$uniqId}).addClass("peep_video_playing"); '
                . '$(".attachment_left", "#" + {$uniqId}).html({$embed});'
                . 'PEEP.trigger("base.comment_video_play", {});'
                . 'return false; });', array(
            "uniqId" => $this->uniqId,
            "embed" => $code
        ));
        
        PEEP::getDocument()->addOnloadScript($js);
    }
    
    public function render()
    {
        if ( $this->oembed["type"] == "video" && !empty($this->oembed["html"]) )
        {
            $this->initJs();
        }
        
        $this->assign('data', $this->oembed);

        return parent::render();
    }
}
<?php


class PCGALLERY_CMP_ProfileActionToolbarMock extends BASE_CMP_ProfileActionToolbar
{
    public function __construct( $userId )
    {
        $this->setVisible(false);
    }

    public function onBeforeRender() {}
}

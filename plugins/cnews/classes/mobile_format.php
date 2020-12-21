<?php

class CNEWS_CLASS_MobileFormat extends CNEWS_CLASS_Format
{
    protected function getViewDir()
    {
        return $this->plugin->getMobileViewDir();
    }
}
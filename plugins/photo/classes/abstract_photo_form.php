<?php

abstract class PHOTO_CLASS_AbstractPhotoForm extends Form
{
    /**
     * @return array
     */
    abstract public function getOwnElements();

    public function getExtendedElements()
    {
        return array_diff(
            array_keys($this->getElements()),
            array_merge(array('form_name'), $this->getOwnElements())
        );
    }

    public function triggerReady( array $data = null )
    {
        PEEP::getEventManager()->trigger(
            new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_ON_FORM_READY, array('form' => $this), $data)
        );
    }

    public function triggerComplete( array $data = null )
    {
        PEEP::getEventManager()->trigger(
            new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_ON_FORM_COMPLETE, array('form' => $this), $data)
        );
    }
}
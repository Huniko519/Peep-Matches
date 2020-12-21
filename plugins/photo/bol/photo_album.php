<?php

class PHOTO_BOL_PhotoAlbum extends PEEP_Entity
{

    public $userId;

    public $entityType = 'user';

    public $entityId = null;

    public $name;
    
    public $description;

    public $createDatetime;

}

<?php

PEEP::getRouter()->addRoute(new PEEP_Route('view_photo', 'photo/view/:id', 'PHOTO_CTRL_Photo', 'view'));

PHOTO_CLASS_EventHandler::getInstance()->genericInit();


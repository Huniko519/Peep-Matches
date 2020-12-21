<?php


$path = PEEP::getPluginManager()->getPlugin('rategame')->getRootDir() . 'langs.zip';
PEEP::getLanguage()->importPluginLangs($path, 'rategame');

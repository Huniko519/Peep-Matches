<?php
BOL_FlagService::getInstance()->deleteByType('profilelike');
BOL_TagService::getInstance()->deleteEntityTypeTags('profilelike');
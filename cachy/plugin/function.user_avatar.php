<?php

function smarty_function_user_avatar( $params, $smarty )
{
    if( empty( $params['userId'] ) )
    {
        return '_EMPTY_USER_ID_';
    }

    $decoratorParams = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']));

    if( empty( $decoratorParams ) )
    {
        return '_USER_NOT_FOUND_';
    }

    return PEEP::getThemeManager()->processDecorator('avatar_item', $decoratorParams[$params['userId']]);
}
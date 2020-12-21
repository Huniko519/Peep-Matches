<?php

abstract class BASE_CMP_UsersWidget extends BASE_CLASS_Widget
{
    protected $forceDisplayMenu = false;
    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'users_widget.html');
        $randId = UTIL_HtmlTag::generateAutoId('base_users_widget');
        $this->assign('widgetId', $randId);

        $data = $this->getData($params);
        $menuItems = array();
        $dataToAssign = array();

        if ( !empty($data) )
        {
            foreach ( $data as $key => $item )
            {
                $contId = "{$randId}_users_widget_{$key}";
                $toolbarId = (!empty($item['toolbar']) ? "{$randId}_toolbar_{$key}" : false );

                $menuItems[$key] = array(
                    'label' => $item['menu-label'],
                    'id' => "{$randId}_users_widget_menu_{$key}",
                    'contId' => $contId,
                    'active' => !empty($item['menu_active']),
                    'toolbarId' => $toolbarId
                );

                $usersCmp = $this->getUsersCmp($item['userIds']);

                $dataToAssign[$key] = array(
                    'users' => $usersCmp->render(),
                    'active' => !empty($item['menu_active']),
                    'toolbar' => (!empty($item['toolbar']) ? $item['toolbar'] : array() ),
                    'toolbarId' => $toolbarId,
                    'contId' => $contId
                );
            }
        }
        $this->assign('data', $dataToAssign);

        $displayMenu = true;

        if( count($data) == 1 && !$this->forceDisplayMenu )
        {
            $displayMenu = false;
        }

        if ( !$params->customizeMode && ( count($data) != 1 || $this->forceDisplayMenu ) )
        {
            $menu = $this->getMenuCmp($menuItems);

            if ( !empty($menu) )
            {
                $this->addComponent('menu', $menu);
            }
        }
    }

    abstract public function getData( BASE_CLASS_WidgetParameter $params );

    protected function getIdList( $users )
    {
        $resultArray = array();

        if ( $users )
        {
            foreach ( $users as $user )
            {
                $resultArray[] = $user->getId();
            }
        }

        return $resultArray;
    }

    protected function forceDisplayMenu( $value )
    {
        $this->forceDisplayMenu = (boolean) $value;
    }

    protected function getUsersCmp( $list )
    {
        return new BASE_CMP_AvatarUserList($list);
    }

    protected function getMenuCmp( $menuItems )
    {
        return new BASE_CMP_WidgetMenu($menuItems);
    }
}
<?php

class BASE_CMP_Console extends PEEP_Component
{

    const EVENT_NAME = 'console.collect_items';

    const ALIGN_LEFT = -1;
    const ALIGN_RIGHT = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $event = new BASE_CLASS_ConsoleItemCollector(self::EVENT_NAME);
        PEEP::getEventManager()->trigger($event);
        $items = $event->getData();

        $resultItems = array();

        foreach ( $items as $item )
        {
            $itemCmp = null;
            $order = self::ALIGN_LEFT;
            if ( is_array($item) )
            {
                if ( empty($item['item']) )
                {
                    continue;
                }

                $itemCmp = $item['item'];

                $order = isset($item['order']) ? $item['order'] : self::ALIGN_LEFT;
            }
            else
            {
                $itemCmp = $item;
            }

            if ( $order == self::ALIGN_LEFT )
            {
                $order = count($resultItems);
            }

            if ( is_subclass_of($itemCmp, 'PEEP_Renderable') && $itemCmp->isVisible() )
            {
                $resultItems[] = array(
                    'item' => $itemCmp->render(),
                    'order' => $order
                );
            }
        }

        usort($resultItems, array($this, '_sortItems'));

        $tplItems = array();

        foreach ( $resultItems as $item )
        {
            $tplItems[] = $item['item'];
        }

        $this->assign('items', $tplItems);


        $jsUrl = PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'console.js';
        PEEP::getDocument()->addScript($jsUrl);

        $event = new PEEP_Event(BASE_CTRL_Ping::PING_EVENT . '.consoleUpdate');
        PEEP::getEventManager()->trigger($event);

        $params = array(
            'pingInterval' => 30000
        );

        $js = UTIL_JsGenerator::newInstance();
        $js->newObject(array('PEEP', 'Console'), 'PEEP_Console', array($params, $event->getData()));

        PEEP::getDocument()->addOnloadScript($js, 900);
    }

    public function _sortItems( $item1, $item2 )
    {
        $a = (int) $item1['order'];
        $b = (int) $item2['order'];

        if ($a == $b)
        {
            return 0;
        }

        return ($a > $b) ? -1 : 1;
    }




    /* Deprecated Block */

    const DATA_KEY_ICON_CLASS = 'icon_class';
    const DATA_KEY_URL = 'url';
    const DATA_KEY_ID = 'id';
    const DATA_KEY_BLOCK = 'block';
    const DATA_KEY_BLOCK_ID = 'block_id';
    const DATA_KEY_ITEMS_LABEL = 'block_items_count';
    const DATA_KEY_BLOCK_CLASS = 'block_class';
    const DATA_KEY_TITLE = 'title';
    const DATA_KEY_HIDDEN_CONTENT = 'hidden_content';

    const VALUE_BLOCK_CLASS_GREEN = 'peep_mild_green';
    const VALUE_BLOCK_CLASS_RED = 'peep_mild_red';

}
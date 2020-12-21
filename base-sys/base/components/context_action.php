<?php

class BASE_CMP_ContextAction extends PEEP_Component
{
    const POSITION_LEFT = 'peep_tooltip_top_left';
    const POSITION_RIGHT = 'peep_tooltip_top_right';

    private $position;

    private $actions = array();

    public function __construct( $position = self::POSITION_RIGHT )
    {
        parent::__construct();

        $this->position = $position;

        $script = '$(document).on("hover", ".peep_context_action",function(e) {
                        if (e.type == "mouseenter") {
                            $(this).find(".peep_tooltip").css({opacity: 0, top: 10}).show().stop(true, true).animate({top: 18, opacity: 1}, "fast"); 
                        }
                        else { // mouseleave
                            $(this).find(".peep_tooltip").hide();  
                        }     
                    }
                );';

        PEEP::getDocument()->addOnloadScript($script);
    }

    public function addAction( BASE_ContextAction $action )
    {
        if ( $action->getParentKey() == null )
        {
            $this->actions[$action->getKey()]['action'] = $action;
        }
        else
        {
            if ( !empty($this->actions[$action->getParentKey()]) )
            {
                $this->actions[$action->getParentKey()]['subactions'][$action->getKey()] = $action;
            }
        }

        if ( $action->getOrder() === null )
        {
            $order = $action->getParentKey() === null
                ? count($this->actions)
                : count($this->actions[$action->getParentKey()]['subactions']);

            $action->setOrder($order);
        }
    }

    public function sortActionsCallback( $a1, $a2 )
    {
        $o1 = $a1->getOrder();
        $o2 = $a2->getOrder();

        $o1 = $o1 === null ? 0 : $o1;
        $o2 = $o2 === null ? 0 : $o2;

        if ($o1 == $o2)
        {
            return 0;
        }

        if ( $o1 === -1 )
        {
            return 1;
        }

        if ( $o2 === -1 )
        {
            return -1;
        }

        return ($o1 < $o2) ? -1 : 1;
    }

    public function setClass( $class )
    {
        $this->assign("class", $class);
    }
    
    public function sortParentActionsCallback( $a1, $a2 )
    {
        return $this->sortActionsCallback($a1['action'], $a2['action']);
    }

    public function render()
    {
        if ( !count($this->actions) )
        {
            $this->setVisible(false);
        }
        else
        {
            $visible = true;
            foreach ( $this->actions as & $action )
            {
                if ( empty($action['subactions']) && !$action['action']->getLabel() )
                {
                    $visible = false;
                    break;
                }

                if ( !empty($action['subactions']) )
                {
                    usort($action['subactions'], array($this, 'sortActionsCallback'));
                }
            }

            $this->setVisible($visible);
        }

        usort($this->actions, array($this, 'sortParentActionsCallback'));

        $this->assign('actions', $this->actions);

        $this->assign('position', $this->position);

        return parent::render();
    }
}

class BASE_ContextAction
{
    private $key;

    private $label;

    private $url;

    private $id;

    private $class;

    private $order;

    private $parentKey;

    private $attributes = array();

    public function __construct() { }

    public function setKey( $key )
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setLabel( $label )
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setUrl( $url )
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setId( $id )
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setClass( $class )
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setOrder( $order )
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setParentKey( $parentKey )
    {
        $this->parentKey = $parentKey;
    }

    public function getParentKey()
    {
        return $this->parentKey;
    }

    public function addAttribute( $name, $value )
    {
        $this->attributes[$name] = $value;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}
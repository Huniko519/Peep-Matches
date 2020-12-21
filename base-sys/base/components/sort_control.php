<?php

class BASE_CMP_SortControl extends PEEP_Component
{
    const ITEM_LABEL = 'label';
    const ITEM_URL = 'url';
    const ITEM_ISACTIVE = 'isActive';
    
    public $sortItems = array();
    /**
     * Constructor.
     *
     */
    public function __construct( array $sortItems = array() )
    {
        parent::__construct();
        
        if (!empty($sortItems))
        {
            $this->setSortItems($sortItems);
        }
        
        $this->assign('itemList', $this->sortItems);
    }

    public function addItem($sortOrder, $label, $url, $isActive = false)
    {
        $this->sortItems[$sortOrder] = array(
            self::ITEM_LABEL => $label,
            self::ITEM_URL => $url,
            self::ITEM_ISACTIVE => $isActive
        );
    }
    
    public function setActive($sortOrder)
    {
        $this->sortItems[$sortOrder]['isActive'] = true;
    }
    
    public function setSortItems(array $sortItems)
    {
        $this->sortItems = $sortItems;
    }
    
    public function render() {
        
        if (empty($this->sortItems))
        {
            $this->setVisible(false);
        }
        
        $this->assign('itemList', $this->sortItems);
        
        return parent::render();
    }
    
     
}
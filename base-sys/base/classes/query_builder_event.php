<?php

class BASE_CLASS_QueryBuilderEvent extends PEEP_Event
{
    const FIELD_USER_ID = "userId";
    const FIELD_CONTENT_ID = "contentId";
    
    const TABLE_USER = "user";
    const TABLE_CONTENT = "content";
    
    const WHERE_AND = "where-and";
    const WHERE_OR = "where-or";
    
    const ORDER_ASC = "ASC";
    const ORDER_DESC = "DESC";
    
    const OPTION_TYPE = "type";
    const OPTION_METHOD = "method";
    
    public function __construct( $name, array $options = array() ) 
    {
        parent::__construct($name, $options);
        
        $this->data = array(
            "join" => array(),
            "where" => array(),
            "order" => array(),
            "params" => array()
        );
    }
    
    public function addJoin( $join )
    {
        $this->data["join"][] = $join;
    }
    
    public function getJoinList()
    {
        return $this->data["join"];
    }
    
    public function getJoin()
    {
        return implode(" ", $this->getJoinList());
    }
    
    public function addWhere( $condition )
    {
        $this->data["where"][] = $condition;
    }
    
    public function getWhereList()
    {
        return $this->data["where"];
    }
    
    public function getWhere( $type = self::WHERE_AND )
    {
        $whereList = $this->getWhereList();
        
        if ( empty($whereList) )
        {
            return "1";
        }
        
        return "(" . implode( $type == self::WHERE_AND ? ") AND (" : ") OR (", $this->getWhereList() ) . ")";
    }

    public function addOrder( $field, $order = self::ORDER_ASC )
    {
        $this->data["order"][$field] = $order;
    }

    public function getOrderList()
    {
        return $this->data["order"];
    }

    public function getOrder()
    {
        $orderList = $this->getOrderList();

        if ( empty($orderList) )
        {
            return "";
        }

        $sep = "";
        $orderStr = "";
        foreach ( $orderList as $field => $order )
        {
            $orderStr .= $sep . $field . " " . $order;
            $sep = ", ";
        }

        return $orderStr;
    }

    public function addQueryParam( $key, $value )
    {
        $this->data['params'][$key] = $value;
    }

    public function addBatchQueryParams( $params )
    {
        $this->data['params'] = array_merge($this->data['params'], $params);
    }

    public function getQueryParams()
    {
        return $this->data['params'];
    }
}
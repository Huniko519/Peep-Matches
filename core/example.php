<?php

class PEEP_Example
{
    /**
     * Query insert string.
     *
     * @var string
     */
    protected $criteriaString;
    /**
     * Query limit clause string.
     *
     * @var string
     */
    protected $limitClauseString;
    /**
     * Query order clause string.
     *
     * @var string
     */
    protected $orderClauseString;
    /**
     * DB Object.
     *
     * @var PEEP_Database
     */
    protected $dbo;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dbo = PEEP::getDbo();
        $this->criteriaString = '';
    }

    /**
     * Adds limit clause to query string.
     *
     * @param integer $count
     * @param integer $offset
     * @return PEEP_Example
     */
    public function setLimitClause( $first, $count )
    {
        $this->limitClauseString = 'LIMIT ' . (int) $first . ', ' . (int) $count;

        return $this;
    }

    /**
     * Adds order clause to query string
     * @example $obj->setOrder( '`myField`' ) | $obj->setOrder( '`myField` DESC' )
     *
     * @param string $orderString
     * @return PEEP_Example
     */
    public function setOrder( $orderString )
    {
        $this->orderClauseString = 'ORDER BY ' . $this->dbo->escapeString($orderString);
        return $this;
    }

    /**
     * Adds field equal clause to query.
     *
     * @param string $field
     * @param mixed $value
     * @return PEEP_Example
     */
    public function andFieldEqual( $field, $value )
    {
        if ( is_bool($value) )
        {
            $value = intval($value);
        }

        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` = " . (is_string($value) ? "'" . $this->dbo->escapeString($value) . "'" : $value);
        return $this;
    }

    /**
     * Adds field like clause to query.
     *
     * @param string $field
     * @param string $value
     * @return PEEP_Example
     */
    public function andFieldLike( $field, $value )
    {
        if ( is_bool($value) )
        {
            $value = intval($value);
        }

        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` LIKE '" . $this->dbo->escapeString($value) . "'";

        return $this;
    }

    /**
     * Adds field between clause to query.
     *
     * @param string $field
     * @param mixed $value1
     * @param mixed $value2
     * @return PEEP_Example
     */
    public function andFieldBetween( $field, $value1, $value2 )
    {
        if ( !is_numeric($value1) || !is_numeric($value2) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` BETWEEN " . $value1 . " AND " . $value2;
        return $this;
    }

    /**
     * Adds field not equal clause to query.
     *
     * @param string $field
     * @param mixed $value
     * @return PEEP_Example
     */
    public function andFieldNotEqual( $field, $value )
    {
        if ( is_bool($value) )
        {
            $value = intval($value);
        }
        
        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` != " . (is_string($value) ? "'" . $this->dbo->escapeString($value) . "'" : $value);
        return $this;
    }

    /**
     * Adds field greater than clause to query.
     *
     * @param string $field
     * @param mixed $value
     * @return PEEP_Example
     */
    public function andFieldGreaterThan( $field, $value )
    {
        if ( !is_numeric($value) )
        {
            throw new InvalidArgumentException("Not numeric param was provided! Number is expected!");
        }

        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` > " . $value;
        return $this;
    }

    /**
     * Adds field greater than or equal clause to query.
     *
     * @param string $field
     * @param mixed $value
     * @return PEEP_Example
     */
    public function andFieldGreaterThenOrEqual( $field, $value )
    {
        if ( !is_numeric($value) )
        {
            throw new InvalidArgumentException("Not numeric param was provided! Number is expected!");
        }

        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` >= " . $value;
        return $this;
    }

    /**
     * Adds field less clause to query.
     *
     * @param string $field
     * @param mixed $value
     * @return PEEP_Example
     */
    public function andFieldLessThan( $field, $value )
    {
        if ( !is_numeric($value) )
        {
            throw new InvalidArgumentException("Not numeric param was provided! Number is expected!");
        }

        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` < " . $value;
        return $this;
    }

    /**
     * Adds field less or equal clause to query.
     *
     * @param string $field
     * @param mixed $value
     * @return PEEP_Example
     */
    public function andFieldLessOrEqual( $field, $value )
    {
        if ( !is_numeric($value) )
        {
            throw new InvalidArgumentException("Not numeric param was provided! Number is expected!");
        }

        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` <= " . $value;
        return $this;
    }

    /**
     * Adds field is null clause to query.
     *
     * @param string $field
     * @param mixed $value
     * @return PEEP_Example
     */
    public function andFieldIsNull( $field )
    {
        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` IS NULL";
        return $this;
    }

    /**
     * Adds field is not null clause to query.
     *
     * @param string $field
     * @param mixed $value
     * @return PEEP_Example
     */
    public function andFieldIsNotNull( $field )
    {
        $this->criteriaString .= " AND `" . $this->dbo->escapeString($field) . "` IS NOT NULL";
        return $this;
    }

    /**
     * @param string $field
     * @param array $valueList
     * @return PEEP_Example
     */
    public function andFieldInArray( $field, array $valueList )
    {
        $result = $this->dbo->mergeInClause($valueList);
        $this->criteriaString .= ' AND `' . $this->dbo->escapeString($field) . '` IN(' . $result . ')';
        return $this;
    }

    /**
     * @param string $field
     * @param array $valueList
     * @return PEEP_Example
     */
    public function andFieldNotInArray( $field, array $valueList )
    {
        $result = $this->dbo->mergeInClause($valueList);
        $this->criteriaString .= ' AND `' . $this->dbo->escapeString($field) . '` NOT IN(' . $result . ')';
        return $this;
    }

    /**
     * @param array $fields
     * @param string $value
     * @return PEEP_Example
     */
    public function andFieldMatchAgainst( array $fields, $value )
    {
        $fieldsString = '';

        foreach ( $fields as $field )
        {
            $fieldsString = '`' . $this->dbo->escapeString($field) . '`,';
        }

        $fieldsString = mb_substr($fieldsString, 0, -1);

        $this->criteriaString .= ' AND MATCH(' . $fieldsString . ') AGAINST (\'' . $this->dbo->escapeString($value) . '\')';

        return $this;
    }

    /**
     * Magic function - compiles and returns result query string.
     *
     * @return string
     */
    public function __toString()
    {
        if ( $this->criteriaString !== null )
        {
            $criteriaString = trim($this->criteriaString);
            if ( mb_strlen($criteriaString) > 2 )
            {
                $criteriaString = ' WHERE ' . mb_substr($criteriaString, 3);
            }
            else
            {
                $criteriaString = '';
            }
        }
        else
        {
            $criteriaString = '';
        }

        return $criteriaString . ($this->orderClauseString !== null ? ' ' . $this->orderClauseString : '') . ($this->limitClauseString !== null ? ' ' . $this->limitClauseString : '');
    }
}


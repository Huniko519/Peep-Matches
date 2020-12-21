<?php

class BASE_CLASS_UserQuestionForm extends Form
{

    /**
     * Return form element presentation class
     *
     * @param string $presentation
     * @param string $questionName
     *
     * @return FormElement
     */
    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        return BOL_QuestionService::getInstance()->getPresentationClass($presentation, $questionName, $configs);
    }

    /**
     * Set question label
     *
     * @param FormElement $formField
     * @param array $question
     */
    protected function setLabel( $formField, $question )
    {
        $label = $formField->getLabel();

        if ( empty($label) )
        {
            $formField->setLabel(PEEP::getLanguage()->text('base', 'questions_question_' . $question['name'] . '_label'));
        }
    }

    /**
     * Add user questions to form
     *
     * $questionList[section_name][question]
     * @param array $questionList
     * @param array $questionValueList
     *
     * @return BASE_UserQuestionForm
     */
    public function addQuestions( $questionList, $questionValueList = array(), $questionData = array() )
    {
        foreach ( $questionList as $key => $question )
        {
            $custom = isset($question['custom']) ? $question['custom'] : null;

            $formField = $this->getPresentationClass($question['presentation'], $question['name'], $custom);


            $formField->setLabel(PEEP::getLanguage()->text('base', 'questions_question_' . $question['name'] . '_label'));
            $this->setLabel($formField, $question);

            if ( in_array($question['type'], array( BOL_QuestionService::QUESTION_VALUE_TYPE_MULTISELECT, BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT) ) 
                && method_exists($formField, 'setColumnCount') )
            {
                $this->setColumnCount($formField, $question);
            }

            // set field options
            if ( isset($questionValueList[$question['name']]) && method_exists($formField, 'setOptions') )
            {
                $this->setFieldOptions($formField, $question['name'], $questionValueList[$question['name']]);
            }

            // set field value
            if ( isset($questionData[$question['name']]) && $question['presentation'] !== BOL_QuestionService::QUESTION_PRESENTATION_PASSWORD )
            {
                $this->setFieldValue($formField, $question['presentation'], $questionData[$question['name']]);
            }

            $this->addFieldValidator($formField, $question);

            $formField->setRequired((string) $question['required'] === '1');

            $this->addElement($formField);
        }

        return $this;
    }

    /**
     * Set field value
     *
     * @param FormElement $formField
     * @param string $presentation
     * @param string $value
     */
    protected function setFieldValue( $formField, $presentation, $value )
    {
        $value = BOL_QuestionService::getInstance()->prepareFieldValue($presentation, $value);
        $formField->setValue($value);
    }

    /**
     * Set field options
     *
     * @param FormElement $formField
     * @param string $questionName
     * @param array<BOL_QuestionValue> $questionValues
     */
    protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        $valuesArray = array();

        foreach ( $questionValues as $values )
        {
            if ( is_array($values) )
            {
                foreach ( $values as $value )
                {
                    $valuesArray[($value->value)] = BOL_QuestionService::getInstance()->getQuestionValueLang($value->questionName, $value->value);
                }
            }
        }

        $formField->setOptions($valuesArray);
    }

    /**
     * Return acount types array
     *
     * @param FormElement $formField
     * @param array $question
     */
    protected function getAccountTypes()
    {
        // get available account types from DB
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

        $accounts = array();



        /* @var $value BOL_QuestionAccount */
        foreach ( $accountTypes as $key => $value )
        {
            $accounts[$value->name] = PEEP::getLanguage()->text('base', 'questions_account_type_' . $value->name);
        }

        return $accounts;
    }

    public function setColumnCount( $formElement, $question )
    {
        $formElement->setColumnCount($question['columnCount']);
    }

    /**
     * Set field validator
     *
     * @param FormElement $formField
     * @param array $question
     */
    protected function addFieldValidator( $formField, $question )
    {

    }
}


<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CMP_SetSuspendMessage extends BASE_CMP_SetSuspendMessage
{
    /**
     * @return Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate( PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'set_suspend_message.html' );
        
        
    }
    
    protected function bindJs( $form )
    {
        $form->bindJsFunction(Form::BIND_SUBMIT, ' function(e) { 
                var form = $("#user-list-form");
                
                if ( form && form.length > 0 )
                {
                    var message = $("<input type=\'hidden\' name=\'suspend_message\' >");
                    message.val(e.message);
                    
                    var suspend = $("<input type=\'hidden\' name=\'suspend\' value=\'1\' >");
                    
                    form.append(message);
                    form.append(suspend);
                    
                    var floatbox = PEEP.getActiveFloatBox();

                    if ( floatbox )
                    {
                        floatbox.close();
                    }
                    
                    form.submit();
                }
                
                return false;
        } ');
    }
}

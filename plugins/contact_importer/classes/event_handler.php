<?php

class CONTACTIMPORTER_CLASS_EventHandler
{

    const EVENT_COLLECT_PROVIDERS = 'contactimporter.collect_providers';
    const EVENT_RENDER_BUTTON = 'contactimporter.render_button';

    private $providers = array();

   private static $classInstance;

public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {

        $this->providers['facebook'] = new CONTACTIMPORTER_CLASS_FacebookProvider();
        $this->providers['google'] = new CONTACTIMPORTER_CLASS_GoogleProvider();
        $this->providers['email'] = new CONTACTIMPORTER_CLASS_EmailProvider();
    }

    public function collectProviders( BASE_CLASS_EventCollector $event )
    {
        foreach ( $this->providers as $p )
        {
            $event->add($p->getProviderInfo());
        }
    }

    public function buttonRender( PEEP_Event $event )
    {
        $params = $event->getParams();
        $key = $params['provider'];

        if ( empty ($this->providers[$key]) )
        {
            return;
        }

        /* @var $provider CONTACTIMPORTER_CLASS_Provider */
        $provider = $this->providers[$key];
        $data = $provider->prepareButton($params);

        $event->setData($data);
    }

    public function onUserRegister( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['params']['code']) )
        {
            return;
        }

	$userIds = array();

	foreach ( $this->providers as $provider )
	{
	    $inviters = $provider->getInviters($params['params']['code']);
	    if ( $inviters && is_array($inviters) )
	    {
		$userIds = array_merge($userIds, $inviters);
	    }
	}
        
        $newId = $params['userId'];

	foreach ( $userIds as $uid )
	{
            $event = new PEEP_Event('friends.add_friend', array(
                'requesterId' => $uid,
                'userId' => $newId
            ));

            PEEP::getEventManager()->trigger($event);

	    $eventParams = array('pluginKey' => 'contactimporter', 'action' => 'import_friend', 'userId' => $userId);

	    
	}
    }
    
    public function onJoinFormRender( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['code']) )
        {
            $data = base64_decode($params['code']);
            $data = json_decode($data, true);
            
            if ( !empty($data['inviters']) )
            {
                throw new JoinRenderException();
            }
        }
    }
 public function genericInit()
    {
      PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_REGISTER, array($this, 'onUserRegister'));

       
        
    }

}

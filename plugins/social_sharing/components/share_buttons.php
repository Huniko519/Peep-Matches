<?php

class SOCIALSHARING_CMP_ShareButtons extends PEEP_Component
{
    protected $class = "";
    protected $title = null;
    protected $description = null;
    protected $url = null;
    protected $imageUrl = null;
    protected $displayBlock = true;
  
    public function __construct( $params = array() )
    {

        $staticUrl = PEEP::getPluginManager()->getPlugin('socialsharing')->getStaticUrl();
        $document = PEEP::getDocument();
        $document->addScript($staticUrl . 'js/popup.js');

        $this->imageUrl = SOCIALSHARING_BOL_Service::getInstance()->getDefaultImageUrl();

        if ( !PEEP::getConfig()->getValue('socialsharing', 'api_key') )
        {
            $this->setVisible(false);
        }

        if ( PEEP::getConfig()->getValue('base', 'guests_can_view') != 1 || PEEP::getConfig()->getValue('base', 'maintenance'))
        {
            $this->setVisible(false);
        }

        parent::__construct();


        if ( !empty($params['title']) )
        {
            $this->setTitle($params['title']);
        }

        if ( !empty($params['description']) )
        {
            $this->setDescription($params['description']);
        }

        if ( !empty($params['url']) )
        {
            $this->setCustomUrl($params['url']);
        }

        if ( !empty($params['image']) )
        {
            $this->setImageUrl($params['image']);
        }
    }

    public function setCustomUrl( $url )
    {
        if ( !empty($url) )
        {
            $this->url = strip_tags($url);
        }


    }

    public function setDescription( $description )
    {
        if ( !empty($description) )
        {
            $this->description = strip_tags($description);
        }
    }

    public function setTitle( $title )
    {
        if ( !empty($title) )
        {
            $this->title = strip_tags($title);
        }
    }

    public function setImageUrl( $url )
    {
        if ( !empty($url) )
        {
            $this->imageUrl = strip_tags($url);
        }
    }

    public function setDisplayBlock( $value )
    {
        $this->displayBlock = (boolean) $value;

        if ( $value )
        {
            $this->setBoxClass( 'peep_social_sharing_box' );
        }
    }

    public function onBeforeRender()
    {
        $config = PEEP::getConfig();

		$apiKey = $config->getValue('socialsharing', 'api_key');

        if ( empty($apiKey) )
        {
            $this->setVisible(false);
        }
		else
		{
			$order = $config->getValue('socialsharing', 'order');
			$defautOrder = SOCIALSHARING_CLASS_Settings::getEntityList();

			if ( !empty($order) )
			{
				$order = json_decode($order, true);

				if( !is_array($order) )
				{
					$order = $defautOrder;
				}

				$result = array();
				foreach ( $order as $key => $item )
				{
					if ( in_array($key, $defautOrder) )
					{
						$result[$key] = $key;
					}
				}

				if ( !empty($order) )
				{
					$order = $result;
				}
				else
				{
					$order = $defautOrder;
				}
			}
			else
			{
				$order = $defautOrder;
			}

			foreach ( $order as $key => $item )
			{
				$var = $config->getValue('socialsharing', $item);

				if ( empty($var) )
				{
					unset($order[$key]);
				}
			}

			$id = uniqid(rand(0,999999));
			$this->assign('id', $id);

			PEEP::getDocument()->addStyleSheet(PEEP::getPluginManager()->getPlugin('socialsharing')->getStaticCssUrl().'style.css');


			 $script = "";

			if ( !empty($this->imageUrl) )
			{
				PEEP::getDocument()->addMetaInfo('image', $this->imageUrl, 'itemprop');
				PEEP::getDocument()->addMetaInfo('og:image', $this->imageUrl, 'property');

				$script .= " image: ". json_encode($this->imageUrl) .", ";

			}

			if ( !empty( $this->url ) )
			{
				$script .= " url: ". json_encode($this->url) .",";
				PEEP::getDocument()->addMetaInfo('og:url', $this->url, 'property');
			}

			if ( !empty( $this->description ) )
			{
                $description = strip_tags($this->description);
                $description = UTIL_String::truncate($description, 255, '...');
                $script .= " description: ". json_encode($description) .",";
				PEEP::getDocument()->addMetaInfo('og:description', $this->description, 'property');
			}

			if ( !empty( $this->title ) )
			{
				$script .= " title: ". json_encode($this->title) .",";
				PEEP::getDocument()->addMetaInfo('og:title', $this->title, 'property');
			}

			PEEP::getDocument()->addScript('//s7.addthis.com/js/300/addthis_widget.js#pubid='.urlencode(PEEP::getConfig()->getValue('socialsharing', 'api_key')).'&async=1');

			$script = substr($script, 0, -1);

			PEEP::getDocument()->addOnloadScript("
				var addthis_share  =
				{
					{$script}
				};
				addthis.init();
				addthis.toolbox('.addthis_toolbox', {}, addthis_share);
			");

			$this->assign('url', $this->url);
			$this->assign('title', $this->title);
			$this->assign('description', $this->description);

			$this->assign('script', $script);
			$this->assign('order', $order);

			$this->assign('class', $this->class);
			$this->assign('imageUrl', $this->imageUrl);

			if ( $this->displayBlock )
			{
				$this->setTemplate(PEEP::getPluginManager()->getPlugin('socialsharing')->getCmpViewDir().'share_buttons_block.html');
			}
		}

        return parent::onBeforeRender();
    }

    public function setBoxClass( $class )
    {
        $this->class = ( !empty($this->class) ? $this->class . ' ' . $class : $class );
    }
}


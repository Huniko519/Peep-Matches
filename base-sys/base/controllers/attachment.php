<?php

class BASE_CTRL_Attachment extends PEEP_ActionController
{
    /**
     * @var BOL_AttachmentService
     */
    private $service;

    public function __construct()
    {
        $this->service = BOL_AttachmentService::getInstance();
    }

    public function delete( $params )
    {
        exit;
    }

    public function addLink()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $url = $_POST['url'];

        $urlInfo = parse_url($url);
        if ( empty($urlInfo['scheme']) )
        {
            $url = 'http://' . $url;
        }

        $url = str_replace("'", '%27', $url);

        $oembed = UTIL_HttpResource::getOEmbed($url);
        $oembedCmp = new BASE_CMP_AjaxOembedAttachment($oembed);

        $attacmentUniqId = $oembedCmp->initJs();

        unset($oembed['allImages']);

        $response = array(
            'content' => $this->getMarkup($oembedCmp->render()),
            'type' => 'link',
            'result' => $oembed,
            'attachment' => $attacmentUniqId
        );

        echo json_encode($response);

        exit;
    }

    private function getMarkup( $html )
    {
        /* @var $document PEEP_AjaxDocument */
        $document = PEEP::getDocument();

        $markup = array();
        $markup['html'] = $html;

        $onloadScript = $document->getOnloadScript();
        $markup['js'] = empty($onloadScript) ? null : $onloadScript;

        $styleDeclarations = $document->getStyleDeclarations();
        $markup['css'] = empty($styleDeclarations) ? null : $styleDeclarations;

        return $markup;
    }
    /* 1.6.1 divider */

    public function addPhoto( $params )
    {
        $resultArr = array('result' => false, 'message' => 'General error');
        $bundle = $_GET['flUid'];

        if ( PEEP::getUser()->isAuthenticated() && !empty($_POST['flUid']) && !empty($_POST['pluginKey']) && !empty($_FILES['attachment']) )
        {
            $pluginKey = $_POST['pluginKey'];
            $item = $_FILES['attachment'];

            try
            {
                $dtoArr = $this->service->processUploadedFile($pluginKey, $item, $bundle, array('jpg', 'jpeg', 'png', 'gif'), 2000);
                $resultArr['result'] = true;
                $resultArr['url'] = $dtoArr['url'];
            }
            catch ( Exception $e )
            {
                $resultArr['message'] = $e->getMessage();
            }
        }

        exit("<script>if(parent.window.peepPhotoAttachment['" . $bundle . "']){parent.window.peepPhotoAttachment['" . $bundle . "'].updateItem(" . json_encode($resultArr) . ");}</script>");
    }

    public function addFile()
    {
        $respondArr = array();
        $bundle = $_GET['flUid'];
        if ( PEEP::getUser()->isAuthenticated() && !empty($_POST['flData']) && !empty($_POST['pluginKey']) && !empty($_FILES['peep_file_attachment']) )
        {
            $respondArr['noData'] = false;
            $respondArr['items'] = array();
            $nameArray = json_decode(urldecode($_POST['flData']), true);
            $pluginKey = $_POST['pluginKey'];

            $finalFileArr = array();

            foreach ( $_FILES['peep_file_attachment'] as $key => $items )
            {
                foreach ( $items as $index => $item )
                {
                    if ( !isset($finalFileArr[$index]) )
                    {
                        $finalFileArr[$index] = array();
                    }

                    $finalFileArr[$index][$key] = $item;
                }
            }

            foreach ( $finalFileArr as $item )
            {
                try
                {
                    $dtoArr = $this->service->processUploadedFile($pluginKey, $item, $bundle);
                    $respondArr['result'] = true;
                }
                catch ( Exception $e )
                {
                    $respondArr['items'][$nameArray[$item['name']]] = array('result' => false, 'message' => $e->getMessage());
                }

                if ( !array_key_exists($nameArray[$item['name']], $respondArr['items']) )
                {
                    $respondArr['items'][$nameArray[$item['name']]] = array('result' => true, 'dbId' => $dtoArr['dto']->getId());
                }
            }

            $items = $this->service->getFilesByBundleName($pluginKey, $bundle);

            PEEP::getEventManager()->trigger(new PEEP_Event('base.attachment_uploaded', array('pluginKey' => $pluginKey, 'uid' => $bundle, 'files' => $items)));
        }
        else
        {
            $respondArr = array('result' => false, 'message' => 'General error', 'noData' => true);
        }

        exit("<script>if(parent.window.peepFileAttachments['" . $bundle . "']){parent.window.peepFileAttachments['" . $bundle . "'].updateItems(" . json_encode($respondArr) . ");}</script>");
    }

    public function deleteFile()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            exit;
        }

        $fileId = !empty($_POST['id']) ? (int) $_POST['id'] : -1;
        $this->service->deleteAttachment(PEEP::getUser()->getId(), $fileId);

        exit;
    }
}

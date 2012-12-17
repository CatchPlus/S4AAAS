<?php

/**
 * Description of MonkController
 *
 * @author Postie
 */
 
include dirname(__FILE__) . '/../config/Config.expert.php';
 
class MonkController {

    private $monk;
    
    public function __construct()
    {
        $this->monk = new Monk(Config::BASEURL . Config::SEARCH_SUFFIX);

        /* create the smarty template for the index */
    }

    /**
     * Main program that starts the monk search.
     */
    public function run() {
        $template = new Smarty();
        $template->compile_check = true;
        $host = PathUtils::getHostType();
        
        if(isset($_SESSION['token'])){
            $template->assign('tokenIsSet', true);
            $template->assign('username', $_SESSION['username']);
        }else{
            $template->assign('tokenIsSet', false);
        }
        
        if($host == 'expert')
            $template->assign('showTopMenu', false);
        else
            $template->assign('showTopMenu', true);
        $template->assign('version', MText::_($host));
        $template->assign('baseurl', PathUtils::getUrlPath(true));
        $template->assign('institutions', $this->monk->getInstitutions());
        $template->assign('defaultInstitution', 'all');

        $template->assign('collections', $this->monk->getCollections());
        $template->assign('defaultCollection', 'all');

        $template->assign('books', $this->monk->getBooks());
        $template->assign('defaultBook', 'all');

        $template->assign('matches', Config::$matches);
        $template->assign('defaultMatch', Config::$matches['prefix']);

        $template->assign('annotation', Config::$annotations);
        $template->assign('defaultAnnotations', Config::$annotations);


        $template->assign('wordzonetypes', Config::$wordZoneTypes);
        $template->assign('defaultWordzoneTypes', 'HUMAN|JAVA');

        $template->assign('anchors', Config::$toolTipAnchors);
        $template->assign('wikiurl', Config::WIKI_URL);

        $template->assign('wordzonelabel', MText::_('wordzonelabel'));
        $template->assign('annotationlabel', MText::_('annotationlabel'));
        $template->assign('matchlabel', MText::_('match'));

        $template->display('templates/index.tpl');
    }

    /**
     * Retrieves the book collection from the Monk server and presents it as a JSON formatted string
     * @return JSON
     */
    public function collectionAsJSON()
    {

        return $this->monk->collectionAsJson();
    }
}

?>

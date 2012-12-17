<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/7/11
 * Time: 3:32 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class Application {

    public function run()
    {
        /* constructor of monkauth will automatically redirect if there are no login credentials */
        $auth = new MonkAuth();
        $page = '';
        $cmd  = '';
        $line = null;

        if(isset($_GET['cmd']))
            $cmd = $_GET['cmd'];
        if(isset($_GET['page']))
            $page = $_GET['page'];
        if(isset($_GET['line']))
            $line = $_GET['line'];


        try {
            switch($cmd)
            {
                case 'read':
                    $reader = new MonkReader();
                    if(!isset($_SESSION[Config::PAGE_STORE]))
                        $reader->readPage();
                    echo $reader->asJSON();
                    break;
                case 'readlabel':
                    $reader = new MonkReader();
                    echo $reader->lineLabelsAsJSON();
                    break;
                case 'save':
                    $user = MonkUser::getInstance();
                    // no more roles on local side
//                    $role = $user->getRole();
//                    if($role | UserRole::VERIFIER)
//                    {
//                        $saver = new MonkSaver();
//                        $saver->savePage();
//                    }
                        $saver = new MonkSaver();
                        $saver->savePage();
                    break;
                case 'transcribe':
                    /* if $page constains username.bookid, load the files from local file system */
                    $pageId = explode('.', $page);
                    switch(count($pageId))
                    {
                        case 1:
                            $reader = new MonkReader();
                            break;
                        case 2:
                            $reader = new MonkLoader();
                            break;
                        default:
                            $reader = new MonkReader();
                    }

//                    $reader->readPage();
                    $reader->readPageWithRest();

                    $transcribe = new TranscribePage();
                    $transcribe->render();
                    break;
                case 'delete':
                    $reader = new MonkLoader();
                    $reader->removeFile();
                    break;
                case 'shear': // unused,
                    if(isset($_SESSION[Config::PAGE_STORE]))
                    {
                        $page = $_SESSION[Config::PAGE_STORE];
                        if($page instanceof Page)
                            echo $page->shearAsJSON();
                        else
                            echo json_encode(array());
                    }
                    else
                    {
                        echo json_encode(array());
                    }

                    break;
                case 'image':
                    if($line)
                    {
                        $test = new ImageLoader($line);
                        $test->getImage();
                    }
                    break;

                default:
                    /* no command given, show homepage */
                    $auth->init();
                    $index = new IndexPage();
                    $index->render();
            }
        }
        // TODO throw and catch custom exceptions
        catch(Exception $e)
        {
            // when logged in, show index page after exception
            $_SESSION['errorMessage'] = $e->getMessage();
            if($auth->isAuthorized())
            {
                $auth->init();
                $index = new IndexPage();
                $index->render();
            }
            // otherwise show index page
            else
            {
                $loginPage = new LoginPage();
                $loginPage->render();
            }

        }
    }

}

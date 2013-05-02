<?php

namespace Dan\CommonBundle\Test;

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    
    protected function getFixturesToLoad()
    {
        return array(
             'Dan\UserBundle\DataFixtures\ORM\LoadUserData'
        );
    }
    
    public function setUp()
    {
        $this->loadFixtures($this->getFixturesToLoad());
    }
    
    public function tearDown()
    {
        $this->clearDocFilesDir();
    }
    
    /**
     * Show the current response in Chrome
     *
     * @param Client $client 
     */
    protected function showInBrowser($client)
    {
        $kernel = $client->getKernel();
        file_put_contents($kernel->getRootDir().'/cache/output.html', $client->getResponse()->getContent());
        exec('chromium-browser '.$kernel->getRootDir().'/cache/output.html');
    }
    
    protected function clearDocFilesDir()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        
        $mappings = $container->getParameter('vich_uploader.mappings');
        $uploadDir = $mappings['doc_file']['upload_dir'];
        if (file_exists($uploadDir)) {
            $dir = opendir($uploadDir);
            while (($file = readdir($dir)) !== false) {
                if ($file=='.' || $file=='..') {
                    continue;
                }
                unlink($uploadDir.'/'.$file);
            }
            closedir($dir);
        }
    }
    
    /**
     * Prepare some important objects in one command line
     *
     * @param string $username Username
     * @param string $password Password
     * 
     * @return \stdClass 
     */
    public function loadApp($username = null, $password = null)
    {
        
        $app = new \stdClass();
        if ($username && $password) {
            $app->client = $this->makeClient(array('username' => $username, 'password' => $password));
        } else {
            $app->client = $this->makeClient();
        }
        $app->client->followRedirects(true);
        
        $app->container = $app->client->getContainer();
        $app->kernel = $app->container->get('kernel');
        $app->router = $app->container->get('router');
        $app->em = $app->container->get('doctrine.orm.entity_manager');

        // It doesn't work without this!! Why?!
        if ($username && $password) {
            $crawler = $app->client->request('GET', $app->router->generate('fos_user_security_login'));
            $form = $crawler->selectButton('Login')->form(array(
                '_username'  => $username,
                '_password'  => $password,
            ));
            $app->client->submit($form);
        }

        $app->container = $app->client->getContainer();
//        $user = $app->client->getContainer()->get('security.context')->getToken()->getUser();
//        if ($user == 'anon.') {
//            $user =  null;
//        }
//        $app->user = $user;
        
        return $app;
    }
    
    /**
     * Assert the route is secured from anonimous access 
     * 
     * @param string $route      Route id
     * @param array  $parameters Parametery array for the route
     */
    public function anonimousIsRedirectedToLogin($route, $parameters=array())
    {
        $app = $this->loadApp();

        $crawler = $app->client->request('GET', $app->router->generate($route, $parameters));
        $this->isSuccessful($app->client->getResponse(), true);
        $this->assertEquals(1, $crawler->filter('input[type=submit][value=Login]')->count());
    }
    
}
<?php

namespace Dan\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

/**
 * GoalBundle Controller
 */
class Controller extends BaseController
{

    protected function getCurrentRoute()
    {
        $request = $this->getRequest();
        $router = $this->get('router');
        $args = $router->match($request->getPathInfo());
        $route = $args['_route'];
        foreach($args as $key => $value) {
            if ($key[0]=='_') {
                unset($args[$key]);
            }
        }
        return array('route' => $route, 'args' => $args);
    }
    
    protected function getFromRoute()
    {
        $route = $this->get('session')->get('from_route');
        //$this->get('session')->set('from_route', null);
        return $route;
    }
    
    protected function setFromRoute($route)
    {
        $this->get('session')->set('from_route', $route);
    }
    
    protected function setCurrentFromRoute()
    {
        $this->setFromRoute($this->getCurrentRoute());
    }
    
}
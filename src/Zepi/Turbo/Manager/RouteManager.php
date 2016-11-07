<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 zepi
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

/**
 * The RouteManager registers and manages all routes. The
 * routes are saved in an object backend.
 * 
 * @package Zepi\Turbo\Manager
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Manager;

use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Backend\ObjectBackendAbstract;
use \Zepi\Turbo\Request\RequestAbstract;

/**
 * The RouteManager registers and manages all routes. The
 * routes are saved in an object backend.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class RouteManager
{
    /**
     * @access protected
     * @var Framework
     */
    protected $framework;
    
    /**
     * @access protected
     * @var ObjectBackendAbstract
     */
    protected $routeObjectBackend;
    
    /**
     * @access protected
     * @var array
     */
    protected $routes = array();
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     * @param \Zepi\Turbo\Backend\ObjectBackendAbstract $routeObjectBackend
     */
    public function __construct(Framework $framework, ObjectBackendAbstract $routeObjectBackend)
    {
        $this->framework = $framework;
        $this->routeObjectBackend = $routeObjectBackend;
    }
    
    /**
     * Initializes the routing table. The function loads
     * the saved routes from the object backend.
     * 
     * @access public
     */
    public function initializeRoutingTable()
    {
        $routes = $this->routeObjectBackend->loadObject();
        if (!is_array($routes)) {
            $routes = array();
        }
        
        $this->routes = $routes;
    }
    
    /**
     * Adds an event handler for the given event.
     * 
     * @access public
     * @param string $route
     * @param string $eventName
     * @param integer $priority
     * @return boolean
     */
    public function addRoute($route, $eventName, $priority = 50)
    {
        // If the priority isn't existing we add the priority as 
        // a new array.
        if (!isset($this->routes[$priority])) {
            $this->routes[$priority] = array();
            ksort($this->routes);
        }
        
        // If we had the route already registred, return at this point
        if (isset($this->routes[$priority][$route])) {
            return true;
        }
        
        // Add the route and save the new routes array
        $this->routes[$priority][$route] = $eventName;
        $this->saveRoutes();
        
        return true;
    }
    
    /**
     * Removes a route for the given priority.
     * 
     * @access public
     * @param string $route
     * @param integer $priority
     * @return boolean
     */
    public function removeRoute($route, $priority = 50)
    {
        // If the priority isn't existing we return at this point
        if (!isset($this->routes[$priority])) {
            return true;
        }
        
        // If the route isn't registred we return with true.
        if (!isset($this->routes[$priority][$route])) {
            return true;
        }
        
        // Remove the route from the array
        unset($this->routes[$priority][$route]);
        $this->saveRoutes();
        
        return true;
    }
    
    /**
     * Clears the route cache and reactivates the modules
     * to rebuild the cache.
     * 
     * @access public
     * @param boolean $reactivateModules
     */
    public function clearCache($reactivateModules = true)
    {
        $this->routes = array();
        
        if ($reactivateModules) {
            $this->framework->getModuleManager()->reactivateModules();
        }
    }
    
    /**
     * Saves the routes in the object backend
     * 
     * @access protected
     */
    protected function saveRoutes()
    {
        $this->routeObjectBackend->saveObject($this->routes);
    }
    
    /**
     * Returns the event name for the given request. The function uses
     * the first possible match. The routes are sorted by the priority.
     * 
     * @access public
     * @param \Zepi\Turbo\Request\RequestAbstract $request
     * @return false|string
     */
    public function getEventNameForRoute(RequestAbstract $request)
    {
        // Loop trough the priorities
        foreach ($this->routes as $priority => $routes) {
            // Loop trough the routes for each priority
            foreach ($routes as $route => $eventName) {
                $result = $this->compareRoute($route, $request);
                
                if ($result) {
                    // The routes are equal - we have an event name
                    return $eventName;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Compares the target route with the found route in the routing table.
     * 
     * @access protected
     * @param string $route
     * @param \Zepi\Turbo\Request\RequestAbstract $request
     * @return boolean
     */
    protected function compareRoute($route, RequestAbstract $request)
    {
        // Replace the normal route delimiter with the request route delimiter
        $route = str_replace('|', $request->getRouteDelimiter(), $route);
        
        // Split the two routes into parts
        $routeParts = explode($request->getRouteDelimiter(), $route);
        $targetRouteParts = explode($request->getRouteDelimiter(), trim($request->getRoute(), $request->getRouteDelimiter()));
        $numberOfTargetRouteParts = count($targetRouteParts);
        
        // If we have different number of parts between the two routes
        // there are not equal so we have no equal route.
        if (count($routeParts) != $numberOfTargetRouteParts) {
            return false;
        }
        
        // Define the data types
        $routeParams = array();
        $routeIndex = 0;
        
        // Loop through the route parts and compare each part
        for ($pos = 0; $pos < $numberOfTargetRouteParts; $pos++) {
            $part = $routeParts[$pos];
            $targetPart = $targetRouteParts[$pos];

            if ($targetPart != '' && preg_match('/\[(d|s)(?:\:([0-9a-zA-Z]*))?\]/', $part)) {
                list($key, $value) = $this->parseRouteParam($part, $targetPart);
                
                $routeParams[$routeIndex] = $value;
                $routeIndex++;
                
                if ($key !== '') {
                    $routeParams[$key] = $value;
                }
            } else if ($part !== $targetPart) {
                // The part isn't equal == the route can't be equal
                return false;
            }
        }

        // Save the route parameters in the request
        $request->setRouteParams($routeParams);
        
        return true;
    }
    
    /**
     * Parses the route param data to the correct format
     * 
     * @access protected
     * @param string $part
     * @param string $targetPart
     * @return array
     */
    protected function parseRouteParam($part, $targetPart)
    {
        preg_match('/\[(d|s)(?:\:([0-9a-zA-Z]*))?\]/', $part, $matches);

        $value = null;
        
        // If the part is a data type we need this route parameter
        if ($matches[1] === 'd' && is_numeric($targetPart)) {
            // Transform the value into the correct data type
            $value = $targetPart * 1;
        } else if ($matches[1] === 's' && is_string($targetPart)) {
            $value = $targetPart;
        }
        
        $key = '';
        if (isset($matches[2])) {
            $key = $matches[2];
        }
        
        return [$key, $value];
    }
}

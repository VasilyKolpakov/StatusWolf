<?php
/**
 * SWRouter
 *
 * Router for StatusWolf, parses the URL path and hands off
 * to the appropriate controller
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 22 May 2013
 *
 * @package StatusWolf.Router
 */
class SWRouter {

  function __construct($uri)
  {
    // Init logging for the class
    if(SWConfig::read_values('statuswolf.debug'))
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::DEBUG);
    }
    else
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::INFO);
    }
    $this->log_tag = '(' . $_SESSION['_sw_authsession']['username'] . '|' . $_SESSION['_sw_authsession']['sessionip'] . ') ';

    // If installed in a subdirectory (e.g. root url of the
    // app is http://example.com/StatusWolf), strip the base URI
    if (BASE_URI && strpos($uri, BASE_URI) !== false)
    {
      $uri = substr_replace($uri, '', 0, strlen(BASE_URI));
    }

    // Strip any php script names passed in the URI, we don't need 'em.
    if (preg_match('/\/(\w+\.php$)/', $uri, $matches))
    {
      $uri = substr_replace($uri, '', -strlen($matches[1]));
    }

    $uri = preg_replace('/^\//', '', $uri);

    // Parse the incoming URL into it component parts
    $url_parts = parse_url(URL . $uri);

    $url_path = array_slice(explode('/', $url_parts['path']), 1);
    if (preg_match($url_path[0], BASE_URI))
    {
      array_shift($url_path);
    }
    $this->loggy->logDebug($this->log_tag . json_encode($url_path));
    $url_parts['url_path'] = $url_path;
    $this->loggy->logDebug($this->log_tag . json_encode($url_parts));

    // The first chunk of the path maps to the controller, if it's empty that
    // means the home controller
    if (count($url_parts['url_path'] == 1) && strlen($url_parts['url_path'][0]) == 0)
    {
      $controller_object = new HomeController();
    }
    else
    {
      $controller = ucfirst(array_shift($url_parts['url_path'])) . 'Controller';
      try
      {
        $controller_object = new $controller($url_parts);
      }
      catch(SWException $e)
      {
        $this->loggy->logDebug("Controller " . $controller . " was not found, punting to Home");
        $controller_object = new HomeController();
      }
    }
  }

}

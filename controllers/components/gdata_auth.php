<?php
/**
 * Provides methods for determining whether you are authorized or can actually
 * get authorization for accessing GData services.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class GdataAuthComponent extends Object {

  /**
   * The other components used by this component
   *
   * @var array
   * @access public
   */
  public $components = array('Session');

  /**
   * The default/common elements of the requests for the oauth request and
   * access tokens made through HttpSocketOauth.
   *
   * @var array
   * @access protected
   */
  protected $_oAuthRequestDefaults = array(
    'uri' => array(
      'scheme' => 'https',
      'host' => 'www.google.com',
    ),
    'method' => 'GET',
    'auth' => array(
      'method' => 'OAuth',
    ),
  );

  /**
   * Called before Controller::beforeFilter(), stores reference to Controller
   * object
   *
   * @param AppController $controller
   * @return void
   * @access public
   */
  public function initialize(&$controller) {
    $this->controller =& $controller;
  }

  /**
   * Checks whether you are authorized to access a particular datasource. You
   * may be authorized if you have credentials in the datasource config, or they
   * are available in the Session. If credentials are in the Session, but not in
   * the datasource config, they are copied from the Session in to the
   * datasource config so they are available when requests are made to the API
   * through the datasource.
   *
   * @param string $dataSourceName The name of the datasource you want to check
   *  if you are authorized to access. E.g. 'youTube' or 'googleAnalytics'
   * @return boolean
   */
  public function isAuthorized($dataSourceName = null) {
    if (!$dataSourceName) {
      $dataSourceName = $this->controller->{$this->controller->modelClass}->useDbConfig;
    }
    $this->_dataSourceName = $dataSourceName;
    if ($this->_isLoggedIn()) {
      if (!$this->_areOAuthCredentialsInConfig() && $this->_areOAuthCredentialsInSession()) {
        $this->_setOAuthCredentialsFromSessionInConfig();
      }
      return true;
    }
    if ($this->_areOAuthCredentialsInConfig()) {
      $this->_setLoggedIn();
      return true;
    }
    if ($this->_areOAuthCredentialsInSession()) {
      $this->_setLoggedIn();
      $this->_setOAuthCredentialsFromSessionInConfig();
      return true;
    }
    return false;
  }

  /**
   * Returns true if logged in, or authorized to access the current datasource
   *
   * @return boolean
   */
  protected function _isLoggedIn() {
    return $this->Session->check('Gdata.Auth' . $this->_dataSourceName . '.is_logged_in')
    && $this->Session->read('Gdata.Auth' . $this->_dataSourceName . '.is_logged_in') === true;
  }

  /**
   * Sets logged in param in Session to true for the current datasource
   *
   * @return boolean
   */
  protected function _setLoggedIn() {
    $this->Session->write('Gdata.Auth.' . $this->_dataSourceName . '.is_logged_in', true);
  }

  /**
   * Returns true if OAuth credentials are in the config for the current
   * datasource
   *
   * @return boolean
   */
  protected function _areOAuthCredentialsInConfig() {
    $dataSource = $this->_getDataSource();
    return isset($dataSource->config['oauth_token']) && !empty($dataSource->config['oauth_token'])
    && isset($dataSource->config['oauth_token_secret'])&& !empty($dataSource->config['oauth_token_secret']);
  }

  /**
   * Returns true if OAuth credentials are in the session for the current
   * datasource
   *
   * @return boolean
   */
  protected function _areOAuthCredentialsInSession() {
    return $this->Session->check('Gdata.Auth.' . $this->_dataSourceName . '.oauth_token')
    && $this->Session->check('Gdata.Auth.' . $this->_dataSourceName . '.oauth_token_secret');
  }

  /**
   * Copies OAuth credentials from session into the config for the current
   * datasource
   *
   * @return void
   */
  protected function _setOAuthCredentialsFromSessionInConfig() {
    $dataSource = $this->_getDataSource();
    $dataSource->config['oauth_token'] = $this->Session->read('Gdata.Auth.' . $this->_dataSourceName . '.oauth_token');
    $dataSource->config['oauth_token_secret'] = $this->Session->read('Gdata.Auth.' . $this->_dataSourceName . '.oauth_token_secret');
  }

  /**
   * Returns the datasource object for the current datasource
   *
   * @return DataSource object
   */
  protected function _getDataSource() {
    return ConnectionManager::getDataSource($this->_dataSourceName);
  }

  /**
   * Returns the oAuthScope public property of the current datasource if set
   * 
   * @return string OAuth scope e.g. http://gdata.youtube.com
   */
  protected function _getOAuthScope() {
    $dataSource = $this->_getDataSource();
    if (!isset($dataSource->oAuthScope)) {
      return false;
    }
    return $dataSource->oAuthScope;
  }

  /**
   * Gets OAuth request token from Google and then redirects user to the page on
   * Google where they can authorize your app to interact with their account on
   * the API for the given datasource. You can also pass a relative or absolute
   * URL that will be stored in the Session and the user redirected back to
   * after they authorize your app and we get the access token.
   *
   * This and the next method which gets the access token are called from 2
   * actions in the GdataAppController. You can link to these actions from your
   * app or lift reproduce them in one of your own controllers that uses this
   * component.
   * 
   * @param string $dataSourceName The name of the datasource for which you want
   *  to get an OAuth token.
   * @param string $returnTo The URL the user should be redirected to after the
   *  hand shaking / OAuth Dance
   */
  public function getOAuthRequestToken($dataSourceName, $returnTo = null) {

    if (Configure::read('Session.checkAgent') === true) {
      trigger_error(__('Set Session.checkAgent to false in core.php to avoid referrer check', true));
    }

    $this->_dataSourceName = $dataSourceName;

    // If not specified, use the referer, if it's available, and only if it's
    // from the current domain, else use the root
    if (!$returnTo) {
      $returnTo = $this->controller->referer('/', true);
    }

    $this->Session->write('Gdata.Auth.' . $this->_dataSourceName . '.return_to', $returnTo);

    // This is the URL Google sends the user back to after allowing your app
    // access to their account. The action should include the call to the
    // getOAuthAccessToken() method in this component.
    $callback = Router::url(array('action' => 'get_gdata_oauth_access_token', $this->_dataSourceName), true);

    // If the scope is not set, you can't continue
    $oAuthScope = $this->_getOAuthScope();
    if (!$oAuthScope) {
      $this->Session->write('Gdata.Auth.' . $this->_dataSourceName . '.error', __('Could not get access token. Scope not set.', true));
      $this->controller->redirect($this->Session->read('Gdata.Auth.' . $this->_dataSourceName . '.return_to'));
    }

    $dataSource = $this->_getDataSource();

    // Construct request
    $request = Set::merge($this->_oAuthRequestDefaults, array(
      'uri' => array(
        'path' => '/accounts/OAuthGetRequestToken',
        'query' => array(
          'scope' => $oAuthScope,
        )
      ),
      'auth' => array(
        'oauth_consumer_key' => $dataSource->config['oauth_consumer_key'],
        'oauth_consumer_secret' => $dataSource->config['oauth_consumer_secret'],
        'oauth_callback' => $callback,
      ),
    ));

    if (isset($dataSource->config['xoauth_displayname'])) {
      $request['body']['xoauth_displayname'] = $dataSource->config['xoauth_displayname'];
    }

    App::import('Vendor', 'HttpSocketOauth');
    $HttpSocketOauth = new HttpSocketOauth();

    $response = $HttpSocketOauth->request($request);

    if ($HttpSocketOauth->response['status']['code'] != 200) {
      $this->Session->write('Gdata.Auth.' . $this->_dataSourceName . '.error', __('Could not get access token. Response for get request token was not OK.', true));
      $this->controller->redirect($this->Session->read('Gdata.Auth.' . $this->_dataSourceName . '.return_to'));
    }

    parse_str($response, $response);

    // Add request token and secret to session for use in getting the access
    // token and secret
    $this->Session->write('Gdata.Auth.' . $this->_dataSourceName . '.oauth_request_token', $response['oauth_token']);
    $this->Session->write('Gdata.Auth.' . $this->_dataSourceName . '.oauth_request_token_secret', $response['oauth_token_secret']);

    // Redirect user to google to Authorize the application
    $this->controller->redirect('https://www.google.com/accounts/OAuthAuthorizeToken?oauth_token=' . $response['oauth_token']);

  }

  /**
   * Gets OAuth access token and secret from Google and then redirects user to
   * the URL in returnTo sent to getOAuthRequestToken().
   *
   * @param string $dataSourceName The name of the datasource for which you want
   *  to get an OAuth token.
   */
  public function getOAuthAccessToken($dataSourceName) {

    $this->_dataSourceName = $dataSourceName;

    $dataSource = $this->_getDataSource();

    // Construct the request
    $request = Set::merge($this->_oAuthRequestDefaults, array(
      'uri' => array(
        'path' => '/accounts/OAuthGetAccessToken',
      ),
      'auth' => array(
        'method' => 'OAuth',
        'oauth_consumer_key' => $dataSource->config['oauth_consumer_key'],
        'oauth_consumer_secret' => $dataSource->config['oauth_consumer_secret'],
        'oauth_token' => $this->Session->read('Gdata.Auth.' . $this->_dataSourceName . '.oauth_request_token'),
        'oauth_token_secret' => $this->Session->read('Gdata.Auth.' . $this->_dataSourceName . '.oauth_request_token_secret'),
        'oauth_verifier' => $this->controller->params['url']['oauth_verifier'],
      ),
    ));

    App::import('Vendor', 'HttpSocketOauth');
    $HttpSocketOauth = new HttpSocketOauth();

    $response = $HttpSocketOauth->request($request);

    if ($HttpSocketOauth->response['status']['code'] != 200) {
      $this->Session->write('Gdata.Auth.' . $this->_dataSourceName . '.error', __('Could not get access token. Response for get access token was not OK.', true));
      $this->controller->redirect($this->Session->read('Gdata.Auth.' . $this->_dataSourceName . '.return_to'));
    }

    parse_str($response, $response);

    // Add the access token and secret to the session
    $this->Session->write('Gdata.Auth.' . $this->_dataSourceName . '.oauth_token', $response['oauth_token']);
    $this->Session->write('Gdata.Auth.' . $this->_dataSourceName . '.oauth_token_secret', $response['oauth_token_secret']);

    // The user is now logged in so next time we check isAuthorized we'll return
    // true and just before that, copy the oauth access token and secret to the
    // datasource config.
    $this->_setLoggedIn();

    $this->controller->redirect($this->Session->read('Gdata.Auth.' . $this->_dataSourceName . '.return_to'));

  }

}

<?php
App::import('Datasource', 'Rest.RestSource');
/**
 * CakePHP base datasource used by all Gdata service data sources
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class GdataSource extends RestSource {

  /**
   * Default config
   * 
   * @var array
   */
  protected $_defaults = array(
    'GData-Version' => 2,
    'oauth_consumer_key' => 'anonymous',
    'oauth_consumer_secret' => 'anonymous',
  );

  /**
   * Overrides RestSource constructor to add defaults into config and use the
   * OAuth extension to CakePHP's default HttpSocket class to issue the requests
   * @param array $config
   */
  public function  __construct($config) {
    $config = array_merge($this->_defaults, $config);
    App::import('Vendor', 'HttpSocketOauth');
    parent::__construct($config, new HttpSocketOauth());
    
  }

  /**
   * Adds in common elements to the request such as GData version and Developer
   * key headers and the OAuth params from config if not set in the request
   * already
   *
   * @param AppModel $model The model the operation is called on. Should have a
   *  request property in the format described in HttpSocket::request
   * @return mixed Depending on what is returned from RestSource::request()
   */
  public function request(&$model) {

    // If auth key is set and not false, fill the request with auth params from
    // config if not already present in the request and set the method to OAuth
    // to trigger HttpSocketOauth to sign the request
    if (array_key_exists('auth', $model->request)
    && $model->request['auth'] !== false) {

      if (!is_array($model->request['auth'])) {
        $model->request['auth'] = array();
      }
      if (!isset($model->request['auth']['method'])) {
        $model->request['auth']['method'] = 'OAuth';
      }
      $oAuthParams = array(
        'oauth_consumer_key',
        'oauth_consumer_secret',
        'oauth_token',
        'oauth_token_secret',
      );
      foreach ($oAuthParams as $oAuthParam) {
        if (!isset($model->request['auth'][$oAuthParam])) {
        	if (!isset($this->config[$oAuthParam])) {
        		trigger_error(sprintf(
        			__('Please specify (either statically or dynamically) the datasource config param "%1s" for connection config %2s.', true), 
        			$oAuthParam,
        			get_class($this)
				), E_USER_ERROR);
        		continue;
        	}
          $model->request['auth'][$oAuthParam] = $this->config[$oAuthParam];
        }
      }
    }

    // Add in GData Version to request
    if (!isset($model->request['header']['GData-Version'])) {
      $model->request['header']['GData-Version'] = $this->config['GData-Version'];
    }

    // Add in developer key to request N.B. Prefix developer key with 'key='
    if (!isset($model->request['header']['X-GData-Key']) && isset($this->config['X-GData-Key'])) {
      $model->request['header']['X-GData-Key'] = 'key=' . $this->config['X-GData-Key'];
    }

    // Get the response from calling request on the Rest Source (it's parent)
    $response = parent::request($model);
//    echo '<pre>';
//    echo htmlspecialchars($this->Http->request['raw']);
//    echo htmlspecialchars($this->Http->response['raw']['response']);
//    echo '</pre>';
//    pr($response);
//    die();

    return $response;
  }

}
?>

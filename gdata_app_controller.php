<?php
/**
 * Plugin base controller. Includes functionality for working with the custom
 * pagination requirements by the web service. To use CakePHP's built in
 * pagination with this web service in your app, you'll need to do something
 * similar to below in your controller.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class GdataAppController extends AppController {

  /**
   * Overrides CakePHP's default paging settings
   *
   * @var array
   */
  public $paginate = array(
    'page' => 1,
    'limit' => 10,
  );

  /**
   * Called automatically before any action
   *
   * Sets any action to be allowed in case you've got Auth enabled in your app
   */
  public function beforeFilter() {
    parent::beforeFilter();
    if (isset($this->Auth)) {
      $this->Auth->allow('*');
    }
  }

  /**
   * Components this controller uses
   *
   * @var array
   */
  public $components = array('GdataAuth');

  /**
   * Overrides Controller::paginate() to set paging options in the
   * Model::paginate property so they available in the Model::paginateCount()
   * method. This is necessary due to the nature of having to paginate over a
   * web service result set rather than a database result set.
   *
   * @param mixed $object
   * @param mixed $scope
   * @param mixed $whitelist
   * @return array The result set
   */
  public function paginate($object = null, $scope = array(), $whitelist = array()) {

    // Manually put page, show & sort & direction params from URL if present in
    // Controller::paginate
    if (isset($this->passedArgs['page'])) {
      $this->paginate['page'] = $this->passedArgs['page'];
    }
    if (isset($this->passedArgs['show'])) {
      $this->paginate['limit'] = $this->passedArgs['show'];
    }
    if (isset($this->passedArgs['sort'])) {
      $this->paginate['order'] = $this->passedArgs['sort'];
      if (isset($this->passedArgs['direction'])) {
        $this->paginate['order'] .= ' ' . $this->passedArgs['direction'];
      }
    }

    // Merges the Controller::paginate paging params with the
    // Controller::paginate[Model] paging params if present
    $options = $this->paginate;
    if (isset($options[$object])) {
      $options = array_merge($options, $options[$object]);
      unset($options[$object]);
    }

    // Set the merged paging options in the Model::paginate property, so they
    // are available in Model::paginateCount() & Model::paginate() methods which
    // handle pagination of a result set from the web service
    $this->$object->paginate = $this->paginate[$object] = $options;

    return parent::paginate($object, $scope, $whitelist);

  }

  /**
   * The first stage of the handshaking with Google to get an OAuth Access Token
   * and OAuth Access Token Secret. This action gets an OAuth Request Token from
   * Google for the scope (e.g. http://gdata.youtube.com) of the given
   * datasource and then redirects the user to Google to authorize the OAuth
   * Request Token.
   *
   * @param String $dataSourceName e.g. 'youTube'
   * @param String $returnTo The url to return to on success or failure. N.B.
   * should be base64URLencoded (i.e. like base64 but with + / = replaced with 
   * - _ , respectively)
   */
  public function get_gdata_oauth_request_token($dataSourceName, $returnTo = null) {
    if ($returnTo) {
      $returnTo = base64_decode(strtr($returnTo, '-_,', '+/='));
    }
    $this->GdataAuth->getOAuthRequestToken($dataSourceName, $returnTo);
  }

  /**
   * The second stage of the handshaking with Google to get an OAuth Access
   * Token and OAuth Access Token Secret. This action is the callback url
   * specified in the get_gdata_oauth_request_token action above that Google
   * redirects the user back to after they have authorised the request token.
   *
   * This actually exchanges the authorised request token for the OAuth Access
   * Token and Secret and stores ithem in the session before redirecting the
   * user back to the URL passed in in the returnTo parameter to the the
   * get_gdata_oauth_request_token action above.
   */
  public function get_gdata_oauth_access_token($dataSourceName) {
    $this->GdataAuth->getOAuthAccessToken($dataSourceName);
  }

}
?>
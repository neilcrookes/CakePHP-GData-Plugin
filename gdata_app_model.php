<?php
/**
 * Plugin base model. Configures all models with regards the database
 * configuration (the datasource for the plugin), table to use (none), imports
 * the datasource, and includes functionality for working with the custom
 * pagination requirements by the web service.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class GdataAppModel extends AppModel {

  /**
   * The models in the plugin get data from the web service, so they don't need
   * a table.
   *
   * @var string
   */
  public $useTable = false;

  /**
   * Methods in the models result in HTTP requests using the HttpSocket. So
   * rather than do all the heavy lifting in the datasource, we set the various
   * params of the request in the individual model methods. This ties the model
   * to the data layer, but these models are especially for this datasource.
   *
   * @var array
   */
  public $request = array();

  /**
   * Since the webservice call returns the results in the current page of the
   * result set and the total number of results in the whole results set, we
   * need custom paginate and paginateCount methods, whereby the call to the
   * web service is made in the paginateCount method, the results stored and the
   * total results returned, then the actual results are returned from the
   * paginate method. This way the call to the web service is only made once.
   * However, in order to do this, we need to know the page and limit params in
   * the paginateCount method. So these should be set in this Model::paginate
   * property in the controller, before calling Controller::paginate().
   *
   * @var array
   */
  public $paginate = array();

  /**
   * Temporarily stores the results after being fetched during the paginateCount
   * method, before returning in the paginate method.
   *
   * @var array
   */
  protected $_results = null;

  /**
   * Adds the datasource to the Connection Manager's list of sources if it is
   * not already there. It would normally be there if you add the datasource
   * details to your app/config/database.php file, but this code negates the
   * need to do that. It adds the datasource for the current model being
   * constructed with default basic configuration options, and extra options
   * from the GDATA_CONFIG->{$this->useDbConfig} class property from the file in
   * plugins/gdata/config/gdata_config.php if it exists, and extra options from
   * Gdata.config key in the Configure class, if set. Options should include
   * X-GData-Key as a minimum if required by the Gdata API, and also optionally
   * oauth_consumer_key, oauth_consumer_secret, oauth_token and
   * oauth_token_secret keys
   *
   * @param mixed $id
   * @param string $table
   * @param mixed $ds
   */
  public function __construct($id = false, $table = null, $ds = null) {

    // Get the list of datasource that the ConnectionManager is aware of
    $sources = ConnectionManager::sourceList();

    // If this model's datasource isn't in it, add it
    if (!in_array($this->useDbConfig, $sources)) {

      // Default minimum config
      $config = array(
        'datasource' => 'Gdata.GdataSource',
        'driver' => 'Gdata.' . Inflector::camelize($this->useDbConfig),
      );

      // Try an import the plugins/gdata/config/gdata_config.php file and merge
      // any default and datasource specific config with the defaults above
      if (App::import(array('type' => 'File', 'name' => 'Gdata.GDATA_CONFIG', 'file' => 'config'.DS.'gdata_config.php'))) {
        $GDATA_CONFIG = new GDATA_CONFIG();
        if (isset($GDATA_CONFIG->default)) {
          $config = array_merge($config, $GDATA_CONFIG->default);
        }
        if (isset($GDATA_CONFIG->{$this->useDbConfig})) {
          $config = array_merge($config, $GDATA_CONFIG->{$this->useDbConfig});
        }
      }

      // Add any config from Configure class that you might have added at any
      // point before the model is instantiated.
      if (($configureConfig = Configure::read('Gdata.config')) != false) {
        $config = array_merge($config, $configureConfig);
      }

      // Add the datasource, with it's new config, to the ConnectionManager
      ConnectionManager::create($this->useDbConfig, $config);

    }

    parent::__construct($id, $table, $ds);
    
  }

  /**
   * Overloads the Model::find() method. Resets request array in between finds
   *
   * @param string $type
   * @param array $options
   */
  public function find($type, $options = array()) {
    $this->request = array();
    $options = $this->_standardGdataParams($options);
    return parent::find($type, $options);
  }

  protected function _standardGdataParams($query) {

    return $query;
  }

  /**
   * Add pagination params from the $query to the $request
   *
   * @param array $query Query array sent as options to a find call
   * @return array
   */
  protected function _paginationParams($query) {
    if (!empty($query['limit'])) {
      $this->request['uri']['query']['max-results'] = $query['limit'];
    } else {
      $this->request['uri']['query']['max-results'] = $query['limit'] = 10;
    }
    if (!empty($query['page'])) {
      $this->request['uri']['query']['start-index'] = ($query['page'] - 1) * $query['limit'] + 1;
    } else {
      $this->request['uri']['query']['start-index'] = $query['page'] = 1;
    }
    return $query;
  }

  /**
   * Called by Controller::paginate(). Calls the custom find type. Stores the
   * results for later returning in the paginate() method. Returns the total
   * number of results from the full result set.
   *
   * @param array $conditions
   * @param integer $recursive
   * @param array $extra
   * @return integer The number of items in the full result set
   */
  public function paginateCount($conditions, $recursive = 1, $extra = array()) {
    $response = $this->find($this->paginate[0], $this->paginate);
    $this->_results = $response;
    return $response['feed']['totalResults'];
  }

  /**
   * Returns the results of the call to the web service fetched in the
   * self::paginateCount() method above.
   *
   * @param mixed $conditions
   * @param mixed $fields
   * @param mixed $order
   * @param integer $limit
   * @param integer $page
   * @param integer $recursive
   * @param array $extra
   * @return array The results of the call to the web service
   */
  public function paginate($conditions, $fields = null, $order = null, $limit = null, $page = 1, $recursive = null, $extra = array()) {
    return $this->_results;
  }

}
?>

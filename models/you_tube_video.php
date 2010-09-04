<?php
/**
 * Plugin model for "You Tube Videos".
 *
 * Provides custom find types for the various calls on the web service, mapping
 * familiar CakePHP methods and parameters to the http request params for
 * issuing to the web service.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class YouTubeVideo extends GdataAppModel {

  /**
   * The URI to get the list of categories
   */
  const YOU_TUBE_CATEGORY_DOCUMENT_URI = 'http://gdata.youtube.com/schemas/2007/categories.cat';

  /**
   * The datasource this model uses
   *
   * @var name
   */
  public $useDbConfig = 'youTube';

  /**
   * The fields and their types for the form helper
   *
   * @var array
   */
  public $_schema = array(
    'videoid' => array('type' => 'string', 'length' => '12'),
    'title' => array('type' => 'string', 'length' => '255'),
    'description' => array('type' => 'text'),
    'keywords' => array('type' => 'text'),
    'category' => array('type' => 'string', 'length' => '255'),
    'developertag' => array('type' => 'string', 'length' => '255'),
    'rate' => array('type' => 'string', 'length' => '10'),
    'comment' => array('type' => 'string', 'length' => '10'),
    'commentVote' => array('type' => 'string', 'length' => '10'),
    'videoRespond' => array('type' => 'string', 'length' => '10'),
    'embed' => array('type' => 'string', 'length' => '10'),
    'syndicate' => array('type' => 'string', 'length' => '10'),
    'private' => array('type' => 'boolean'),
    'latitude' => array('type' => 'float', 'length' => '13,6'),
    'longitude' => array('type' => 'float', 'length' => '13,6'),
  );

  /**
   * The field to be used as the primary key
   * 
   * @var string
   */
  public $primaryKey = 'videoid';

  /**
   * Validation rules
   * 
   * @var array
   */
  public $validate = array(
    'title' => array(
      'notEmpty' => array(
        'rule' => 'notEmpty',
        'message' => 'Please enter a title',
      ),
    ),
    'category' => array(
      'validCategory' => array(
        'rule' => 'validCategory',
        'message' => 'Please select a valid category',
        'allowEmpty' => true,
      ),
    ),
    'rate' => array(
      'validAccessControl' => array(
        'rule' => 'validAccessControl',
        'message' => 'Please select either "allowed" or "denied"',
        'allowEmpty' => true,
      ),
    ),
    'comment' => array(
      'validAccessControl' => array(
        'rule' => 'validAccessControl',
        'message' => 'Please select either "allowed", "moderated" or "denied"',
        'allowEmpty' => true,
      ),
    ),
    'commentVote' => array(
      'validAccessControl' => array(
        'rule' => 'validAccessControl',
        'message' => 'Please select either "allowed" or "denied"',
        'allowEmpty' => true,
      ),
    ),
    'videoRespond' => array(
      'validAccessControl' => array(
        'rule' => 'validAccessControl',
        'message' => 'Please select either "allowed", "moderated" or "denied"',
        'allowEmpty' => true,
      ),
    ),
    'embed' => array(
      'validAccessControl' => array(
        'rule' => 'validAccessControl',
        'message' => 'Please select either "allowed" or "denied"',
        'allowEmpty' => true,
      ),
    ),
    'syndicate' => array(
      'validAccessControl' => array(
        'rule' => 'validAccessControl',
        'message' => 'Please select either "allowed" or "denied"',
        'allowEmpty' => true,
      ),
    ),
  );
  
  /**
   * 
   */
  public $_findMethods = array(
    'videos' => true,
    'userUploads' => true,
    'related' => true,
    'responses' => true,
    'topRated' => true,
    'topFavorites' => true,
    'mostViewed' => true,
    'mostPopular' => true,
    'mostRecent' => true,
    'mostDiscussed' => true,
    'mostResponded' => true,
    'recentlyFeatured' => true,
    'watchOnMobile' => true,
    'favorites' => true,
  );

  /**
   * The access controls You Tube supports and their supported values
   *
   * This be used to populate forms and is also used to validate data
   *
   * @var array
   */
  public $accessControls = array(
    'rate' => array('allowed', 'denied'),
    'comment' => array('allowed', 'moderated', 'denied'),
    'commentVote' => array('allowed', 'denied'),
    'videoRespond' => array('allowed', 'moderated', 'denied'),
    'embed' => array('allowed', 'denied'),
    'syndicate' => array('allowed', 'denied'),
  );

  /**
   * You Tube regions
   * @var array
   */
  public $regions = array(
    'AU' => 'Australia',
    'BR' => 'Brazil',
    'CA' => 'Canada',
    'CZ' => 'Czech Republic',
    'FR' => 'France',
    'DE' => 'Germany',
    'GB' => 'Great Britain',
    'NL' => 'Holland',
    'HK' => 'Hong Kong',
    'IN' => 'India',
    'IE' => 'Ireland',
    'IL' => 'Israel',
    'IT' => 'Italy',
    'JP' => 'Japan',
    'MX' => 'Mexico',
    'NZ' => 'New Zealand',
    'PL' => 'Poland',
    'RU' => 'Russia',
    'KR' => 'South Korea',
    'ES' => 'Spain',
    'SE' => 'Sweden',
    'TW' => 'Taiwan',
    'US' => 'United States',
  );

  public $times = array(
    'today' => 'Today',
    'this_week' => 'This week',
    'this_month' => 'This month',
    'all_time' => 'All time'
  );
  
  /**
   * Fetches categories from cache or from You Tube categories .cat file,
   * extracts the terms and labels into and array of term => label pairs, caches
   * them and then returns them.
   *
   * Can be used to populate form fields and is used to validate categories.
   *
   * Default is to extract only the assignable categories (categories may be
   * deprecated so videos can no longer be assigned to them, but you may want
   * all).
   *
   * Language is not yet supported - the default - english - will be returned
   * from You Tube.
   *
   * @param boolean $assignableOnly Defines whether to return only assignable
   * categories or all
   * @param string $language Not yet implemented, will allow definition of
   * language to be used for the categories.
   * @return array Array of category term => label pairs
   */
  public function categories($assignableOnly = true, $language = '') {

    $cacheKey = 'gdata_you_tube_categories';

    if ($assignableOnly) {
      $cacheKey .= '_assignable';
    }

    $categories = Cache::read($cacheKey);

    if ($categories != false) {
      return $categories;
    }
    
    $categories = array();

    // Load the categories into an XML doc
    $xml = new SimpleXMLElement(self::YOU_TUBE_CATEGORY_DOCUMENT_URI, null, true);

    $xpath = '/app:categories/atom:category';

    // Only fetch assignable categories
    if ($assignableOnly) {
      $xpath .= '/yt:assignable/..';
    }

    $categoryObjects = $xml->xpath($xpath);

    foreach ($categoryObjects as $categoryObject) {
      $categories[(string)$categoryObject['term']] = (string)$categoryObject['label'];
    }

    Cache::write($cacheKey, $categories);

    return $categories;
    
  }

  /**
   * Validates the selected category is one of the assignable categories from
   * Google
   *
   * @param array $data
   * @return boolean
   */
  public function validCategory($data) {
    $categories = $this->categories();
    return array_key_exists(current($data), $categories);
  }

  /**
   * Validates the value for the access control field is allowed for that field
   * 
   * @param array $data
   * @return boolean
   */
  public function validAccessControl($data) {
    return in_array(current($data), $this->accessControls[key($data)]);
  }

  /**
   * Creates the API XML request containing the meta data and adds it to the
   * request body along with the file as multipart data, and sets other values
   * in the request array required for uploading a video to YouTube.
   *
   * ClassRegistry::init('Gdata.YouTubeVideo')->save(array(
  'YouTubeVideo' => array(
    'title' => 'Flying into Chicago Airport',
    'description' => 'Filmed through the plane window, shows coming in over the lake',
    'category' => 'Travel',
    'keywords' => 'Chicago, Plane, Lake, Skyline',
    'rate' => 'allowed',
    'comment' => 'allowed',
    'commentVote' => 'allowed',
    'videoRespond' => 'allowed',
    'embed' => 'allowed',
    'syndicate' => 'allowed',
    'private' => 1,
    'file' => array(
      'name' => 'chicago 1 060.AVI',
      'type' => 'video/avi',
      'tmp_name' => 'C:\Windows\Temp\php6D66.tmp',
      'error' => 0,
      'size' => 5863102))));
   *
   * @param array $data See Model::save()
   * @param boolean $validate See Model::save()
   * @param array $fieldList See Model::save()
   * @return boolean
   */
  public function save($data = null, $validate = true, $fieldList = array()) {

    // Create the XML containing the meta data about the video
    $doc = new DOMDocument('1.0', 'utf-8');
    $entry = $doc->createElementNS('http://www.w3.org/2005/Atom', 'entry');
    $entry->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:media', 'http://search.yahoo.com/mrss/');
    $entry->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:yt', 'http://gdata.youtube.com/schemas/2007');
    $doc->appendChild($entry);
    $media = $doc->createElementNS('http://search.yahoo.com/mrss/', 'media:group');
    $entry->appendChild($media);

    if (!empty($data[$this->alias]['title'])) {
      $title = $doc->createElementNS('http://search.yahoo.com/mrss/', 'media:title', $data[$this->alias]['title']);
      $title->setAttribute('type', 'plain');
      $media->appendChild($title);
    }

    if (!empty($data[$this->alias]['description'])) {
      $description = $doc->createElementNS('http://search.yahoo.com/mrss/', 'media:description', $data[$this->alias]['description']);
      $description->setAttribute('type', 'plain');
      $media->appendChild($description);
    }

    if (!empty($data[$this->alias]['category'])) {
      $category = $doc->createElementNS('http://search.yahoo.com/mrss/', 'media:category', $data[$this->alias]['category']);
      $category->setAttribute('scheme', 'http://gdata.youtube.com/schemas/2007/categories.cat');
      $media->appendChild($category);
    }

    if (!empty($data[$this->alias]['developertag'])) {
      $category = $doc->createElementNS('http://search.yahoo.com/mrss/', 'media:category', $data[$this->alias]['developertag']);
      $category->setAttribute('scheme', 'http://gdata.youtube.com/schemas/2007/developertags.cat');
      $media->appendChild($category);
    }

    if (!empty($data[$this->alias]['keywords'])) {
      $keywords = $doc->createElementNS('http://search.yahoo.com/mrss/', 'media:keywords', $data[$this->alias]['keywords']);
      $media->appendChild($keywords);
    }

    foreach ($this->accessControls as $action => $permissions) {
      if (empty($data[$this->alias][$action])) {
        continue;
      }
      $accessControl = $doc->createElementNS('http://gdata.youtube.com/schemas/2007', 'yt:accessControl');
      $accessControl->setAttribute('action', $action);
      $accessControl->setAttribute('permission', $data[$this->alias][$action]);
      $entry->appendChild($accessControl);
    }

    if (!empty($data[$this->alias]['private'])) {
      $private = $doc->createElementNS('http://gdata.youtube.com/schemas/2007', 'yt:private');
      $media->appendChild($private);
    }

    // The boundary string is used to identify the different parts of a
    // multipart http request
    $boundaryString = 'Next_Part_' . String::uuid();

    // Build the multipart body of the http request
    $body = "--$boundaryString\r\n";
    $body.= "Content-Type: application/atom+xml; charset=UTF-8\r\n";
    $body.= "\r\n";
    $body.= $doc->saveXML()."\r\n";
    $body.= "--$boundaryString\r\n";
    $body.= "Content-Type: {$data[$this->alias]['file']['type']}\r\n";
    $body.= "Content-Transfer-Encoding: binary\r\n";
    $body.= "\r\n";
    $body.= file_get_contents($data[$this->alias]['file']['tmp_name'])."\r\n";
    $body.= "--$boundaryString--\r\n";

    $this->request = array(
      'uri' => array(
        'host' => 'uploads.gdata.youtube.com',
        'path' => '/feeds/api/users/default/uploads',
      ),
      'header' => array(
        'Content-Type' => 'multipart/related; boundary="' . $boundaryString . '"',
        'Slug' => $data[$this->alias]['file']['name']
      ),
      'auth' => array(
        'method' => 'OAuth',
      ),
      'body' => $body,
    );

    return parent::save($data, $validate, $fieldList);

  }

  /**
   *
   * @param <type> $state
   * @param <type> $query
   * @param <type> $results
   * @return <type>
   */
  protected function _findVideos($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/videos';
      if (isset($query['conditions']['q'])) {
        $this->request['uri']['query']['q'] = $query['conditions']['q'];
      }
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns a list of videos that a particular user has uploaded
   *
   *      YouTubeVideo::find('userUploads'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - username String If omitted, gets logged in user's favorites
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findUserUploads($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      if (empty($query['conditions']['username'])) {
        $query['conditions']['username'] = 'default';
        $this->request['auth'] = true;
      }
      $this->request['uri']['path'] = 'feeds/api/users/' . $query['conditions']['username'] . '/uploads';
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns related videos
   *
   *      YouTubeVideo::find('related'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - videoid Integer (required)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findRelated($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      if (empty($query['conditions']['videoid'])) {
        return false;
      }
      $this->request['uri']['path'] = 'feeds/api/videos/' . $query['conditions']['videoid'] . 'related';
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns related videos
   *
   *      YouTubeVideo::find('responses'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - videoid Integer (required)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findResponses($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      if (empty($query['conditions']['videoid'])) {
        return false;
      }
      $this->request['uri']['path'] = 'feeds/api/videos/' . $query['conditions']['videoid'] . 'responses';
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns a list of videos that a particular user has explicitly flagged as a
   * favorite video
   *
   *      YouTubeVideo::find('favorites'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - username String If omitted, gets logged in user's favorites
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findFavorites($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      if (empty($query['conditions']['username'])) {
        $query['conditions']['username'] = 'default';
        $this->request['auth'] = true;
      }
      $this->request['uri']['path'] = 'feeds/api/users/' . $query['conditions']['username'] . '/favorites';
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns top rated videos
   *
   *      YouTubeVideo::find('topRated'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - regionId (See _regionIdCondition() method)
   *    - category (See _categoryCondition() method)
   *    - time string (See _timeCondition() method)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findTopRated($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/standardfeeds';
      $query = $this->_regionIdCondition($query);
      $this->request['uri']['path'] .= '/top_rated';
      $query = $this->_categoryCondition($query);
      $query = $this->_timeCondition($query);
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns videos most frequently flagged as favorite videos
   *
   *      YouTubeVideo::find('topFavorites'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - regionId (See _regionIdCondition() method)
   *    - category (See _categoryCondition() method)
   *    - time string (See _timeCondition() method)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findTopFavorites($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/standardfeeds';
      $query = $this->_regionIdCondition($query);
      $this->request['uri']['path'] .= '/top_favorites';
      $query = $this->_categoryCondition($query);
      $query = $this->_timeCondition($query);
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns the most frequently watched YouTube videos
   *
   *      YouTubeVideo::find('mostViewed'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - regionId (See _regionIdCondition() method)
   *    - category (See _categoryCondition() method)
   *    - time string (See _timeCondition() method)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findMostViewed($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/standardfeeds';
      $query = $this->_regionIdCondition($query);
      $this->request['uri']['path'] .= '/most_viewed';
      $query = $this->_categoryCondition($query);
      $query = $this->_timeCondition($query);
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns the most popular YouTube videos, selected using an algorithm that
   * combines many different signals to determine overall popularity.
   *
   *      YouTubeVideo::find('mostPopular'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - regionId (See _regionIdCondition() method)
   *    - category (See _categoryCondition() method)
   *    - time string (See _timeCondition() method)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findMostPopular($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/standardfeeds';
      $query = $this->_regionIdCondition($query);
      $this->request['uri']['path'] .= '/most_popular';
      $query = $this->_categoryCondition($query);
      $query = $this->_timeCondition($query);
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns the videos most recently submitted to YouTube.
   *
   *      YouTubeVideo::find('mostRecent'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - regionId (See _regionIdCondition() method)
   *    - category (See _categoryCondition() method)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findMostRecent($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/standardfeeds';
      $query = $this->_regionIdCondition($query);
      $this->request['uri']['path'] .= '/most_recent';
      $query = $this->_categoryCondition($query);
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns the YouTube videos that have received the most comments.
   *
   *      YouTubeVideo::find('mostDiscussed'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - regionId (See _regionIdCondition() method)
   *    - category (See _categoryCondition() method)
   *    - time string (See _timeCondition() method)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findMostDiscussed($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/standardfeeds';
      $query = $this->_regionIdCondition($query);
      $this->request['uri']['path'] .= '/most_discussed';
      $query = $this->_categoryCondition($query);
      $query = $this->_timeCondition($query);
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns the YouTube videos that receive the most video responses.
   *
   *      YouTubeVideo::find('mostResponded'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - regionId (See _regionIdCondition() method)
   *    - category (See _categoryCondition() method)
   *    - time string (See _timeCondition() method)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findMostResponded($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/standardfeeds';
      $query = $this->_regionIdCondition($query);
      $this->request['uri']['path'] .= '/most_responded';
      $query = $this->_categoryCondition($query);
      $query = $this->_timeCondition($query);
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns the videos recently featured on the YouTube home page or featured
   * videos tab.
   *
   *      YouTubeVideo::find('recentlyFeatured'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - regionId (See _regionIdCondition() method)
   *    - category (See _categoryCondition() method)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findRecentlyFeatured($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/standardfeeds';
      $query = $this->_regionIdCondition($query);
      $this->request['uri']['path'] .= '/recently_featured';
      $query = $this->_categoryCondition($query);
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Returns the videos suitable for playback on mobile devices.
   *
   *      YouTubeVideo::find('watchOnMobile'[, $options]);
   *
   * ** $options **
   *  - conditions
   *    - category (See _categoryCondition() method)
   *  - limit Integer
   *  - page Integer
   *
   * @param string $state 'before' or 'after'
   * @param array $query
   * @param array $results
   * @return array
   */
  protected function _findWatchOnMobile($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'feeds/api/standardfeeds/watch_on_mobile';
      $query = $this->_categoryCondition($query);
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * The API enables you to retrieve region-specific standard feeds by inserting
   * a region ID in the standard feed URL.
   *
   * Note: Region-specific versions of the watch_on_mobile standard feed are not
   * available.
   *
   * @param array $query
   * @return array $query
   */
  protected function _regionIdCondition($query) {
    if (!empty($query['conditions']['regionId']) && array_key_exists($query['conditions']['regionId'], $this->regions)) {
      $this->request['uri']['path'] .= '/' . $query['conditions']['regionId'];
    }
    return $query;
  }

  /**
   * The API also enables you to retrieve category-specific standard feeds by
   * appending an underscore and a category name to the standard feed URL.
   *
   * @todo Enable browsable check with territory condition
   *
   * @param array $query
   * @return array $query
   */
  protected function _categoryCondition($query) {
    if (!empty($query['conditions']['category']) && array_key_exists($query['conditions']['category'], $this->categories())) {
      $this->request['uri']['path'] .= '_' . $query['conditions']['category'];
    }
    return $query;
  }
  
  /**
   * The time parameter restricts the search to videos uploaded within the
   * specified time. Valid values for this parameter are:
   * 
   * - today (1 day)
   * - this_week (7 days)
   * - this_month (1 month)
   * - all_time
   *
   * The default value for this parameter is all_time.
   *
   * This parameter is supported for search feeds as well as for the top_rated,
   * top_favorites, most_viewed, most_popular, most_discussed and most_responded
   * standard feeds.
   *
   * @param array $query
   * @return array Modified query
   */
  protected function _timeCondition($query) {
    if (!empty($query['conditions']['time']) && array_key_exists($query['conditions']['time'], $this->times)) {
      $this->request['uri']['query']['time'] = $query['conditions']['time'];
    }
    return $query;
  }

}
?>
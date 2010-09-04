<?php
/**
 * Plugin model for "You Tube Playlist".
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
class YouTubePlaylist extends GdataAppModel {

  /**
   * The name of this model
   *
   * @var name
   */
  public $name ='YouTubePlaylist';

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
    'id' => array('type' => 'string', 'length' => '255'),
    'title' => array('type' => 'string', 'length' => '255'),
    'summary' => array('type' => 'text'),
    'private' => array('type' => 'boolean'),
  );

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
        'allowEmpty' => false,
        'required' => true,
        'on' => 'create',
      ),
    ),
    'summary' => array(
      'notEmpty' => array(
        'rule' => 'notEmpty',
        'message' => 'Please enter a summary',
        'allowEmpty' => false,
        'required' => true,
        'on' => 'create',
      ),
    ),
    'private' => array(
      'boolean' => array(
        'rule' => 'boolean',
        'message' => 'Value for private must be boolean',
        'allowEmpty' => false,
        'required' => false,
      ),
    ),
  );

  /**
   * The custom
   * 
   * @var array
   */
  public $_findMethods = array(
    'playlists' => true,
  );

  /**
   * Creates a You Tube Playlist.
   *
   * @param array $data See Model::save()
   * @param boolean $validate See Model::save()
   * @param array $fieldList See Model::save()
   * @return boolean
   */
  public function save($data = null, $validate = true, $fieldList = array()) {

    // Create the XML payload
    $doc = new DOMDocument('1.0', 'utf-8');
    $entry = $doc->createElementNS('http://www.w3.org/2005/Atom', 'entry');
    $entry->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:yt', 'http://gdata.youTube.com/schemas/2007');
    $doc->appendChild($entry);
    if (isset($data[$this->alias]['title'])) {
      $title = $doc->createElement('title', $data[$this->alias]['title']);
      $title->setAttribute('type', 'text');
    }
    $entry->appendChild($title);
    if (isset($data[$this->alias]['summary'])) {
      $summary = $doc->createElement('summary', $data[$this->alias]['summary']);
    }
    $entry->appendChild($summary);
    if (!empty($data[$this->alias]['private'])) {
      $private = $doc->createElementNS('http://gdata.youtube.com/schemas/2007', 'yt:private');
      $entry->appendChild($private);
    }

    // Add the content type in so OAuth won't use the body in the signature
    $this->request = array(
      'uri' => array(
        'path' => 'feeds/api/users/default/playlists',
      ),
      'header' => array(
        'Content-Type' => 'application/atom+xml',
      ),
      'auth' => true,
      'body' => $doc->saveXML(),
    );

    $result = parent::save($data, $validate, $fieldList);

    if ($result) {
      $this->setInsertID($this->response['entry']['playlistId']);
    }

    return $result;

  }

  protected function _findPlaylists($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      if (empty($query['conditions']['username'])) {
        $query['conditions']['username'] = 'default';
        $this->request['auth'] = true;
      }
      $this->request['uri']['path'] = 'feeds/api/users/' . $query['conditions']['username'] . '/playlists';
      $query = $this->_paginationParams($query);
      return $query;
    } else {
      return $results;
    }
  }

}

?>
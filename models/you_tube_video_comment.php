<?php
/**
 * Plugin model for "You Tube Video Comment".
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
class YouTubeVideoComment extends GdataAppModel {

  /**
   * The name of this model
   *
   * @var name
   */
  public $name ='YouTubeVideoComment';

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
    'video_id' => array('type' => 'string', 'length' => '255'),
    'reply_to_comment_id' => array('type' => 'string', 'length' => '255'),
    'title' => array('type' => 'string', 'length' => '255'),
    'content' => array('type' => 'text'),
  );

  /**
   * Validation rules
   * 
   * @var array
   */
  public $validate = array(
    'video_id' => array(
      'notEmpty' => array(
        'rule' => 'notEmpty',
        'message' => 'Please enter a video id',
        'allowEmpty' => false,
        'required' => true,
      ),
    ),
    'content' => array(
      'notEmpty' => array(
        'rule' => 'notEmpty',
        'message' => 'Please enter a comment',
        'allowEmpty' => false,
        'required' => true,
      ),
    ),
  );

  /**
   * Creates a comment on a You Tube Video.
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
    if (isset($data[$this->alias]['content'])) {
      $content = $doc->createElement('content', $data[$this->alias]['content']);
    }
    $entry->appendChild($content);

    // Add the content type in so OAuth won't use the body in the signature
    $this->request = array(
      'header' => array(
        'Content-Type' => 'application/atom+xml',
      ),
      'auth' => true,
      'body' => $doc->saveXML(),
    );

    if (isset($data[$this->alias]['video_id'])) {
      $this->request['uri']['path'] = '/feeds/api/videos/' . $data[$this->alias]['video_id'] . '/comments';
    }

    $result = parent::save($data, $validate, $fieldList);

    return $result;

  }

}

?>
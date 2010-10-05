<?php

/**
 * Plugin model for "Google Document".
 *
 * Provides custom find types for the various calls on the web service, mapping
 * familiar CakePHP methods and parameters to the http request params for
 * issuing to the web service.
 *
 * @author Jamie Mill <jamiermill@gmail.com>
 * @link http://jamiemill.com
 * @copyright (c) 2010 Jamie Mill
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class GoogleDocument extends GdataAppModel {

	/**
	 * The name of this model
	 *
	 * @var name
	 */
	public $name = 'GoogleDocument';

	/**
	 * The datasource this model uses
	 *
	 * @var name
	 */
	public $useDbConfig = 'googleDocumentsList';

	/**
	* The fields and their types for the form helper
	*
	* @var array
	*/
	public $_schema = array(
		'id' => array('type' => 'string', 'length' => '255'),
		'title' => array('type' => 'string', 'length' => '255'),
		'type' => array('type' => 'string', 'length' => '255'),
		'file' => array('type' => 'blob'),
	);
	
	/**
	* Validation definition
	*
	* @var array
	*/
	public $validate = array(
		'title' => array(
			'notEmpty'=>array(
				'rule' => 'notEmpty',
				'message' => 'Please enter a valid title.',
				'allowEmpty' => false
			),
			'requiredOnCreate'=>array(
				'rule' => 'notEmpty',
				'message' => 'Please enter a valid title.',
				'allowEmpty' => false,
				'required'=>true,
				'on'=>'create'
			),
		),
	);

	/**
	 * The custom find types
	 * 
	 * @var array
	 */
	public $_findMethods = array(
		'documents' => true,
	);

	protected function _findDocuments($state, $query = array(), $results = array()) {
		if ($state == 'before') {
			$this->request['auth'] = true;
			$this->request['uri']['path'] = 'feeds/documents/private/full';
			if(!empty($query['conditions']['title'])) {
				$this->request['uri']['query']['title'] = $query['conditions']['title'];
			} elseif (!empty($query['conditions']['q'])) {
				$this->request['uri']['query']['q'] = $query['conditions']['q'];
			}
			$query = $this->_paginationParams($query);
			return $query;
		} else {
			return $results;
		}
	}

	/**
	 * Creates the API XML request containing the meta data and adds it to the
	 * request body.
	 * 
	 * If a 'file' key contains a file upload array, then the request is set to
	 * multipart mode and the file attached to send the upload to Google.
	 *
	 * If there is no 'file' key then a blank document is created, using the 'title'
	 * key as its name. A 'type' key may also be supplied containing 'document' or
	 * 'spreadsheet'. Other types are not tested yet. 
	 * 
	 * Note that the 'type' key is not necessary when uploading a file, as google
	 * seems to work that out for itself.
	 *
	 * ClassRegistry::init('Gdata.GoogleDocument')->save(array(
	 *	 'GoogleDocument' => array(
	 *		 'file' => array(
	 *			 'name' => 'my text document.doc',
	 *			 'type' => 'application/msword',
	 *			 'tmp_name' => '/tmp/asdkjhkjhkj',
	 *			 'error' => 0,
	 *			 'size' => 863102))));
	 *
	 * @param array $data See Model::save()
	 * @param boolean $validate See Model::save()
	 * @param array $fieldList See Model::save()
	 * @return boolean
	 */
	public function save($data = null, $validate = true, $fieldList = array()) {

		// Create the XML containing the meta data about the video
		$doc = new DOMDocument('1.0', 'utf-8');
		$entry = $doc->createElementNS('http://www.w3.org/2005/Atom', 'atom:entry');
		$doc->appendChild($entry);
		
		$type = 'document';
		if(!empty($data[$this->alias]['type'])) {
			$type = $data[$this->alias]['type'];
		}

		$category = $doc->createElement('atom:category');
		$category->setAttribute('scheme' ,'http://schemas.google.com/g/2005#kind');
		$category->setAttribute('term' ,'http://schemas.google.com/docs/2007#'.$type);
		$category->setAttribute('label' ,$type);
		$entry->appendChild($category);

		$category = $doc->createElementNS('http://www.w3.org/2005/Atom', 'atom:title', $data[$this->alias]['title']);
		$entry->appendChild($category);
		
		$body = '';
		$mainContentType  = 'application/atom+xml';
		$hasFileUpload = !empty($data[$this->alias]['file']);
		
		if($hasFileUpload) {
			$boundaryString = 'Next_Part_' . String::uuid();
			$mainContentType = 'multipart/related; boundary="' . $boundaryString . '"';
			$body.= "--$boundaryString\r\n";
			$body.= "Content-Type: application/atom+xml; charset=UTF-8\r\n";
			$body.= "\r\n";
		}
		
		$body.= $doc->saveXML()."\r\n";
		
		if($hasFileUpload) {
			$body.= "--$boundaryString\r\n";
			$body.= "Content-Type: {$data[$this->alias]['file']['type']}\r\n";
			$body.= "Content-Transfer-Encoding: binary\r\n";
			$body.= "\r\n";
			$body.= file_get_contents($data[$this->alias]['file']['tmp_name'])."\r\n";
			$body.= "\r\n";
			$body.= "--$boundaryString--\r\n";
		}

		$this->request = array(
			'uri' => array(
				'host' => 'docs.google.com',
				'path' => '/feeds/documents/private/full',
			),
			'header' => array(
				'Content-Type' => $mainContentType,
				'Slug' => $data[$this->alias]['title']
			),
			'auth' => array(
				'method' => 'OAuth',
			),
			'body' => $body,
		);
		
		$result = parent::save($data, $validate, $fieldList);
		
		if($result){
			// In Google's documentation it looks like there should be a gd:resourceId node, but it appears 
			// as simply resourceId to us. Keep an eye on this.
			if(empty($this->response['entry']['resourceId'])) {
				trigger_error('No resourceId from google.');
				return false;
			}
			$this->setInsertID($this->response['entry']['resourceId']);
		}
		
		return $result;

	}

}

?>
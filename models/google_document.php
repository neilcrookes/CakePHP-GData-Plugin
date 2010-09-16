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
		'file' => array('type' => 'blob'),
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
			$query = $this->_paginationParams($query);
			return $query;
		} else {
			return $results;
		}
	}

	/**
	 * Creates the API XML request containing the meta data and adds it to the
	 * request body along with the file as multipart data, and sets other values
	 * in the request array required for uploading a document to Google Docs.
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

		$category = $doc->createElement('atom:category');
		$category->setAttribute('scheme' ,'http://schemas.google.com/g/2005#kind');
		$category->setAttribute('term' ,'http://schemas.google.com/docs/2007#document');
		$category->setAttribute('label' ,'document');
		$entry->appendChild($category);

		$category = $doc->createElementNS('http://www.w3.org/2005/Atom', 'atom:title', $data[$this->alias]['file']['name']);
		$entry->appendChild($category);

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
				'host' => 'docs.google.com',
				'path' => '/feeds/documents/private/full',
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
		
		$result = parent::save($data, $validate, $fieldList);
		
		if($result){
			$this->setInsertID($this->response['entry']['id']);
		}
		
		return $result;

	}

}

?>
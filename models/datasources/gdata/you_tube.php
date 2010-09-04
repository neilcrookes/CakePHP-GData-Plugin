<?php
/**
 * DataSource for the Gdata YouTube API
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class YouTube extends GdataSource {

  /**
   * The scope for this datasource that must be supplied to Google when getting
   * an OAuth request token
   * 
   * @var string
   */
  public $oAuthScope = 'http://gdata.youtube.com';

  /**
   * Inserts the default hostname in the request uri if not already set and
   * calls the parent request() method.
   * 
   * @param Model $model The model object that operation is performed on,
   * @return mixed The response from the parent datasource request()
   */
  public function request(&$model) {

    if (!isset($model->request['uri']['host'])) {
      $model->request['uri']['host'] = 'gdata.youtube.com';
    }

    $response = parent::request($model);

    return $response;

  }
  
}
?>
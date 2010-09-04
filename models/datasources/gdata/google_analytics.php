<?php
/**
 * DataSource for the Gdata Google Analytics API
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class GoogleAnalytics extends GdataSource {

  /**
   * The scope for this datasource that must be supplied to Google when getting
   * an OAuth request token
   *
   * @var string
   */
  public $oAuthScope = 'https://www.google.com/analytics/feeds/';

  /**
   * Inserts the default hostname and scheme in the request uri if not already
   * set and prefixes the path of the URI then calls the parent request() method
   *
   * @param Model $model The model object that operation is performed on,
   * @return mixed The response from the parent datasource request()
   */
  public function request(&$model) {

    if (!isset($model->request['uri']['host'])) {
      $model->request['uri']['host'] = 'www.google.com';
    }

    if (!isset($model->request['uri']['scheme'])) {
      $model->request['uri']['scheme'] = 'https';
    }

    $model->request['uri']['path'] = 'analytics/feeds/' . $model->request['uri']['path'];

    $response = parent::request($model);

    return $response;

  }

}
?>
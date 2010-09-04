<?php
/**
 * Configuration options for datasources for Gdata API.
 *
 * There are two ways you can set the configuration options for the GData API
 * Datasources included in this plugin:
 *
 * 1. Add a datasource config into your app/config/database.php file
 * 2. Rename or copy plugins/gdata/config/gdata_config.default.php file to
 * plugins/gdata/config/gdata_config.php and add the configuration options
 * there. Note you only need to do this if specific configuration options are
 * required by the particular GData API you are using. Some GData APIs e.g.
 * Google Analytics don't need any, so you don't have to do anything.
 *
 * Option 1 is good if you are happy for the configuration options to be
 * separate from the plugin. This is the approach recommended by CakePHP.
 * Sometimes however you may prefer to bundle the config settings for the plugin
 * inside the plugin itself. In which case use option 2.
 *
 * With option 1, CakePHP's ConnectionManager is aware of the datasource by the
 * time a model, whose useDbConfig property is set to that datasource, is
 * constructed. However, with option 2, it isn't, so the GdataAppModel
 * constructor adds the selected model's datasource, with it's configuration
 * options from the file in plugins/gdata/config/gdata_config.php if present, to
 * the ConnectionManager's list of known sources, so that when the parent's
 * constructor is run, the ConnectionManager *is* aware of the datasource.
 *
 * Option 1 requires the full configuration array whereas with option 2 you get
 * some configuration options dynamically created for you, such as the
 * datasource and driver options and you can specify some common extra options,
 * or override those ones in the default property and in the property for that
 * model's useDbConfig param, which will be merged with the default ones.
 *
 * With option 1, the minimum you have to add to your database.php looks like:
 *
 *      var $youTube = array(
 *        'datasource' => 'Gdata.GdataSource',
 *        'driver' => 'youTube',
 *        'X-GData-Key' => '', // Add this line if required by API
 *      );
 *
 * With option 2, the minimum you have to add to your gdata_config.php looks
 * like (if the API requires a developer key, or nothing at all if it doesn't):
 *
 *      var $youTube = array(
 *        'X-GData-Key' => '', // Add this
 *      );
 *
 * In addition to datasource, driver, X-GData-Key, you can also specify the
 * following (values show defaults):
 * 
 *        'GData-Version' => 2,
 *        'oauth_consumer_key' => 'anonymous',
 *        'oauth_consumer_secret' => 'anonymous',
 *        'oauth_token',
 *        'oauth_token_secret',
 *        'xoauth_displayname'
 *
 * The GData plugin uses OAuth for authentication with Google's services. It can
 * work in 2 ways, the first is whereby the oauth params are not hardcoded in
 * the plugin config. It's up to your application to dynamically add these in to
 * the datasource config property before you call an action on a model that uses
 * that datasource that requires authentication. This is useful for applications
 * that need to interact with multiple Google Accounts. You can use the
 * functions in the plugin to obtain the oauth_token and oauth_token_secret and
 * just use them for the session, or persist them in your database or other
 * storage system. The second is for applications that only ever interact with
 * one Google Account (per datasource) and the oauth_token and
 * oauth_token_secret are hard coded in the plugin config. you can again use the
 * functionality in the plugin to retrieve these and then copy them into you
 * config.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class GDATA_CONFIG {

  /**
   * Configuration options for all datasources for Gdata API
   *
   * @var array
   */
//  public $default = array(
//    'X-GData-Key' => '',
//  );

  /**
   * Configuration options for the Google Analytics datasource
   *
   * @var array
   */
//  var $googleAnalytics = array(
//    'datasource' => 'Gdata.GdataSource',
//    'driver' => 'googleAnalytics',
//  );

  /**
   * Configuration options for the YouTube datasource
   *
   * @var array
   */
//  var $youTube = array(
//    'datasource' => 'Gdata.GdataSource',
//    'driver' => 'youTube',
//    'X-GData-Key' => '',
//  );

}
?>

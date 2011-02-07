

Installation
-----------------------------------------------------------

if you use git and want to implement as submodules: 

1. git submodule add https://github.com/neilcrookes/CakePHP-GData-Plugin.git ./app/plugins/gdata
2. git submodule add https://github.com/neilcrookes/CakePHP-ReST-DataSource-Plugin  ./app/plugins/rest

if you prefer to just download the files to their appropriate spots:

1. download https://github.com/neilcrookes/CakePHP-GData-Plugin/ and extract to ./app/plugins/gdata
2. download https://github.com/neilcrookes/CakePHP-ReST-DataSource-Plugin and extract to ./app/plugins/rest

also you'll need to get http_socket_oath (HttpSocket extension):

1. curl https://github.com/neilcrookes/http_socket_oauth/raw/master/http_socket_oauth.php > ./app/vendors/http_socket_oauth.php

Configuration
-----------------------------------------------------------

edit @./app/config/database.php@ and add a configuration for @$googleAnalytics@

	public $googleAnalytics = array(
		'datasource' => 'Gdata.GdataSource',
		'driver' => 'googleAnalytics',
		'oauth_consumer_key' => 'unknown@gmail.com',
		'auth_consumer_secret' => 'unknown',
		'profile-id' => '1234567',
		'X-GData-Key' => 'UA-1234567-1',
		);


Hello World
-----------------------------------------------------------

	App::import('Model', 'Gdata.GoogleAnalytic');
	$this->GoogleAnalytic =& ClassRegistry::init('Gdata.GoogleAnalytic');
	$accounts = $this->GoogleAnalytic->find('accounts');



Refernces
-----------------------------------------------------------

* @author: http://www.neilcrookes.com
* http://www.neilcrookes.com/2009/09/27/get-google-analytics-data-in-your-cakephp/

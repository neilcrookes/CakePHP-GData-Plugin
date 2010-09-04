<?php
/**
 * Sample controller with sample actions - not intended for your apps.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class YouTubeVideosController extends GdataAppController {

  /**
   * Models this controller uses
   *
   * @var array
   */
  public $uses = array('Gdata.YouTubeVideo', 'Gdata.YouTubeVideoComment');

  public $helpers = array('Time');

  /**
   * Sample add() action for uploading a YouTube Video
   */
  public function add() {
    if ($this->GdataAuth->isAuthorized()) {
      if (!empty($this->data)) {
        $this->YouTubeVideo->set($this->data);
        if ($this->YouTubeVideo->validates() && $this->YouTubeVideo->save($this->data)) {
          $this->Session->setFlash('It worked!');
          $this->redirect(array());
        }
      }
      // Set official you tube categories and access control options to populate
      // form fields
      $this->set('categories', $this->YouTubeVideo->categories());
      foreach ($this->YouTubeVideo->accessControls as $accessControl => $options) {
        $this->set(Inflector::pluralize($accessControl), array_combine($options, $options));
      }
    }
  }

  /**
   * Sample index action to display videos matching a search term
   */
  public function index($selectedFeed = 'videos') {
    if (!empty($this->data)) {
      $this->redirect(array_merge(array($selectedFeed), $this->data['YouTubeVideo']));
    }
    $this->paginate['YouTubeVideo'][] = $selectedFeed;
    if (!empty($this->passedArgs)) {
      $this->data['YouTubeVideo'] = $this->paginate['YouTubeVideo']['conditions'] = $this->passedArgs;
    }
    if ($selectedFeed != 'videos' || !empty($this->passedArgs['q'])) {
      if ($selectedFeed != 'favorites' || !empty($this->passedArgs['username']) || $this->GdataAuth->isAuthorized()) {
        $this->set('youTubeVideos', $this->paginate('YouTubeVideo'));
      }
    }
    $feeds = array(
      'videos' => 'Search',
      'topRated' => 'Top Rated',
      'topFavorites' => 'Top Favorites',
      'mostViewed' => 'Most Viewed',
      'mostPopular' => 'Most Popular',
      'mostRecent' => 'Most Recent',
      'mostDiscussed' => 'Most Discussed',
      'mostResponded' => 'Most Responded',
      'recentlyFeatured' => 'Recently Featured',
      'watchOnMobile' => 'Watch On Mobile',
      'favorites' => 'User Favorites',
    );
    $regionIds = $this->YouTubeVideo->regions;
    $categories = $this->YouTubeVideo->categories();
    $times = $this->YouTubeVideo->times;
    $this->set(compact('selectedFeed', 'feeds', 'regionIds', 'categories', 'times'));
  }

  /**
   * Sample view() action for viewing a You Tube Video and adding a comment.
   * 
   * @param string $videoId
   */
  public function view($videoId = null) {
    if (!$videoId) {
      $this->cakeError('error404', 'No video selected');
    }
    $youTubeVideo = $this->YouTubeVideo->find('first', array('conditions' => array('videoid' => $videoId)));
    if (!$youTubeVideo) {
      $this->cakeError('error404', 'Invalid video selected');
    }
    $this->set(compact('youTubeVideo'));
    if (!empty($this->data['YouTubeVideoComment'])) {
      if ($this->GdataAuth->isAuthorized()) {
        $this->data['YouTubeVideoComment']['video_id'] = $videoId;
        if ($this->YouTubeVideoComment->save($this->data)) {
          $this->Session->setFlash('It worked!');
          $this->redirect(array($videoId));
        } else {
          $this->Session->setFlash('It didn\'t work!');
        }
      } else {
        $this->Session->setFlash('You are not authorized!');
      }
    }
  }

}
?>
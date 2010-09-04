<?php
class YouTubePlaylistsController extends GdataAppController {
  public $paginate = array(
    'YouTubePlaylist' => array(
      'playlists',
    ),
  );
  public function add() {
    if (!empty($this->data)) {
      if ($this->GdataAuth->isAuthorized()) {
        if ($this->YouTubePlaylist->save($this->data)) {
          $this->redirect(array());
          $this->Session->setFlash(__('It worked!'));
        } else {
          $this->Session->setFlash(__('It didn\t work'));
        }
      } else {
        $this->Session->setFlash(__('You must be logged in to create a playlist'));
      }
    }
  }
  public function index() {
    if (!empty($this->data)) {
      $this->redirect($this->data['YouTubePlaylist']);
    }
    if (!empty($this->passedArgs['username']) || $this->GdataAuth->isAuthorized()) {
      if (!empty($this->passedArgs['username'])) {
        $this->paginate['YouTubePlaylist']['conditions']['username'] = $this->data['YouTubePlaylist']['username'] = $this->passedArgs['username'];
      }
      $this->set('youTubePlaylists', $this->paginate('YouTubePlaylist'));
    }
  }
}
?>

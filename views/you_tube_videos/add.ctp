<?php
/**
 * Sample add() view for adding a you tube video - not intended for your apps.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
echo $this->Session->flash();
if ($this->Session->check('Gdata.Auth.youTube.is_logged_in')) {
  echo $this->Form->create('YouTubeVideo', array('type' => 'file'));
  echo $this->Form->input('file', array('type' => 'file'));
  echo $this->Form->input('title');
  echo $this->Form->input('description');
  echo $this->Form->input('category', array('empty' => true));
  echo $this->Form->input('keywords');
  echo $this->Form->input('rate', array('type' => 'radio'));
  echo $this->Form->input('comment', array('type' => 'radio'));
  echo $this->Form->input('commentVote', array('type' => 'radio'));
  echo $this->Form->input('videoRespond', array('type' => 'radio'));
  echo $this->Form->input('embed', array('type' => 'radio'));
  echo $this->Form->input('syndicate', array('type' => 'radio'));
  echo $this->Form->input('private');
  echo $this->Form->end('Upload');
} else {
  echo $this->element('oauth_login_link', array('useDbConfig' => 'youTube'));
}
?>
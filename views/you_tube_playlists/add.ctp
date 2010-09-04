<?php
/**
 * Sample add() view for adding a you tube playlist - not intended for your apps.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
echo $this->Session->flash();
if ($this->Session->check('Gdata.Auth.youTube.is_logged_in')) {
  echo $this->Form->create('YouTubePlaylist');
  echo $this->Form->input('title');
  echo $this->Form->input('summary');
  echo $this->Form->input('private');
  echo $this->Form->end('Create');
} else {
  echo $this->element('oauth_login_link');
}
?>
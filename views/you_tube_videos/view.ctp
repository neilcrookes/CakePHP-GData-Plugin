<?php
/**
 * Sample view() view for viewing a you tube video and adding a comment - not
 * intended for your apps.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
echo $this->Session->flash();
echo $this->Html->image('http://i3.ytimg.com/vi/'.$this->params['pass'][0].'/default.jpg');
if ($this->Session->read('Gdata.Auth.youTube.is_logged_in')) {
  echo $this->Form->create('YouTubeVideoComment', array('url' => array('controller' => 'you_tube_videos', 'action' => 'view', $this->params['pass'][0])));
  echo $this->Form->input('content');
  echo $this->Form->end('Post comment');
} else {
  echo $this->element('oauth_login_link', array('useDbConfig' => 'youTube', 'linkText' => 'Sign in with your Google Account to comment'));
}
?>

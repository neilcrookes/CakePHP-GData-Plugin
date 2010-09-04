<?php
/* 
 * 
 */
echo $this->Form->create('YouTubePlaylist', array('url' => array('action' => 'index')));
if (!$this->Session->check('Gdata.Auth.youTube.is_logged_in')) {
  echo $this->element('oauth_login_link', array('useDbConfig' => 'youTube', 'linkText' => 'Sign in with your Google Account to display your playlists'));
  __(' or enter a YouTube username below');
} else {
  __('Enter a YouTube username below or leave blank to show your playlists');
}
echo $this->Form->input('username');
echo $this->Form->end('Show');
pr($youTubePlaylists);
?>

<?php if (!empty($youTubePlaylists['feed']['entry'])) : ?>
  <?php
    $this->Paginator->options(array('url' => $this->passedArgs));
    echo $this->Paginator->counter(array(
      'format' => 'Page %page% of %pages%, showing records %start% to %end% out of %count%'
    ));
  ?>
  <table>
  <?php foreach ($youTubePlaylists['feed']['entry'] as $youTubePlaylist) : ?>
    <tr>
    <td>
      <?php
        pr($youTubePlaylist);
      ?>
    </td>
  </tr>
  <?php endforeach; ?>
  </table>
  <?php
    echo $this->Paginator->prev('< Previous ', null, null, array('class' => 'disabled'));
    echo $this->Paginator->numbers();
    echo $this->Paginator->next(' Next >', null, null, array('class' => 'disabled'));
  ?>
<?php else : ?>
  <p>Sorry, there are no matching playlists</p>
<?php endif; ?>
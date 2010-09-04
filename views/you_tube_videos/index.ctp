<style type="text/css">
  #feeds li {display:inline;}

</style>
<ul id="feeds">
  <?php foreach ($feeds as $feedParam => $feedLabel) : ?>
    <li>
      <?php
      if ($feedParam != $selectedFeed) {
        echo $this->Html->link($feedLabel, array($feedParam));
      } else {
        echo $feedLabel;
      }
      ?>
    </li>
  <?php endforeach; ?>
</ul>

<?php
echo $this->Form->create('YouTubeVideo', array('url' => array('action' => 'index', $selectedFeed)));
if (in_array($selectedFeed, array('topRated', 'topFavorites', 'mostViewed', 'mostPopular', 'mostRecent', 'mostDiscussed', 'mostResponded', 'recentlyFeatured'))) {
  echo $this->Form->input('regionId', array('empty' => __('All regions', true)));
}
if (in_array($selectedFeed, array('topRated', 'topFavorites', 'mostViewed', 'mostPopular', 'mostRecent', 'mostDiscussed', 'mostResponded', 'recentlyFeatured', 'watchOnMobile'))) {
  echo $this->Form->input('category', array('empty' => __('All categories', true)));
}
if (in_array($selectedFeed, array('topRated', 'topFavorites', 'mostViewed', 'mostPopular', 'mostDiscussed', 'mostResponded'))) {
  echo $this->Form->input('time', array('default' => 'all_time'));
}
if (in_array($selectedFeed, array('videos'))) {
  echo $this->Form->input('q', array('label' => 'Search'));
}
if ($selectedFeed == 'favorites') {
  $options = array();
  if (!$this->Session->check('Gdata.Auth.youTube.is_logged_in')) {
    echo $this->element('oauth_login_link', array('useDbConfig' => 'youTube', 'linkText' => 'Sign in with your Google Account to display your favourites'));
    __(' or enter a YouTube username below');
  } else {
    __('Enter a YouTube username below or leave blank to show your favorites');
  }
  echo $this->Form->input('username', $options);
}
echo $this->Form->end('Show');
?>

<?php if (!empty($youTubeVideos['feed']['entry'])) : ?>
  <?php
    $this->Paginator->options(array('url' => $this->passedArgs));
    echo $this->Paginator->counter(array(
      'format' => 'Page %page% of %pages%, showing records %start% to %end% out of %count%'
    ));
  ?>
  <table>
  <?php foreach ($youTubeVideos['feed']['entry'] as $youTubeVideo) : ?>
    <tr>
    <td>
      <?php
        $thumbnail = current($youTubeVideo['group']['thumbnail']);
        $image = $this->Html->image($thumbnail['url'], array(
          'width' => $thumbnail['width'],
          'height' => $thumbnail['height'],
          'alt' => $thumbnail['time'],
        ));
        $url = $youTubeVideo['group']['player']['url'];
        echo $this->Html->link($image, $url, array('escape' => false));
      ?>
    </td>
    <td>
      <?php echo $this->Html->link($youTubeVideo['title'], $url, array('escape' => false)); ?><br />
      <?php echo $youTubeVideo['group']['duration']['seconds']; ?> secs
      by
      <?php echo $this->Html->link($youTubeVideo['author']['name'], $youTubeVideo['author']['uri'], array('escape' => false)); ?><br />
      <abbr title="<?php echo $youTubeVideo['group']['description']['value']; ?>">Description</abbr>
      <abbr title="<?php echo $youTubeVideo['group']['keywords']; ?>">Keywords</abbr>
    </td>
    <td>
      Comments: <?php echo $this->Html->link($youTubeVideo['comments']['feedLink']['countHint'], $youTubeVideo['comments']['feedLink']['href'], array('escape' => false)); ?><br />
      Rating: <?php echo $youTubeVideo['rating'][0]['average']; ?> from <?php echo $youTubeVideo['rating'][0]['numRaters']; ?><br />
      Favorited: <?php echo $youTubeVideo['statistics']['favoriteCount']; ?><br />
      Views: <?php echo $youTubeVideo['statistics']['viewCount']; ?><br />
      Likes: <?php echo $youTubeVideo['rating'][1]['numLikes']; ?><br />
      Dislikes <?php echo $youTubeVideo['rating'][1]['numDislikes']; ?>
    </td>
    <td>
      Published: <?php echo $this->Time->niceShort($youTubeVideo['published']); ?><br />
      Updated: <?php echo $this->Time->niceShort($youTubeVideo['updated']); ?>
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
  <p>Sorry, there are no matching videos</p>
<?php endif; ?>
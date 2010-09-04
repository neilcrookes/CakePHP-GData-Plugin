<?php
if ($session->read('Gdata.Auth.googleAnalytics.is_logged_in')) {
  if ($accounts) {
    echo '<h2>' . $accounts['feed']['title'] . '</h2>';
    $this->Paginator->options(array('url' => $this->passedArgs));
    echo $this->Paginator->numbers();
    echo $this->Paginator->prev('< Previous ', null, null, array('class' => 'disabled'));
    echo $this->Paginator->next(' Next >', null, null, array('class' => 'disabled'));
    echo $this->Paginator->counter(array(
      'format' => 'Page %page% of %pages%, showing records %start% to %end% out of %count%'
    ));
    foreach ($accounts['feed']['entry'] as $account) {
      echo '<li>' . $this->Html->link($account['title'], array('action' => 'view', 'ids' => str_replace('ga:', '', $account['tableId']))) . '</li>';
    }
  } else {
    echo '<p>' . __('You are not authorized to view any accounts', true) . '</p>';
  }
} else {
  echo $this->element('oauth_login_link', array('useDbConfig' => 'googleAnalytics'));
}
?>
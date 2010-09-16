<?php
// Scope must be set
if (!isset($useDbConfig)) {
  __('Pass in the db config');
  return;
}
if (!isset($linkText)) {
  $linkText = __('Sign in with your Google Account', true);
}
echo $this->Html->link(
  $linkText,
  array(
    'plugin' => 'gdata',
    'action' => 'get_gdata_oauth_request_token',
    $useDbConfig,
    strtr(base64_encode('/'.$this->params['url']['url']), '+/=', '-_,')
  )
);
?>
<?php
/**
 * Sample view() view for querying google analytics - not intended for your
 * apps.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
?>
<?php if ($this->Session->read('Gdata.Auth.googleAnalytics.is_logged_in')) : ?>
  <style type="text/css">
    * {
      font:12px verdana,helvetica,arial,sans-serif;
    }
    .checkbox {
      float:left;
      width:245px;
      clear:none;
      margin-bottom:0;
      padding:0;
    }
    fieldset {
      padding:0 0 0 0.5em;
      margin:0;
      border:0;
    }
    fieldset div {
      display:none;
    }
    legend {
      border-bottom:1px dotted;
      padding:0;
      cursor:pointer;
    }
    abbr {
      cursor:help;
    }
    ul {
      margin:1em 0;
      padding:0;
    }
    li {
      list-style:none;
      float:left;
      margin-right:0.5em;
    }
    table {
      border-collapse:collapse;
    }
    th,td {
      border:1px solid #999;
      padding:1px 3px;
    }
  </style>
  <?php
    echo $this->Form->create('GoogleAnalytic', array('action' => 'view'));
    echo $this->Form->input('ids', array('after' => 'Enter the profile id integer(s), comma separated, e.g. 1234567,7654321'));
    echo $this->Form->input('dimensions', array('multiple' => 'checkbox', 'escape' => false));
    echo $this->Form->input('metrics', array('multiple' => 'checkbox', 'escape' => false));
    echo $this->Form->input('filters');
    echo $this->Form->input('segment');
    echo $this->Form->input('start-date', array('type' => 'date', 'dateFormat' => 'DMY'));
    echo $this->Form->input('end-date', array('type' => 'date', 'dateFormat' => 'DMY'));
    echo $this->Form->end('Show');
  ?>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
  <script type="text/javascript">
    $("legend").click(function() { $(this).nextAll().toggle(); });
  </script>
  <?php
  if (isset($results) && $results) :
    $this->Paginator->options(array('url' => $this->passedArgs));
    echo $this->Paginator->numbers();
    echo $this->Paginator->prev('< Previous ', null, null, array('class' => 'disabled'));
    echo $this->Paginator->next(' Next >', null, null, array('class' => 'disabled'));
    echo $this->Paginator->counter(array(
      'format' => 'Page %page% of %pages%, showing records %start% to %end% out of %count%'
    ));
    ?>
    <table>
      <thead>
        <tr>
          <?php
          function arrayalise($var) {
            if (isset($var[0])) {
              return $var;
            }
            return array($var);
          }
          $results['feed']['entry'] = arrayalise($results['feed']['entry']);
          foreach (arrayalise($results['feed']['entry'][0]['dimension']) as $dimension) {
            echo '<th>'.$this->Paginator->sort(substr($dimension['name'], 3)).'</th>';
          }
          foreach (arrayalise($results['feed']['entry'][0]['metric']) as $metric) {
            echo '<th>'.$this->Paginator->sort(substr($metric['name'], 3)).'</th>';
          }
          ?>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($results['feed']['entry'] as $entry) {
          echo '<tr>';
          foreach (arrayalise($entry['dimension']) as $dimension) {
            echo '<td>'.$dimension['value'].'</td>';
          }
          foreach (arrayalise($entry['metric']) as $metric) {
            echo '<td>'.$metric['value'].'</td>';
          }
          echo '</tr>';
        }
        ?>
      </tbody>
    </table>
  <?php
  elseif (isset($results) && !$results) :
    echo '<p>Invalid combination, here\'s a list of <a href="http://code.google.com/apis/analytics/docs/gdata/gdataReferenceValidCombos.html">valid combinations</a></p>';
  else : ?>
    <p>No results</p>
  <?php endif; ?>
<?php
else :
  echo $this->element('oauth_login_link', array('useDbConfig' => 'googleAnalytics'));
endif;
?>
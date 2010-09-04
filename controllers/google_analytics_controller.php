<?php
/**
 * Sample controller with sample actions - not intended for your apps.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class GoogleAnalyticsController extends GdataAppController {

  /**
   * Sample action for listing the accounts the logged in user has access to
   */
  public function index() {
    if ($this->GdataAuth->isAuthorized()) {
      // Set paginate() to use the custom find type
      $this->paginate['GoogleAnalytic'][] = 'accounts';
      $this->set('accounts', $this->paginate('GoogleAnalytic'));
    }
  }

  /**
   * Sample action to demo fetching and displaying data from google analytics
   */
  function view() {

    if ($this->GdataAuth->isAuthorized()) {

      // If the user has submitted the form, construct redirect url and redirect
      if (!empty($this->data)) {

        $redirect = $this->data['GoogleAnalytic'];

        foreach ($redirect as $k => $v) {

          // Format dates as yyyy-mm-dd as required by google analytics
          // start-date and end-date parameters and in a format suitable for
          // URLs and comma separate multiple value fields, again as required by
          // google analytics and in a format suitable for URLs
          if (strpos($k, 'date')) {
            $v = $v['year'].'-'.$v['month'].'-'.$v['day'];
          } elseif (is_array($v)) {
            $v = implode(',', $v);
          }

          $redirect[$k] = $v;

        }

        $this->redirect($redirect);

      // If we have the minimum required params in the URL in order to issue
      // a request to Google Analytics
      } elseif (array_diff_key(array_flip(array('ids', 'metrics', 'dimensions', 'start-date', 'end-date')), $this->passedArgs) == array()) {

        // Set up pagination conditions
        $this->paginate['GoogleAnalytic']['conditions'] = array_intersect_key(
          $this->passedArgs,
          array_flip(array('start-date', 'end-date', 'filters', 'segments', 'ids'))
        );

        // Metrics could be added to conditions but conceptually, they fit
        // better in the fields param
        $this->paginate['GoogleAnalytic']['fields'] = $this->passedArgs['metrics'];

        // Dimensions could be added to conditions but conceptually, they fit
        // better in the group param
        $this->paginate['GoogleAnalytic']['group'] = $this->passedArgs['dimensions'];

        // Set paginate() to use the custom find type
        $this->paginate['GoogleAnalytic'][] = 'data';

        $this->set('results', $this->paginate('GoogleAnalytic'));

      }

      // Load url params into this->data to re-populate form values
      $this->data['GoogleAnalytic'] = $this->passedArgs;
      
      // Separate out the comma separated values
      foreach ($this->data['GoogleAnalytic'] as $k => $v) {
        if (in_array($k, array('metrics', 'dimensions'))) {
          $this->data['GoogleAnalytic'][$k] = explode(',', $v);
        }
      }

      // Set these model properties to be available in the view in order to
      // populate the options for the multi checkbox fields
      $metrics = $this->GoogleAnalytic->metrics;
      $dimensions = $this->GoogleAnalytic->dimensions;
      foreach ($metrics as $category => $categoryMetrics) {
        foreach ($categoryMetrics as $metric => $description) {
          $metrics[$category][$metric] = '<abbr title="'.$description.'">'.$metric.'</abbr>';
        }
      }
      foreach ($dimensions as $category => $categoryDimensions) {
        foreach ($categoryDimensions as $dimension => $description) {
          $dimensions[$category][$dimension] = '<abbr title="'.$description.'">'.$dimension.'</abbr>';
        }
      }
      $this->set(compact('metrics', 'dimensions'));

    }
    
  }

}
?>
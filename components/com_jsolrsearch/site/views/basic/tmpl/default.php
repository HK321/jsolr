<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.
 *
 *   The JSolrSearch Component for Joomla! is free software: you can redistribute it 
 *   and/or modify it under the terms of the GNU General Public License as 
 *   published by the Free Software Foundation, either version 3 of the License, 
 *   or (at your option) any later version.
 *
 *   The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
 *   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with the JSolrSearch Component for Joomla!.  If not, see 
 *   <http://www.gnu.org/licenses/>.
 *
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Hayden Young <haydenyoung@wijiti.com>
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$document = JFactory::getDocument();
$document->addScript('/media/com_jsolrsearch/js/jquery/jquery.js');
$document->addScript('/media/com_jsolrsearch/js/jsolrsearch.js');

$document->addStyleSheet('/media/com_jsolrsearch/css/jsolrsearch.css');
$document->addStyleSheet('/media/com_jsolrsearch/css/dropdown.css');

?>
<?php echo $this->loadFormTemplate()?>

<?php if (!is_null($this->items)): ?>
   <div class="jsolr-results">
   <?php
   if (!count($this->items)) {
      echo '<span>' . JText::_(COM_JSOLRSEARCH_NO_RESULTS) . '</span>';
   }
   foreach ($this->items as $item) :
          echo $this->loadResultTemplate($item);
   endforeach;
   ?>
   </div>
      
   <div class="pagination jsolr-pagination">
      <?php echo $this->get("Pagination")->getPagesLinks(); ?>
   </div>
<?php endif ?>
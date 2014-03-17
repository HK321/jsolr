<?php
/**
 * Renders a calendar search tool form field. Filters the results displayed by 
 * a period of time.
 * 
 * @package		JSolr
 * @subpackage	Form
 * @copyright	Copyright (C) 2013-2014 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSpace component for Joomla!.

   The JSpace component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSpace component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSpace component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Michał Kocztorz				<michalkocztorz@wijiti.com>
 * Hayden Young					<haydenyoung@knowledgearc.com>
 * 
 */

defined('JPATH_BASE') or die;

jimport('jsolr.form.fields.searchtool');
jimport('jsolr.form.fields.filterable');

class JSolrFormFieldCalendarTool extends JSolrFormFieldSearchTool implements JSolrFilterable
{
	/**
	 * The form field type.
	 *
	 * @var         string
	 * @since       1.6
	 */
	protected $type = 'JSolr.CalendarTool';
	
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
	
		foreach ($this->element->children() as $option) {
			// Only add <option /> elements.
			if ($option->getName() != 'option') {
				continue;
			}
			
			$value = JArrayHelper::getValue($option, 'value', null, 'string');
			
			$selected = $value == $this->value;

			$uri = clone JSolrSearchFactory::getSearchRoute();
			
			if (!empty($value)) {
				$uri->setVar($this->name, $value);
			} else {
				$uri->delVar($this->name);
			}

			$link = '<a role="menuitem" tabindex="-1" href="'.((string)$uri).'">'.JText::_(trim((string)$option)).'</a>';

			$tmp = '<li role="presentation"'.( $selected ? ' class="active" ' : '').' data-value="'.$value.'">'.$link.'</li>';
	
	
			// Add the option object to the result set.
			$options[] = $tmp;
		}
		
		if ($this->show_custom) {		
			$dataValue = '';
			$cssClass = '';
			
			if (($min = $this->_getMinInput()) && ($max = $this->_getMaxInput())) {
				$selected = true;
				$dataValue = "$min,$max";
				$cssClass = 'class="active"';
			}
			
			$link = <<<HTML
<a 
	role="menuitem" 
	tabindex="-1" 
	href="#custom-dates" 
	id="calendar-picker">Custom...</a>
HTML;
				
			$tmp = <<<HTML
<li 
	role="presentation" 
	$cssClass
	date-value="$dataValue">$link</li>
HTML;
			
			$options[] = $tmp;
		}
		
		reset($options);
	
		return $options;
	}
	
	protected function getSelectedLabel() {
		if (($min = $this->_getMinInput()) && ($max = $this->_getMaxInput())) {
			return $min." - ".$max;
		} else {		
			foreach ($this->element->children() as $option) {
				// Only add <option /> elements.
				if ($option->getName() != 'option') {
					continue;
				}
		
				$selected = ((string) $option['value']) == $this->value;
				if( $selected ) {
					return trim((string) $option);
				}
		
			}
		}
		
		return "";
	}
	
	/**
	 * Gets the date filter based on the currently selected value.
	 * 
	 * @return array An array containing a single date filter based on the 
	 * currently selected value.
	 * 
	 * @see JSolrFilterable::getFilters()
	 */
	public function getFilters()
	{	
		$filters = array();
		
		if (($min = $this->_getMinInput()) && ($max = $this->_getMaxInput())) {	
			$filters[] = $this->filter.":[".$min."T00:00:00Z TO ".$max."T11:59:59Z]";
		} else {		
			foreach ($this->element->children() as $option) {
				// Only use <option /> elements.
				if ($option->getName() != 'option') {
					continue;
				}
				
				$value = JArrayHelper::getValue($option, 'value', null, 'string');
				
				if ($this->value && $value == $this->value) {
					$filter = JArrayHelper::getValue($option, 'filter', null, 'string');
					$filters[] = $this->filter.":".$filter;
					continue;
				}
			}
		}

		return (count($filters)) ? $filters : array();
	}
	
	public function __get($name)
	{
		switch ($name) {
			case 'filter':
				return JArrayHelper::getValue($this->element, $name, null, 'string');
				break;
								
			case 'filter_quoted':
			case 'show_custom':
				if (JArrayHelper::getValue($this->element, $name, null, 'string') === 'true')
					return true;
				else
					return false;
				break;
	
			default:
				return parent::__get($name);
		}
	}
	
	/**
	 * Gets the min range input by the user if specifying a custom date. 
	 * If custom date is not used or min date is not specified, null is 
	 * returned.
	 * 
	 * @return string The min range input or null if custom date is not used 
	 * or min date is not specified.
	 */
	private function _getMinInput()
	{
		$dateParts = explode(",", JFactory::getApplication()->input->getString('qdr'));
		$minParts = explode(":", JArrayHelper::getValue($dateParts, 0));
		
		if (JArrayHelper::getValue($minParts, 0) == 'min' && 
			($min = JArrayHelper::getValue($minParts, 1))) {
			return $min;
		} else {
			return null;
		}
	}
	
	/**
	 * Gets the max range input by the user if specifying a custom date.
	 * If custom date is not used or max date is not specified, null is
	 * returned.
	 *
	 * @return string The max range input or null if custom date is not used
	 * or max date is not specified.
	 */
	private function _getMaxInput()
	{
		$dateParts = explode(",", JFactory::getApplication()->input->getString('qdr'));
		$maxParts = explode(":", JArrayHelper::getValue($dateParts, 1));
		
		if (JArrayHelper::getValue($maxParts, 0) == 'max' &&
			($max = JArrayHelper::getValue($maxParts, 1))) {
			return $max;
		} else {
			return null;
		}
	}
}
<?php
/**
 * @copyright   Copyright (C) 2013-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

use \JText as JText;
use \JArrayHelper as JArrayHelper;
use \JFactory as JFactory;

/**
 * Renders a calendar search tool form field. Filters the results displayed by
 * a period of time.
 */
class Calendar extends FilterList
{
    /**
     * The form field type.
     *
     * @var  string
     */
    protected $type = 'JSolr.Calendar';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        // Initialize variables.
        $options = array();

        foreach ($this->element->children() as $element) {
            // Only add <option /> elements.
            if ($element->getName() != 'option') {
                continue;
            }

            $value = (string)$element->attributes()->value;

            $selected = $value == $this->value;

            $uri = clone \JSolr\Search\Factory::getSearchRoute();

            if (!empty($value)) {
                $uri->setVar($this->name, $value);
            } else {
                $uri->delVar($this->name);
            }

            $option = array();
            $option["uri"] = (string)$uri;
            $option["value"] = $value;
            $option["label"] = (string)$element;
            $option["selected"] = $selected;

            // Add the option object to the result set.
            $options[] = $option;
        }

        reset($options);

        return $options;
    }

    /**
     * Gets the date filter based on the currently selected value.
     *
     * @return array An array containing a single date filter based on the
     * currently selected value.
     *
     * @see Filterable::getFilter()
     */
    public function getFilter()
    {
        $filter = null;

        foreach ($this->element->xpath('option') as $option) {
            $value = (string)$option['value'];

            if ($this->value && $value == $this->value) {
                $selected = (string)$option['filter'];

                $filter = new \Solarium\QueryType\Select\Query\FilterQuery();
                $filter->setKey($this->name.".".$this->filter);
                $filter->setQuery($this->filter.":".$selected);

                continue;
            }
        }

        return $filter;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'show_custom':
                if ($this->getAttribute($name, null) === 'true') {
                    return true;
                } else {
                    return false;
                }

                break;

            default:
                return parent::__get($name);
        }
    }
}

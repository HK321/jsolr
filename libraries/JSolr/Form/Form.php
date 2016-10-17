<?php
/**
 * @copyright    Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form;

/**
 * Provides a specialized JSolr form which exposes a number of additional
 * methods for handling filtering, faceting and sorting of search results.
 */
class Form extends \JForm
{
    protected $facets = false;

    /**
     * Gets a list of filters for narrowing the search result set.
     *
     * @return array An array of solr-specific filters.
     */
    public function getFilters()
    {
        $filters = array();

        foreach ($this->getFieldsets() as $fieldset) {
            foreach ($this->getFieldset($fieldset->name) as $field) {
                if (in_array('JSolr\Form\Fields\Filterable', class_implements($field)) == true) {
                    foreach ($field->getFilters() as $filter) {
                        $filters[$field->fieldname] = $filter;
                    }
                }
            }
        }

        return $filters;
    }

    /**
     * Gets an array of facets from the currently configured list of JSolr
     * Form Fields.
     *
     * @return array An array of facets.
     */
    public function getFacets()
    {
        $facets = array();

        foreach ($this->getFieldset('facets') as $field) {
            $facets = array_merge_recursive($facets, $field->getFacetParams());
        }

        return $facets;
    }

    /**
     * Gets the fields to sort the result set by.
     *
     * @return array The fields to sor the result set by.
     */
    public function getSorts()
    {
        $sort = array();

        // get sort fields.
        foreach ($this->getFieldsets() as $fieldset) {
            foreach ($this->getFieldset($fieldset->name) as $field) {
                if (in_array('JSolr\Form\Fields\Sortable', class_implements($field)) == true) {
                    if ($field->getSort()) {
                        $sort[] = $field->getSort();
                    }
                }
            }
        }

        return $sort;
    }

    /**
     * Method to get an instance of a form.
     *
     * @param   string  $name     The name of the form.
     * @param   string  $data     The name of an XML file or string to load as the form definition.
     * @param   array   $options  An array of form options.
     * @param   string  $replace  Flag to toggle whether form fields should be replaced if a field
     * already exists with the same group/name.
     * @param   string  $xpath    An optional xpath to search for the fields.
     *
     * @return  object  JForm instance.
     *
     * @throws  Exception if an error occurs.
     */
    public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false)
    {
        // Reference to array with form instances
        $forms = &self::$forms;

        // Only instantiate the form if it does not already exist.
        if (!isset($forms[$name]))
        {
            $data = trim($data);

            if (empty($data)) {
                throw new \InvalidArgumentException(sprintf('Form::getInstance(name, *%s*)', gettype($data)));
            }

            // Instantiate the form.
            $forms[$name] = new Form($name, $options);

            // Load the data.
            if (substr($data, 0, 1) == '<') {
                if ($forms[$name]->load($data, $replace, $xpath) == false) {
                    throw new \RuntimeException('Form::getInstance could not load form');
                }
            } else {
                if ($forms[$name]->loadFile($data, $replace, $xpath) == false) {
                    throw new \RuntimeException('Form::getInstance could not load file');
                }
            }
        }

        return $forms[$name];
    }

    /**
     * Gets the facet form field markup for the facet field input.
     *
     * The facet field must implement the JSolrFacetable interface.
     *
     * @param   string  $name   The name of the form field.
     * @param   string  $group  The optional dot-separated form group path on which to find the field.
     * @param   mixed   $value  The optional value to use as the default for the field.
     *
     * @return  string  The facet form field markup for the facet field input.
     */
    public function getFacetInput($name, $group = null, $value = null)
    {
        // Attempt to get the form field.
        if ($field = $this->getField($name, $group, $value)) {
            return $field->facetInput;
        }

        return '';
    }
}

<?php
/**
 * @package     JSolr
 * @subpackage  Search
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
jimport('joomla.filesystem.path');
jimport('joomla.utilities.arrayhelper');

class JSolrViewSuggest extends JViewLegacy
{
    public function display($tpl = null)
    {
        echo json_encode($this->get('Items'));

        jexit();
    }
}

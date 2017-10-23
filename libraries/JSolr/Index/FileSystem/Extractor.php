<?php
/**
 * @copyright   Copyright (C) 2014-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Index\FileSystem;

/**
 * A class for extracting metadata, content and other information from a file.
 */
abstract class Extractor
{
    /**
     * @var  string  $appPath  The path to the extract application.
     */
    private $appPath;

    private $pathOrUrl;

    private $params;

    private $language;

    public function __construct($pathOrUrl)
    {
        $this->pathOrUrl = $pathOrUrl;

        $params = \JComponentHelper::getParams('com_jsolr', true);

        $this->params = $params;

        $this->setAppPath($params->get('component.app_path'));
    }

    public function getPathOrUrl()
    {
        return $this->pathOrUrl;
    }

    /**
     * Sets the path to the extraction application.
     *
     * The path can be whatever the inheriting class understands; refer to the
     * individual class' documentation for allowed app path values.
     *
     * @param  string  $appPath  The path to the application.
     */
    public function setAppPath($appPath)
    {
        $this->appPath = $appPath;
    }

    /**
     * Gets the path of the extraction application.
     *
     * The path can be whatever the inheriting class understands; refer to the
     * individual class' documentation for allowed app path values.
     *
     * @return  string  The path to the application.
     */
    public function getAppPath()
    {
        return $this->appPath;
    }

    abstract public function getContentType();

    abstract public function getLanguage();

    abstract public function getContent();

    abstract public function getMetadata();

    public function getAllowedContentTypes()
    {
        $types = $this->params->get('component.content_types_allowed');

        return array_map('trim', explode(',', trim($types)));
    }

    public function getIndexContentContentTypes()
    {
        $types = $this->params->get('component.content_types_index_content');

        return array_map('trim', explode(',', trim($types)));
    }

    public function isAllowedContentType()
    {
        $allowed = false;

        $contentType = $this->getContentType();

        $types = $this->getAllowedContentTypes();

        while ((($type = current($types)) !== false) && !$allowed) {
            if (preg_match("#".$type."#i", $contentType)) {
                $allowed = true;
            }

            next($types);
        }

        return $allowed;
    }

    public function isContentIndexable()
    {
        $allowed = false;

        $contentType = $this->getContentType();

        $types = $this->getIndexContentContentTypes();

        while ((($type = current($types)) !== false) && !$allowed) {
            if (preg_match("#".$type."#i", $contentType)) {
                $allowed = true;
            }

            next($types);
        }

        return $allowed;
    }
}

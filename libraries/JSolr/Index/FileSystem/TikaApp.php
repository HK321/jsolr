<?php
/**
 * @copyright   Copyright (C) 2014-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Index\FileSystem;

use \JString as JString;
use \JFactory as JFactory;

/**
 * A class for extracting metadata, content and other information from a file.
 */
class TikaApp extends Extractor
{
    private $metadata;

    private $content;

    public function getContentType()
    {
        $result = $this->extract('-d');

        $map = array_map('trim', explode(';', trim($result)));

        // sometimes the charset is appended to the file type.
        return \JArrayHelper::getValue($map, 0);
    }

    /**
     * Gets an array of languages associated with this document.
     *
     * In many cases only a two-letter iso language (iso639) is attached to the
     * document. However, JSolr supports both language (iso639) and region
     * (iso3166), E.g. en-AU.
     * This method will attempt to match all the language codes with their
     * language+region counterparts so it is possible that more than one code
     * will be returned for the document.
     *
     * @return array An array of languages associated with the document.
     */
    public function getLanguage()
    {
        $result = $this->extract('-l');

        $results = explode("\n", str_replace("\r", "\n", $result));

        $array = array();

        foreach ($results as $value) {
            if ($value) {
                if (JString::strlen($value) == 5) { // assume iso with region
                    $array[] = str_replace('_', '-', $value);
                } elseif (JString::strlen($value) == 2) { // assume iso without region
                    $found = false;
                    $languages = \JLanguageHelper::getLanguages();

                    while (($language = current($languages)) && !$found) {
                        $parts = explode('-', $language->lang_code);

                        if ($value == \JArrayHelper::getValue($parts, 0)) {
                            if (array_search($language->lang_code, $array) === false) {
                                $array[] = $language->lang_code;
                            }

                            $found = true;
                        }

                        next($languages);
                    }

                    reset($languages);
                }
            }
        }

        // if no languages could be detected, use the system lang.
        if (!count($array)) {
            $array[] = JFactory::getLanguage()->getTag();
        }

        return $array;
    }

    public function getContent()
    {
        if (!$this->content) {
            $result = $this->extract('-t');

            $this->content = $result;
        }

        return $this->content;
    }

    public function getMetadata()
    {
        if (!$this->metadata) {
            $result = $this->extract('-j');

            $metadata = new \Joomla\Registry\Registry($result);

            $this->metadata = $metadata;
        }

        return $this->metadata;
    }

    private function extract($flags)
    {
        \JLog::addLogger(array());

        ob_start();

        $cmd = "java -Xmx128M -jar ".
            $this->getAppPath()." ".$flags." ".$this->getPathOrUrl()." 2> /dev/null";

        \JLog::add('tika app: '.$cmd, \JLog::DEBUG, 'tikaserver');

        passthru($cmd);

        $result = ob_get_contents();

        ob_end_clean();

        return $result;
    }
}

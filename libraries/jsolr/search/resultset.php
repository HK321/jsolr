<?php
class JSolrSearchResultSet extends JObject implements IteratorAggregate, Countable
{
	protected $numFound;
	
	protected $documents;
	
	protected $qTime;
	
	protected $qTimeFormatted;
	
	protected $handlers;
	
	public function __construct($response)
	{
		$headers = json_decode($response->getRawResponse())->responseHeader;
		
		$this->numFound = $response->response->numFound;
		$this->qTime = $headers->QTime;
		$this->qTimeFormatted = round($this->qTime/1000, 5, PHP_ROUND_HALF_UP);
		
		$this->documents = $response->response->docs;
		
		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher = JDispatcher::getInstance();
		
		for ($i = 0; $i < count($this->documents); $i++) {
			// Get Highlight fields for results.
			foreach ($dispatcher->trigger('onJSolrSearchURIGet', array($this->documents[$i])) as $document) {
				if ($document) {
					$this->documents[$i]->link = $document;
				}
			}
		}
		
		if (isset($response->facet_counts->facet_fields)) {
			$this->handlers['facets'] = $response->facet_counts->facet_fields;
		}
		
		if (isset($response->highlighting)) {
			$this->handlers['highlighting'] = $response->highlighting;
		}
		
		if (isset($response->spellcheck->suggestions->collation)) {
			$this->handlers['suggestions'] = $response->spellcheck->suggestions->collation;
		}
	}	
	
	/**
	 * IteratorAggregate implementation
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->documents);
	}
	
	public function count()
	{
		return count($this->documents);
	}
	
	public function getHighlighting()
	{
		return JArrayHelper::getValue($this->handlers, 'highlighting');
	}
	
	public function getFacets()
	{
		return JArrayHelper::getValue($this->handlers, 'facets');
	}
	
	public function getSuggestions()
	{
		return JArrayHelper::getValue($this->handlers, 'suggestions');
	}
	
	public function getMoreLikeThis()
	{
		return JArrayHelper::getValue($this->handlers, 'moreLikeThis');
	}
}
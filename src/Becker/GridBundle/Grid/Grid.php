<?php

namespace Becker\GridBundle\Grid;

use Doctrine\Common\Collections\ArrayCollection;

class Grid {
	
	/**
	 * Service container
	 */
	private $container = null;
	
	/**
	 * Entity name
	 */
	private $entityName = null;
	
	/**
	 * Columns
	 */
	private $columns = null;
	
	/**
	 * Rows
	 */
	private $objects = null;
	
	/**
	 * Sort by column
	 */
	private $orderBy = null;
	
	/**
	 * Sort direction
	 */
	private $order = null;
	
	/**
	 * Filter by column
	 */
	private $filterBy = null;
	
	/**
	 * Filter
	 */
	private $filter = null;
	
	/**
	 * Page
	 */
	private $page = 1;
	
	/**
	 * Total objects
	 */
	private $count = 0;
	
	/**
	 * Per page
	 */
	private $perPage = 25;
	
	/**
	 * Constructor
	 */
	public function __construct($entityName = null)
	{
		if ($entityName) {
			$this->setEntity($entityName);
		}
		
		$this->columns = new ArrayCollection();
	}
	
	/**
	 * Add column
	 */
	public function add($key, $name = null, $options = array())
	{
		$this->columns->add(new Column($key, $name, $options));
		
		return $this;
	}
	
	/**
	 * Remove column
	 */
	public function remove($key)
	{
		$this->columns->remove($key);
		
		return $this;
	}
	
	/**
	 * Get columns
	 */
	public function getColumns()
	{
		return $this->columns;
	}
	
	/**
	 * Set container
	 */
	public function setContainer($container)
	{
		$this->container = $container;
	}
	
	/**
	 * Get container
	 */
	public function getContainer()
	{
		return $this->container;
	}
	
	/**
	 * Get entity manager
	 */
	public function getManager()
	{
		return $this->getContainer()->get('doctrine.orm.entity_manager');
	}
	
	/**
	 * Get objects
	 */
	public function getObjects()
	{
		$request = $this->getContainer()->get('request');
		
		// Set page
		$this->page = $request->get('_page') ?: 1;
		
		// Set order by
		$order = array();
		if ($request->get('_order_by')) {
			$this->orderBy = $request->get('_order_by');
			$this->order = $request->get('_order') ?: 'asc';
			$order = array($this->orderBy => $this->order);
		}
		
		$filter = array();
		if ($request->get('_filter_by')) {
			$this->filterBy = $request->get('_filter_by');
			$this->filter = $request->get('_filter');
			$filter = array($this->filterBy => $this->filter);
		}

		if ($this->objects === null) {
			// Get total objects
			$query = $this->getManager()
						->getRepository($this->getEntity())
						->createQueryBuilder('o')
						->select('count(o)');
			
			if ($this->filterBy) {
				// Join tables?
				if (preg_match('/^(.*)\.(.*)$/', $this->filterBy, $out)) {
					$joinTable = $out[1];
					$joinField = $out[2];
					$filterBy = $joinTable;
					$query->innerJoin("o.{$joinTable}", $joinTable);
				} else {
					$joinTable = false;
					$joinField = false;
					$filterBy = "o.{$this->filterBy}";
				}
				
				// Default comparison is LIKE
				$comparison = is_numeric($this->filter) ? '=' : 'LIKE';

				// Filter table
				$query->where("{$filterBy} {$comparison} :where");
				$query->setParameter('where', $this->filter);
			}
			
			// Get total
			$this->count = $query->getQuery()->getSingleScalarResult();
			
			// Get subset
			$query->select('o');
			$query->setFirstResult(($this->page - 1) * $this->perPage);
			$query->setMaxResults($this->perPage);
			
			foreach ($order as $k => $v) {
				$query->orderBy("o.{$k}", $v);
			}
			
			$this->objects = $query->getQuery()->getResult();
		}
				
		return $this->objects;
	}
	
	/**
	 * Get entity name
	 */
	public function getEntity()
	{
		return $this->entityName;
	}
	
	/**
	 * Set source
	 */
	public function setEntity($entityName)
	{
		$this->entityName = $entityName;
		
		// Get fields
		//$this->fields = $this->getManager()->getClassMetadata($this->entityName)->getFieldNames();
	}
	
	/**
	 * Get order
	 */
	public function getOrder()
	{
		return $this->order;
	}
	
	/**
	 * Get order by
	 */
	public function getOrderBy()
	{
		return $this->orderBy;
	}
	
	/**
	 * Get count
	 */
	public function getCount()
	{
		return $this->count;
	}
	
	/**
	 * Get per page
	 */
	public function getPerPage()
	{
		return $this->perPage;
	}
	
	/**
	 * Get pages
	 */
	public function getPages()
	{
		return ceil($this->count / $this->perPage);
	}
	
	/** 
	 * Get current page number
	 */
	public function getPage()
	{
		return $this->page;
	}
}
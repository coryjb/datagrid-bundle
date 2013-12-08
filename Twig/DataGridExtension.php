<?php

namespace Becker\GridBundle\Twig;

class DataGridExtension extends \Twig_Extension
{
	const DEFAULT_TEMPLATE = 'BeckerGridBundle::blocks.html.twig';

    /**
     * @var \Twig_Environment
     */
    protected $environment;

	/**
	 * Constructor
	 */
	public function __construct(\Twig_Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'grid' => new \Twig_Function_Method($this, 'getGrid', array('is_safe' => array('html')))
        );
    }

	/**
     * Render grid block
     *
     * @param \Nightlife\GridBundle\Grid\Grid
     *
     * @return string
     */
    public function getGrid($grid)
    {	
		return $this->renderBlock('grid', array(
			'grid' => $grid
		));
    }

	/**
	 * Render block
	 */
	public function renderBlock($block, $parameters)
	{
		$template = $this->environment->loadTemplate(self::DEFAULT_TEMPLATE);
		
		return $template->renderBlock($block, array_merge($this->environment->getGlobals(), $parameters));
	}
	
	public function getName()
	{
		return 'becker_grid_extension';
	}
}
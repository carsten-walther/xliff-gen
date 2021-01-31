<?php

namespace CarstenWalther\XliffGen\Generator;

use CarstenWalther\XliffGen\Domain\Model\Xlf;
use CarstenWalther\XliffGen\View\View;

/**
 * Class Generator
 *
 * @package CarstenWalther\XliffGen\Generator
 */
class Generator
{
    const TEMPLATE_DEFAULT = 'locallang.xlf';

    /**
     * @var \CarstenWalther\XliffGen\Domain\Model\Xlf
     */
    protected $xlf;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var \CarstenWalther\XliffGen\View\View
     */
    protected $view;

    /**
     * Generator constructor.
     *
     * @param \CarstenWalther\XliffGen\Domain\Model\Xlf|null $xlf
     * @param string                                         $basePath
     */
    public function __construct(Xlf $xlf = null, string $basePath)
    {
        $this->xlf = $xlf;
        $this->basePath = $basePath;
    }

    /**
     * @return false|string
     * @throws \SmartyException|\ReflectionException
     */
    public function generate()
    {
        $this->view = new View();
        $this->view->setBasePath($this->basePath);
        $this->view->setTemplatePath($this->basePath . '/Resources/Private/Templates/');
        $this->view->assign('xlf', $this->xlf->toArray());

        return $this->view->renderTemplate(self::TEMPLATE_DEFAULT, true);
    }
}

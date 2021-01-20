<?php

namespace CarstenWalther\XliffGen\Utility;

/**
 * Class Generator
 * @package CarstenWalther\XliffGen
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
     *
     * @throws \SmartyException
     */
    public function __construct(\CarstenWalther\XliffGen\Domain\Model\Xlf $xlf = null, string $basePath)
    {
        $this->xlf = $xlf;
        $this->basePath = $basePath;
    }

    /**
     * @return false|string
     * @throws \SmartyException
     */
    public function generate()
    {
        $this->view = new \CarstenWalther\XliffGen\View\View();
        $this->view->setBasePath($this->basePath);
        $this->view->setTemplatePath($this->basePath . '/Resources/Private/Templates/');
        $this->view->assign('xlf', $this->xlf->toArray());

        return $this->view->renderTemplate(self::TEMPLATE_DEFAULT, true);
    }
}

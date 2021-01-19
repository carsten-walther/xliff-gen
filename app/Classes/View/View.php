<?php

namespace CarstenWalther\XliffGen\View;

/**
 * Class View
 *
 * @package CarstenWalther\XliffGen\View
 */
class View
{
    /**
     * @var string
     */
    protected $layoutPath;

    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @var array
     */
    protected $variables;

    /**
     * @var string
     */
    protected $pageTitle;

    /**
     * @var string
     */
    protected $pageTemplate = 'Default.html';

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var \Smarty|object
     */
    protected $templateEngine;

    /**
     * View constructor.
     *
     * @param        $layoutPath
     * @param        $templatePath
     * @param string $templateEngineObject
     */
    public function __construct($layoutPath, $templatePath, $templateEngineObject = 'Smarty')
    {
        if ($layoutPath && is_dir($layoutPath)) {
            $this->layoutPath = $layoutPath;
        } else {
            throw new \InvalidArgumentException('Layout path must be a valid path.');
        }

        if ($templatePath && is_dir($templatePath)) {
            $this->templatePath = $templatePath;
        } else {
            throw new \InvalidArgumentException('Template path must be a valid path.');
        }

        $this->templateEngine = new $templateEngineObject;
        $this->templateEngine->debugging = true;
    }

    /**
     * @param $title
     */
    public function setTitle($title) : void
    {
        $this->pageTitle = $title;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath) : void
    {
        $this->basePath = $basePath;
    }

    /**
     * @param $variable
     * @param $mixedValue
     */
    public function assign($variable, $mixedValue) : void
    {
        $this->variables[$variable] = $mixedValue;
    }

    /**
     * @param $template
     *
     * @throws \SmartyException
     */
    public function render($template) : void
    {
        $this->templateEngine->assign('title', $this->pageTitle);
        $this->templateEngine->assign('content', $this->renderContent($template));

        echo $this->templateEngine->fetch($this->layoutPath . $this->pageTemplate);
    }

    /**
     * @param string $template
     *
     * @return string
     * @throws \SmartyException
     */
    public function renderContent(string $template) : string
    {
        foreach ($this->variables as $key => $variable) {
            $this->templateEngine->assign($key, $variable);
        }

        return $this->templateEngine->fetch($this->templatePath . $template);
    }
}

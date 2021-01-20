<?php

namespace CarstenWalther\XliffGen\Controller;

use CarstenWalther\XliffGen\Model\TranslationUnit;
use CarstenWalther\XliffGen\Model\Xlf;

/**
 * Class XliffGenController
 *
 * @package CarstenWalther\XliffGen
 */
class XliffGenController
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var \CarstenWalther\XliffGen\View\View
     */
    protected $view;

    /**
     * @var \CarstenWalther\XliffGen\Domain\Repository\LanguageRepository
     */
    protected $languageRepository;

    /**
     * @var array
     */
    protected $errorMessages;

    /**
     * XliffGenController constructor.
     *
     * @param string $basePath
     * @param string $baseUrl
     * @param array  $repositoryConfiguration
     */
    public function __construct(string $basePath, string $baseUrl, array $repositoryConfiguration)
    {
        $this->basePath = $basePath;
        $this->baseUrl = $baseUrl;

        $this->initRepository($repositoryConfiguration);
        $this->initView();
        $this->dispatch();
    }

    /**
     * @param $repositoryConfiguration
     */
    protected function initRepository($repositoryConfiguration)
    {
        switch ($repositoryConfiguration['type']) {
            case 'csv':
                $this->languageRepository = new \CarstenWalther\XliffGen\Domain\Repository\LanguageRepository($repositoryConfiguration['file']);
                $this->languageRepository->setBasePath($this->basePath);
                break;
        }
    }

    /**
     * @return void
     */
    protected function initView() : void
    {
        $this->view = new \CarstenWalther\XliffGen\View\View();
        $this->view->setBasePath($this->basePath);
        $this->view->setLayoutPath($this->basePath . '/Resources/Private/Layout/');
        $this->view->setTemplatePath($this->basePath . '/Resources/Private/Templates/');

        $this->view->setTitle('XLIFF Generator');
    }

    /**
     * @return void
     */
    protected function dispatch() : void
    {
        $arguments = $_GET;
        $action = $arguments['action'] ?? 'index';

        $this->{$action . 'Action'}();
    }

    /**
     * @throws \SmartyException
     */
    protected function indexAction() : void
    {
        $languageObjects = $this->languageRepository->findAll();
        $languages = [];
        foreach ($languageObjects as $count => $languageObject) {
            $languages[$count] = $languageObject->toArray();
        }

        $this->view->assign('baseUrl', $this->baseUrl);
        $this->view->assign('languages', $languages);
        $this->view->render('Index.html');
    }

    /**
     * @throws \SmartyException
     */
    protected function executeAction() : void
    {
        $arguments = $_POST;
        $files = $_FILES["files"];

        $xlfStorage = [];

        if ($files) {
            foreach ($files["error"] as $key => $error) {

                $tmp_name = $files["tmp_name"][$key];

                if (!$tmp_name) {
                    continue;
                }

                if ($error === UPLOAD_ERR_OK) {

                    $fileContent = file_get_contents($tmp_name);
                    $fileContent = str_replace(['\"', "\'"], '"', $fileContent);

                    $extractor = new \CarstenWalther\XliffGen\Utility\Extractor($fileContent, [
                        'sourceLanguage' => $arguments['sourceLanguage'],
                        'targetLanguage' => $arguments['targetLanguage'],
                        'productName' => $arguments['productName'],
                        'original' => 'SOURCE.FILE'
                    ]);
                    $xlf = $extractor->extract();

                    #echo('<pre>' . print_r($xlf, true) . '</pre>');

                    $generator = new \CarstenWalther\XliffGen\Utility\Generator($xlf, $this->basePath);
                    $xlfStorage[] = $generator->generate();
                } else {
                    $this->errorMessages[] = "Upload error. [" . $error . "] on file '" . $files["name"][$key];
                }
            }
        }


        #die();

        $this->indexAction();
    }
}

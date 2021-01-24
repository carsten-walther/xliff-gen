<?php

namespace CarstenWalther\XliffGen\Controller;

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
     * @var \CarstenWalther\XliffGen\Domain\Repository\TypeRepository
     */
    protected $typeRepository;

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
     *
     * @return void
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
     *
     * @return void
     */
    protected function initRepository($repositoryConfiguration) : void
    {
        if ($repositoryConfiguration['type'] === 'csv') {
            $this->typeRepository = new \CarstenWalther\XliffGen\Domain\Repository\TypeRepository($repositoryConfiguration['data'] . '/Types.csv');
            $this->typeRepository->setBasePath($this->basePath);
            $this->languageRepository = new \CarstenWalther\XliffGen\Domain\Repository\LanguageRepository($repositoryConfiguration['data'] . '/Languages.csv');
            $this->languageRepository->setBasePath($this->basePath);
        }
    }

    /**
     * @return void
     */
    protected function initView() : void
    {
        $this->view = new \CarstenWalther\XliffGen\View\View();
        $this->view->setTitle('XLIFF Generator');
        $this->view->setBasePath($this->basePath);
        $this->view->setLayoutPath($this->basePath . '/Resources/Private/Layout/');
        $this->view->setTemplatePath($this->basePath . '/Resources/Private/Templates/');
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
        $typeObjects = $this->typeRepository->findAll();
        $types = [];
        foreach ($typeObjects as $count => $typeObject) {
            $types[$count] = $typeObject->toArray();
        }

        $languageObjects = $this->languageRepository->findAll();
        $languages = [];
        foreach ($languageObjects as $count => $languageObject) {
            $languages[$count] = $languageObject->toArray();
        }

        $this->view->assign('baseUrl', $this->baseUrl);
        $this->view->assign('types', $types);
        $this->view->assign('languages', $languages);
        $this->view->render('Index.html');
    }

    /**
     * @throws \SmartyException
     * @throws \Exception
     */
    protected function executeAction() : void
    {
        $arguments = $_POST;
        $files = $_FILES['files'];

        $xlfStorage = [];

        if ($files) {

            if (!$arguments['productName']) {
                $arguments['productName'] = 'my_product_name';
            }

            if ($arguments['addSourceLanguageToTargetLanguage']) {
                $arguments['targetLanguages'][] = $arguments['sourceLanguage'];
            }

            foreach ($arguments['targetLanguages'] as $targetLanguage) {
                foreach ($files['error'] as $key => $error) {

                    $name = pathinfo($files['name'][$key], PATHINFO_FILENAME);
                    $tmp_name = $files['tmp_name'][$key];

                    if (!$tmp_name) {
                        continue;
                    }

                    if ($error === UPLOAD_ERR_OK) {

                        $fileContent = file_get_contents($tmp_name);
                        $fileContent = str_replace(['\"', "\'"], '"', $fileContent);

                        $extractor = new \CarstenWalther\XliffGen\Utility\Extractor($fileContent, [
                            'sourceLanguage' => $arguments['sourceLanguage'],
                            'targetLanguage' => $arguments['sourceLanguage'] !== $targetLanguage ? $targetLanguage : null,
                            'productName' => $arguments['productName'],
                            'original' => $arguments['sourceLanguage'] !== $targetLanguage ? 'locallang_' . strtolower($name) . '.xlf' : ''
                        ]);

                        $xlf = $extractor->extract();

                        if ($xlf) {
                            $xlf->setType((array_key_exists('type', $arguments) ? $arguments['type'] : ''));
                            $xlf->setDescription((array_key_exists('description', $arguments) ? $arguments['description'] : ''));
                            $xlf->setAuthorEmail((array_key_exists('authorEmail', $arguments) ? $arguments['authorEmail'] : ''));
                            $xlf->setAuthorName((array_key_exists('authorName', $arguments) ? $arguments['authorName'] : ''));
                        }

                        $generator = new \CarstenWalther\XliffGen\Utility\Generator($xlf, $this->basePath);

                        $xlfStorage[$targetLanguage][] = [
                            'content' => $generator->generate(),
                            'filename' => ($arguments['sourceLanguage'] !== $targetLanguage ? $targetLanguage . '.' : '') . 'locallang_' . strtolower($name) . '.xlf',
                            'relpath' => $arguments['productName'] . '/Resources/Private/Language',
                            'comment' => 'Generated by XLIFF Generator'
                        ];
                    } else {
                        $this->errorMessages[] = "Upload error. [" . $error . "] on file '" . $files["name"][$key];
                    }
                }
            }
        }

        if (isset($xlf)) {
            $zip = new \CarstenWalther\XliffGen\Utility\Zip($arguments['productName'] . '.xliff.zip', 'Generated by XLIFF Generator');
            foreach ($xlfStorage as $lang) {
                foreach ($lang as $xlf) {
                    $zip->addData($xlf['content'], $xlf['filename'], $xlf['relpath'], $xlf['comment']);
                }
            }
            $zip->send();
        }

        $this->indexAction();
    }
}

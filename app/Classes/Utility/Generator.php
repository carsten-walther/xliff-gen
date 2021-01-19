<?php

namespace CarstenWalther\XliffGen;

/**
 * Class Generator
 * @package CarstenWalther\XliffGen
 */
class Generator
{
    const TEMPLATE_DEFAULT = 'template.xlf';

    /**
     * @var \CarstenWalther\XliffGen\Model\Xlf
     */
    protected $xlf;

    /**
     * @var \Twig\Loader\FilesystemLoader
     */
    protected $filesystemLoader;

    /**
     * @var \Twig\Environment
     */
    protected $environment;

    /**
     * Generator constructor.
     *
     * @param \CarstenWalther\XliffGen\Model\Xlf $xlf
     */
    public function __construct(\CarstenWalther\XliffGen\Model\Xlf $xlf)
    {
        $this->xlf = $xlf;

        /** @var \Twig\Loader\FilesystemLoader $loader */
        $this->filesystemLoader = new \Twig\Loader\FilesystemLoader('packages/carstenwalther/xliff-gen/src/templates');

        /** @var \Twig\Environment $twig */
        $this->environment = new \Twig\Environment($this->filesystemLoader);
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function generate() : string
    {
        return $this->environment->render(self::TEMPLATE_DEFAULT, [
            'xlf' => $this->xlf
        ]);
    }
}

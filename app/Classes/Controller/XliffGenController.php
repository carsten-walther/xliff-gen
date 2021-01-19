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
     * XliffGenController constructor.
     *
     * @param $basePath
     * @param $baseUrl
     */
    public function __construct($basePath, $baseUrl)
    {
        $this->basePath = $basePath;
        $this->baseUrl = $baseUrl;

        $this->initView();
        $this->dispatch();
    }

    /**
     * @return void
     */
    protected function initView() : void
    {
        $this->view = new \CarstenWalther\XliffGen\View\View(
            $this->basePath . '/Resources/Private/Layout/',
            $this->basePath . '/Resources/Private/Templates/'
        );
        $this->view->setBasePath($this->basePath);
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
        $this->view->setTitle('XLIFF Generator :: Index');
        $this->view->assign('baseUrl', $this->baseUrl);
        $this->view->assign('languages', $this->getLanguages());
        $this->view->render('Index.html');
    }

    /**
     * @return \string[][]
     */
    protected function getLanguages() : array
    {
        return [
            ['id' => 'ab', 'title' => 'Аҧсуа бызшәа'],
            ['id' => 'aa', 'title' => 'Afaraf'],
            ['id' => 'af', 'title' => 'Afrikaans'],
            ['id' => 'sq', 'title' => 'Gjuha shqipe'],
            ['id' => 'am', 'title' => 'አማርኛ'],
            ['id' => 'ar', 'title' => 'العربية'],
            ['id' => 'hy', 'title' => 'Հայերեն'],
            ['id' => 'as', 'title' => 'অসমীয়া'],
            ['id' => 'ay', 'title' => 'Aymar aru'],
            ['id' => 'az', 'title' => 'Azərbaycan dili'],
            ['id' => 'ba', 'title' => 'Башҡорт'],
            ['id' => 'eu', 'title' => 'Euskara'],
            ['id' => 'bn', 'title' => 'বাংলা'],
            ['id' => 'dz', 'title' => 'ཇོང་ཁ'],
            ['id' => 'bh', 'title' => 'भोजपुरी'],
            ['id' => 'bi', 'title' => 'Bislama'],
            ['id' => 'br', 'title' => 'Brezhoneg'],
            ['id' => 'bg', 'title' => 'Български'],
            ['id' => 'my', 'title' => 'မ္ရန္‌မာစာ'],
            ['id' => 'be', 'title' => 'Беларуская'],
            ['id' => 'km', 'title' => 'ភាសាខ្មែរ'],
            ['id' => 'ca', 'title' => 'Català'],
            ['id' => 'za', 'title' => 'Sawcuengh'],
            ['id' => 'zh', 'title' => '漢語'],
            ['id' => 'co', 'title' => 'Corsu'],
            ['id' => 'hr', 'title' => 'Hrvatski'],
            ['id' => 'cs', 'title' => 'Čeština'],
            ['id' => 'da', 'title' => 'Dansk'],
            ['id' => 'nl', 'title' => 'Nederlands'],
            ['id' => 'en', 'title' => 'English'],
            ['id' => 'eo', 'title' => 'Esperanto'],
            ['id' => 'et', 'title' => 'Eesti'],
            ['id' => 'fo', 'title' => 'Føroyskt'],
            ['id' => 'fa', 'title' => 'فارسی'],
            ['id' => 'fj', 'title' => 'Na Vosa Vakaviti'],
            ['id' => 'fi', 'title' => 'Suomi'],
            ['id' => 'fr', 'title' => 'Français'],
            ['id' => 'fy', 'title' => 'Frysk'],
            ['id' => 'gl', 'title' => 'Galego'],
            ['id' => 'gd', 'title' => 'Gàidhlig'],
            ['id' => 'gv', 'title' => 'Gaelg'],
            ['id' => 'ka', 'title' => 'ქართული'],
            ['id' => 'de', 'title' => 'Deutsch'],
            ['id' => 'el', 'title' => 'Ελληνικά'],
            ['id' => 'kl', 'title' => 'Kalaallisut'],
            ['id' => 'gn', 'title' => 'Avañe\'ẽ'],
            ['id' => 'gu', 'title' => 'ગુજરાતી'],
            ['id' => 'ha', 'title' => 'Hausa'],
            ['id' => 'he', 'title' => 'עברית'],
            ['id' => 'hi', 'title' => 'हिन्दी'],
            ['id' => 'hu', 'title' => 'Magyar'],
            ['id' => 'is', 'title' => 'Íslenska'],
            ['id' => 'id', 'title' => 'Bahasa Indonesia'],
            ['id' => 'ia', 'title' => 'Interlingua'],
            ['id' => 'ie', 'title' => 'Interlingue'],
            ['id' => 'iu', 'title' => 'ᐃᓄᒃᑎᑐᑦ'],
            ['id' => 'ik', 'title' => 'Iñupiak'],
            ['id' => 'ga', 'title' => 'Gaeilge'],
            ['id' => 'it', 'title' => 'Italiano'],
            ['id' => 'ja', 'title' => '日本語'],
            ['id' => 'kn', 'title' => 'ಕನ್ನಡ'],
            ['id' => 'ks', 'title' => 'कॉशुर'],
            ['id' => 'kk', 'title' => 'Қазақ тілі'],
            ['id' => 'rw', 'title' => 'Kinyarwanda'],
            ['id' => 'ky', 'title' => 'Кыргыз тили'],
            ['id' => 'rn', 'title' => 'kiRundi'],
            ['id' => 'ko', 'title' => '한국말'],
            ['id' => 'ku', 'title' => 'Kurdî'],
            ['id' => 'lo', 'title' => 'ພາສາລາວ'],
            ['id' => 'la', 'title' => 'Lingua latina'],
            ['id' => 'lv', 'title' => 'Latviešu'],
            ['id' => 'ln', 'title' => 'Lingála'],
            ['id' => 'lt', 'title' => 'Lietuvių'],
            ['id' => 'mk', 'title' => 'Македонски'],
            ['id' => 'mg', 'title' => 'Merina'],
            ['id' => 'ms', 'title' => 'Bahasa Melayu'],
            ['id' => 'ml', 'title' => 'മലയാളം'],
            ['id' => 'mt', 'title' => 'Malti'],
            ['id' => 'mi', 'title' => 'Māori'],
            ['id' => 'mr', 'title' => 'मराठी'],
            ['id' => 'mo', 'title' => 'молдовеняскэ'],
            ['id' => 'mn', 'title' => 'Монгол'],
            ['id' => 'na', 'title' => 'Ekakairũ Naoero'],
            ['id' => 'ne', 'title' => 'नेपाली'],
            ['id' => 'no', 'title' => 'Norsk'],
            ['id' => 'oc', 'title' => 'Occitan'],
            ['id' => 'or', 'title' => 'ଓଡ଼ିଆ'],
            ['id' => 'om', 'title' => 'Afaan Oromoo'],
            ['id' => 'ps', 'title' => 'پښت'],
            ['id' => 'pl', 'title' => 'Polski'],
            ['id' => 'pt', 'title' => 'Português'],
            ['id' => 'pa', 'title' => 'ਪੰਜਾਬੀ / پنجابی'],
            ['id' => 'qu', 'title' => 'Runa Simi'],
            ['id' => 'rm', 'title' => 'Rumantsch'],
            ['id' => 'ro', 'title' => 'Română'],
            ['id' => 'ru', 'title' => 'Русский'],
            ['id' => 'sm', 'title' => 'Gagana faʼa Samoa'],
            ['id' => 'sg', 'title' => 'Sängö'],
            ['id' => 'sa', 'title' => 'संस्कृतम्'],
            ['id' => 'sr', 'title' => 'Српски / Srpski'],
            ['id' => 'st', 'title' => 'seSotho'],
            ['id' => 'tn', 'title' => 'Setswana'],
            ['id' => 'sn', 'title' => 'chiShona'],
            ['id' => 'sd', 'title' => 'سنڌي، سندھی'],
            ['id' => 'si', 'title' => 'සිංහල'],
            ['id' => 'ss', 'title' => 'siSwati'],
            ['id' => 'sk', 'title' => 'Slovenčina'],
            ['id' => 'sl', 'title' => 'Slovenščina'],
            ['id' => 'so', 'title' => 'af Soomaali'],
            ['id' => 'es', 'title' => 'Español'],
            ['id' => 'su', 'title' => 'Basa Sunda'],
            ['id' => 'sw', 'title' => 'Kiswahili'],
            ['id' => 'sv', 'title' => 'Svenska'],
            ['id' => 'tl', 'title' => 'Tagalog'],
            ['id' => 'tg', 'title' => 'тоҷикӣ / تاجیکی'],
            ['id' => 'ta', 'title' => 'தமிழ்'],
            ['id' => 'tt', 'title' => 'татарча / tatarça / تاتارچ'],
            ['id' => 'te', 'title' => 'తెలుగు'],
            ['id' => 'th', 'title' => 'ภาษาไทย'],
            ['id' => 'bo', 'title' => 'བོད་ཡིག'],
            ['id' => 'ti', 'title' => 'ትግርኛ'],
            ['id' => 'to', 'title' => 'faka-Tonga'],
            ['id' => 'ts', 'title' => 'Tsonga'],
            ['id' => 'tr', 'title' => 'Türkçe'],
            ['id' => 'tk', 'title' => 'Türkmen dili'],
            ['id' => 'tw', 'title' => 'Twi'],
            ['id' => 'ug', 'title' => 'ئۇيغۇرچه'],
            ['id' => 'uk', 'title' => 'Українська'],
            ['id' => 'ur', 'title' => 'اردو'],
            ['id' => 'uz', 'title' => 'Ўзбек / O\'zbek'],
            ['id' => 'vi', 'title' => 'Tiếng Việt'],
            ['id' => 'vo', 'title' => 'Volapük'],
            ['id' => 'cy', 'title' => 'Cymraeg'],
            ['id' => 'wo', 'title' => 'Wolof'],
            ['id' => 'xh', 'title' => 'isiXhosa'],
            ['id' => 'yi', 'title' => 'ייִדיש'],
            ['id' => 'yo', 'title' => 'Yorùbá'],
            ['id' => 'zu', 'title' => 'isiZulu'],
            ['id' => 'bs', 'title' => 'Bosanski'],
            ['id' => 'ae', 'title' => 'Avestan'],
            ['id' => 'ak', 'title' => 'Akan'],
            ['id' => 'an', 'title' => 'Aragonés'],
            ['id' => 'av', 'title' => 'магӀарул мацӀ'],
            ['id' => 'bm', 'title' => 'Bamanankan'],
            ['id' => 'ce', 'title' => 'Нохчийн'],
            ['id' => 'ch', 'title' => 'Chamoru'],
            ['id' => 'cr', 'title' => 'ᓀᐦᐃᔭᐤ'],
            ['id' => 'cu', 'title' => 'церковнославя́нский язы́к'],
            ['id' => 'cv', 'title' => 'Чăваш чěлхи'],
            ['id' => 'dv', 'title' => 'ދިވެހި'],
            ['id' => 'ee', 'title' => 'Ɛʋɛgbɛ'],
            ['id' => 'ff', 'title' => 'Fulfulde / Pulaar'],
            ['id' => 'ho', 'title' => 'Hiri motu'],
            ['id' => 'ht', 'title' => 'Krèyol ayisyen'],
            ['id' => 'hz', 'title' => 'otsiHerero'],
            ['id' => 'ig', 'title' => 'Igbo'],
            ['id' => 'ii', 'title' => 'ꆇꉙ'],
            ['id' => 'io', 'title' => 'Ido'],
            ['id' => 'jv', 'title' => 'Basa Jawa'],
            ['id' => 'kg', 'title' => 'Kikongo'],
            ['id' => 'ki', 'title' => 'Gĩkũyũ'],
            ['id' => 'kj', 'title' => 'Kuanyama'],
            ['id' => 'kr', 'title' => 'Kanuri'],
            ['id' => 'kv', 'title' => 'коми кыв'],
            ['id' => 'kw', 'title' => 'Kernewek'],
            ['id' => 'lb', 'title' => 'Lëtzebuergesch'],
            ['id' => 'lg', 'title' => 'Luganda'],
            ['id' => 'li', 'title' => 'Limburgs'],
            ['id' => 'lu', 'title' => 'Luba-Katanga'],
            ['id' => 'mh', 'title' => 'Kajin M̧ajeļ'],
            ['id' => 'nb', 'title' => 'Norsk bokmål'],
            ['id' => 'nd', 'title' => 'isiNdebele'],
            ['id' => 'ng', 'title' => 'Owambo'],
            ['id' => 'nn', 'title' => 'Norsk nynorsk'],
            ['id' => 'nr', 'title' => 'Ndébélé'],
            ['id' => 'nv', 'title' => 'Dinékʼehǰí'],
            ['id' => 'ny', 'title' => 'chiCheŵa'],
            ['id' => 'oj', 'title' => 'ᐊᓂᔑᓈᐯᒧᐎᓐ'],
            ['id' => 'os', 'title' => 'Ирон æвзаг'],
            ['id' => 'pi', 'title' => 'Pāli'],
            ['id' => 'sc', 'title' => 'Sardu'],
            ['id' => 'se', 'title' => 'Sámegiella'],
            ['id' => 'ty', 'title' => 'Reo Tahiti'],
            ['id' => 've', 'title' => 'tshiVenḓa'],
            ['id' => 'wa', 'title' => 'Walon'],
            ['id' => 'pt', 'title' => 'Português brasileiro'],
            ['id' => 'zh', 'title' => '汉语'],
            ['id' => 'fr', 'title' => 'Français canadien'],
            ['id' => 'tl', 'title' => 'Filipino'],
            ['id' => 'sr', 'title' => 'Crnogorski jezik'],
            ['id' => 'de', 'title' => 'Deutsch (Schweiz)'],
            ['id' => 'de', 'title' => 'Deutsch (Österreich)'],
            ['id' => 'en', 'title' => 'English (USA)'],
            ['id' => 'en', 'title' => 'English (United Kingdom)']
        ];
    }
}

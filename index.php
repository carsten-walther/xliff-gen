<?php

// for infinite time of execution
ini_set('max_execution_time', '0');

/**
 * run: php -S localhost:8000
 */

require __DIR__ . '/vendor/autoload.php';

$uploaddir = 'public/tmp/';

$errorMessages = [];
$uploadedFiles = [];

// selectable languages
$languages = [
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

// form values
$productName = $_POST['productName'];
$sourceLanguage = $_POST['sourceLanguage'];
$targetLanguage = $_POST['targetLanguage'];

#die('<pre>' . print_r($_FILES["files"], true) . '</pre>');

die('<pre>' . print_r($_POST, true) . '</pre>');

if ($_FILES["files"]) {
    foreach ($_FILES["files"]["error"] as $key => $error) {

        $tmp_name = $_FILES["files"]["tmp_name"][$key];

        if (!$tmp_name) {
            continue;
        }

        $filename = basename($_FILES["files"]["name"][$key]);
        $uploadfile = $uploaddir . basename($filename);

        if ($error === UPLOAD_ERR_OK) {
            if (move_uploaded_file($tmp_name, $uploadfile)) {
                $uploadedFiles[] = $filename;
            } else {
                $errorMessages[] = "Could not move uploaded file '" . $tmp_name . "' to '" . $filename;
            }
        } else {
            $errorMessages[] = "Upload error. [" . $error . "] on file '" . $filename;
        }
    }
}

/*
$htmlString = "<f:layout name=\"Default\" />

<f:section name=\"main\">

	<f:variable name=\"deleteAddressLabel\">
		<f:translate key=\"frontendUser.action.delete.address\" default=\"Are you sure to delete this?\"/>
	</f:variable>

	<f:variable name=\"deleteCommunicationChannelLabel\">
		<f:translate key=\"frontendUser.action.delete.communicationChannel\" default=\"Are you sure to delete this?\"/>
	</f:variable>

	<f:comment>
		<!--
		<f:flashMessages />
		-->
	</f:comment>

	<f:comment>
		<!--
		<f:render partial=\"FrontendUser/FormErrors\" />
		-->
	</f:comment>

	<f:form action=\"update\" name=\"frontendUser\" object=\"{frontendUser}\" class=\"powermail_form-self-service\" enctype=\"multipart/form-data\" additionalAttributes=\"{ data-parsley-validate: 'data-parsley-validate', data-validate: 'html5', novalidate: '', autocomplete: 'off' }\">
		<f:render section=\"account\" arguments=\"{_all}\" />
		<f:for each=\"{frontendUser.organisations}\" as=\"organisation\" iteration=\"organisationIteration\">
			<f:render section=\"organisation\" arguments=\"{_all}\" />
			<f:render section=\"addresses\" arguments=\"{_all}\" />
			<f:render section=\"communicationChannels\" arguments=\"{_all}\" />
			<f:render section=\"categories\" arguments=\"{_all}\" />
		</f:for>
		<p>
			<small><f:translate key=\"required.fields\" default=\"Field marked with (*) are required.\"/></small>
		</p>
		<p class=\"align-right\">
			<f:form.button class=\"more highlight\">
				<f:translate key=\"frontendUser.action.update\" default=\"update\"/>
			</f:form.button>
		</p>
	</f:form>
</f:section>



<f:section name=\"account\">
	<fieldset class=\"powermail_fieldset\">
		<legend class=\"powermail_legend\">
			<f:translate key=\"frontendUser.form.account.legend\" default=\"Account\"/>
		</legend>
		<f:render partial=\"FrontendUser/FormFields/Textfield\" arguments=\"{
				object: '{frontendUser}',
				property: 'username',
				value: '{frontendUser.username}',
				label: '{f:translate(key: \'frontendUser.property.username\', default: \'username\')}',
				required: 'TRUE'
			}\" />
		<f:render partial=\"FrontendUser/FormFields/Textfield\" arguments=\"{
				object: '{frontendUser}',
				property: 'email',
				value: '{frontendUser.email}',
				label: '{f:translate(key: \'frontendUser.property.email\', default: \'email\')}',
				type: 'email',
				required: 'TRUE'
			}\" />
	</fieldset>
</f:section>



<f:section name=\"organisation\">
	<f:form.hidden property=\"organisations.{organisation.uid}\" value=\"{organisation}\"/>
	<fieldset class=\"powermail_fieldset\">
		<legend class=\"powermail_legend\">
			<f:translate key=\"frontendUser.form.organisation.legend\" default=\"Organisation\"/>
		</legend>
		<f:render partial=\"FrontendUser/FormFields/Textfield\" arguments=\"{
			object: '{frontendUser}',
			property: 'organisations.{organisation.uid}.name',
			value: '{organisation.name}',
			label: '{f:translate(key: \'frontendUser.property.organisation.name\', default: \'name\')}'
		}\" />
		<f:render partial=\"FrontendUser/FormFields/Textarea\" arguments=\"{
			object: '{frontendUser}',
			property: 'organisations.{organisation.uid}.description',
			value: '{organisation.description}',
			label: '{f:translate(key: \'frontendUser.property.organisation.description\', default: \'description\')}',
			rows: 6
		}\" />

		<f:comment>
		<f:render partial=\"FrontendUser/FormFields/Upload\" arguments=\"{
			object: '{frontendUser}',
			property: 'organisations.{organisation.uid}.logo',
			value: '{organisation.logo}',
			label: '{f:translate(key: \'frontendUser.property.organisation.logo\', default: \'logo\')}'
		}\" />
		</f:comment>

	</fieldset>
</f:section>



<f:section name=\"addresses\">
	<fieldset class=\"powermail_fieldset\">
		<legend class=\"powermail_legend\">
			<f:translate key=\"frontendUser.form.organisation.addresses.legend\" default=\"Addresses\"/>
		</legend>
		<div class=\"float-right\">
			<f:form.button type=\"submit\" class=\"more highlight\" name=\"organisation[{organisation.uid}][address]\" value=\"NEW\">
				<f:translate key=\"frontendUser.action.add\" default=\"add\"/>
			</f:form.button>
		</div>
		<div class=\"clearfix\"></div>
		<f:for each=\"{organisation.addresses}\" as=\"address\" iteration=\"addressIteration\">
			<f:form.hidden property=\"organisations.{organisation.uid}.addresses.{address.uid}\" value=\"{address}\"/>
			<div class=\"grid-x grid-padding-x\" id=\"organisations_{organisation.uid}_addresses_{address.uid}\">
				<div class=\"cell small-10\">
					<div class=\"grid-x grid-padding-x\">
						<div class=\"cell small-8\">
							<f:render partial=\"FrontendUser/FormFields/Textfield\" arguments=\"{
								object: '{frontendUser}',
								property: 'organisations.{organisation.uid}.addresses.{address.uid}.streetAddress',
								value: '{address.streetAddress}',
								label: '{f:translate(key: \'frontendUser.property.organisation.address.streetAddress\', default: \'streetAddress\')}',
								required: 'TRUE'
							}\" />
						</div>
						<div class=\"cell small-4\">
							<f:render partial=\"FrontendUser/FormFields/Textfield\" arguments=\"{
								object: '{frontendUser}',
								property: 'organisations.{organisation.uid}.addresses.{address.uid}.houseNumber',
								value: '{address.houseNumber}',
								label: '{f:translate(key: \'frontendUser.property.organisation.address.houseNumber\', default: \'houseNumber\')}',
								required: 'TRUE'
							}\" />
						</div>
					</div>
					<div class=\"grid-x grid-padding-x\">
						<div class=\"cell small-4\">
							<f:render partial=\"FrontendUser/FormFields/Textfield\" arguments=\"{
								object: '{frontendUser}',
								property: 'organisations.{organisation.uid}.addresses.{address.uid}.zip',
								value: '{address.zip}',
								label: '{f:translate(key: \'frontendUser.property.organisation.address.zip\', default: \'zip\')}',
								required: 'TRUE'
							}\" />
						</div>
						<div class=\"cell small-8\">
							<f:render partial=\"FrontendUser/FormFields/Textfield\" arguments=\"{
								object: '{frontendUser}',
								property: 'organisations.{organisation.uid}.addresses.{address.uid}.city',
								value: '{address.city}',
								label: '{f:translate(key: \'frontendUser.property.organisation.address.city\', default: \'city\')}',
								required: 'TRUE'
							}\" />
						</div>
					</div>
					<div class=\"grid-x grid-padding-x\">
						<div class=\"cell small-6\">
							<f:render partial=\"FrontendUser/FormFields/Checkbox\" arguments=\"{
								object: '{frontendUser}',
								property: 'organisations.{organisation.uid}.addresses.{address.uid}.hideAddress',
								value: '{address.hideAddress}',
								label: '{f:translate(key: \'frontendUser.property.organisation.address.hideAddress\', default: \'hideAddress\')}'
							}\" />
						</div>
						<div class=\"cell small-6\">
							<f:render partial=\"FrontendUser/FormFields/Checkbox\" arguments=\"{
								object: '{frontendUser}',
								property: 'organisations.{organisation.uid}.addresses.{address.uid}.mainAddress',
								value: '{address.mainAddress}',
								label: '{f:translate(key: \'frontendUser.property.organisation.address.mainAddress\', default: \'mainAddress\')}'
							}\" />
						</div>
					</div>
				</div>
				<div class=\"cell small-2 align-right\">
					<f:form.button type=\"submit\" class=\"more highlight\" name=\"organisation[{organisation.uid}][address]\" value=\"{address.uid}\" onclick=\"return confirm('{deleteAddressLabel}');\">
						<f:translate key=\"frontendUser.action.delete\" default=\"delete\"/>
					</f:form.button>
				</div>
			</div>
			<f:if condition=\"{addressIteration.isLast}\">
				<f:else>
					<hr/>
				</f:else>
			</f:if>
		</f:for>
	</fieldset>
</f:section>



<f:section name=\"communicationChannels\">
	<fieldset class=\"powermail_fieldset\">
		<legend class=\"powermail_legend\">
			<f:translate key=\"frontendUser.form.organisation.communicationChannels.legend\" default=\"Communication channels\"/>
		</legend>
		<div class=\"float-right\">
			<f:form.button type=\"submit\" class=\"more highlight\" name=\"organisation[{organisation.uid}][communicationChannel]\" value=\"NEW\">
				<f:translate key=\"frontendUser.action.new\" default=\"new\"/>
			</f:form.button>
		</div>
		<div class=\"clearfix\"></div>
		<f:for each=\"{organisation.communicationChannels}\" as=\"communicationChannel\" iteration=\"communicationChannelIteration\">
			<f:form.hidden property=\"organisations.{organisation.uid}.communicationChannels.{communicationChannel.uid}\" value=\"{communicationChannel}\"/>
			<div class=\"grid-x grid-padding-x\" id=\"organisations_{organisation.uid}_communicationChannels_{communicationChannel.uid}\">
				<div class=\"cell small-10\">
					<div class=\"grid-x grid-padding-x\">
						<div class=\"cell small-4\">
							<f:render partial=\"FrontendUser/FormFields/Select\" arguments=\"{
								object: '{frontendUser}',
								property: 'organisations.{organisation.uid}.communicationChannels.{communicationChannel.uid}.channelType',
								value: '{communicationChannel.channelType}',
								label: '{f:translate(key: \'frontendUser.property.organisation.communicationChannel.channelType\', default: \'channelType\')}',
								objects: '{communicationChannelTypes}'
							}\" />
						</div>
						<div class=\"cell small-4\">
							<f:render partial=\"FrontendUser/FormFields/Textfield\" arguments=\"{
								object: '{frontendUser}',
								property: 'organisations.{organisation.uid}.communicationChannels.{communicationChannel.uid}.value',
								value: '{communicationChannel.value}',
								label: '{f:translate(key: \'frontendUser.property.organisation.communicationChannel.value\', default: \'value\')}'
							}\" />
						</div>
						<div class=\"cell small-4\">
							<f:render partial=\"FrontendUser/FormFields/Checkbox\" arguments=\"{
								object: '{frontendUser}',
								property: 'organisations.{organisation.uid}.communicationChannels.{communicationChannel.uid}.hideCommunicationChannel',
								value: '{communicationChannel.hideCommunicationChannel}',
								label: '{f:translate(key: \'frontendUser.property.organisation.communicationChannel.hideCommunicationChannel\', default: \'hideCommunicationChannel\')}'
							}\" />
						</div>
					</div>
				</div>
				<div class=\"cell small-2 align-right\">
					<f:form.button type=\"submit\" class=\"more highlight\" name=\"organisation[{organisation.uid}][communicationChannel]\" value=\"{communicationChannel.uid}\" onclick=\"return confirm('{deleteCommunicationChannelLabel}');\">
						<f:translate key=\"frontendUser.action.delete\" default=\"delete\"/>
					</f:form.button>
				</div>
			</div>
			<f:if condition=\"{communicationChannelIteration.isLast}\">
				<f:else>
					<hr/>
				</f:else>
			</f:if>
		</f:for>
	</fieldset>
</f:section>



<f:section name=\"categories\">
	<fieldset class=\"powermail_fieldset\">
		<legend class=\"powermail_legend\">
			<f:translate key=\"frontendUser.form.organisation.categories.legend\" default=\"Categories\"/>
		</legend>
		<f:render partial=\"FrontendUser/FormFields/Multiselect\" arguments=\"{
			object: '{frontendUser}',
			property: 'organisations.{organisation.uid}.categories',
			value: '{organisation.categories}',
			label: '{f:translate(key: \'frontendUser.property.organisation.categories\', default: \'categories\')}',
			objects: '{categories}',
			required: 'TRUE'
		}\" />
	</fieldset>
</f:section>
";

$htmlString = str_replace(['\"', "\'"], '"', $htmlString);

/** @var \CarstenWalther\XliffGen\Extractor $extractor *
$extractor = new \CarstenWalther\XliffGen\Extractor($htmlString, [
    'sourceLanguage' => 'en',
    'targetLanguage' => 'de',
    'original' => 'SOURCE.FILE',
    'productName' => 'my_extension'
]);
$xlf = $extractor->extract();

/** @var \CarstenWalther\XliffGen\Generator $generator *
$generator = new \CarstenWalther\XliffGen\Generator($xlf);
$string = $generator->generate();

die($string);
*/

/** @var \Twig\Loader\FilesystemLoader $loader */
$loader = new \Twig\Loader\FilesystemLoader('public/templates');

/** @var \Twig\Environment $twig */
$twig = new \Twig\Environment($loader, []);

echo $twig->render('index.html', [
    'errorMessages' => $errorMessages,
    'uploadedFiles' => $uploadedFiles,
    'languages' => $languages
]);
exit();

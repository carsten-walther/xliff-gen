<?php

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

/** @var \CarstenWalther\XliffGen\Extractor $extractor */
$extractor = new \CarstenWalther\XliffGen\Extractor($htmlString, [
    'sourceLanguage' => 'en',
    'targetLanguage' => 'de',
    'original' => 'SOURCE.FILE',
    'productName' => 'my_extension'
]);
$xlf = $extractor->extract();

/** @var \CarstenWalther\XliffGen\Generator $generator */
$generator = new \CarstenWalther\XliffGen\Generator($xlf);
$string = $generator->generate();

die($string);


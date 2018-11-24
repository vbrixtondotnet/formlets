<?php

$protocol = $GLOBALS['conf']['protocol'] ?: 'http';

$ref['NO_DB_URI'] = array('img', 'file', 'logo', 'images', 'supportimg', 'secretfileuploadtest');

/**
 * these are the element types that we only allow free users to add it to form,
 * but only on inactive forms
 */
$ref['RESTRICTED_ELEMENTS_FOR_FREE_USERS'] = array(
    'FILE',
    'PAYMENT',
    'STRIPE',
    'PAYPAL',
    'STRIPEPAYPAL',
);

$ref['STRIPE_ADDITIONAL_METHODS'] = array(
    "ideal"=>"Pay with iDEAL",
    "alipay"=>"Pay with Alipay",
    "ach_credit_transfer"=>"Pay with ACH Credit Transfer",
    "bancontact"=>"Pay with Bancontact",
    "eps"=>"Pay with EPS",
    "giropay"=>"Pay with Giropay",
    "multibanco"=>"Pay with Multibanco",
    "p24"=>"Pay with P24",
    "sepa_debit"=>"Pay with SEPA Direct Debit",
    "sofort"=>"Pay with SOFORT"
);

$ref['FORM_VALIDATION_RULES'] = array(
    'settings' => array(
        'redirectUrl' => array(
            'regex' => '/(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/',
            'when' => array('doRedirect'=>true)
        )
    ),
    'elements' => array(
        'PAYPAL' => array(
            'required' => array('email')
        ),
        'STRIPE' => array(
            'required' => array('public_key', 'secret_key')
        ),
        'STRIPEPAYPAL' => array(
            'required' => array('email', 'public_key', 'secret_key')
        ),
    )
);

$ref['MAX_UPLOAD_SIZE'] = 5; //in MB

$ref['views_warning_percentage'] = array('50', '90', '100'); //50% 90% 100%

$ref['plan_lists'] = array(
    array(
        'name'           => 'Personal',
        'plan'          => 'FREE',
        'status'         => 'active',
        'display'        => true,
        'stripe_plans'   => array(
            'FREE'  => 0
        ),
        'descr'          => array(
            'Publish <span>1</span> form',
            '<span>Unlimited</span> responses',
            '<span>5000</span> form views per Month',
            'Admin Email notifications',
            'Export your data',
            '<span>500+ app</span> integrations included',
            'Modify <span>Colors & add Logo</span>',
            'Custom <span>Thank You</span> page',
        ),
        'smalldescr'=>array(
            'Publish <span>1</span> form',
            '<span>5000</span> form views per Month'
        ),
        'maxActiveForms' => 1,
        'maxViews'=>50000,
        'emailTemplates' => false,
        'theme' => false,
        'team' => false,
        'endpoint' => false,
    ),

    array(
        'name'           => 'OLD Dollar Club',
        'plan'          => 'DOLLAR-MONTHLY',
        'status'         => 'discontinued',
        'display'        => false,
        'stripe_plans'   =>array(
          'USD'=>array('MONTHLY'=>1.99)),
        'descr'          => array(
            '<span>Unlimited</span> Single Page Forms',
            '<span>250</span> responses per Month',
            '<span>150MB</span> Storage',
            'Email Notifications',
            'White Label Formlets',
            'Export Data Available',
            '<span>500+ app</span> integration support',
            'Change <span>Colors & add Logo</span>',
            'Custom Thank You page',
        ),
        'maxActiveForms' => 200,
        'maxViews'=>'UNLIMITED',
        'emailTemplates' => false,
        'theme' => false,
        'team' => false,
        'endpoint' => false,
    ),

    array(
        'name'           => 'OLD Standard',
        'plan'          => 'BASIC-MONTHLY',
        'status'         => 'discontinued',
        'display'        => false,
        'stripe_plans'   =>array(
          'USD'=>array('MONTHLY'=>10)),
        'descr'          => array(
            '<span>Unlimited</span> Multi-Page Forms',
            '<span>2,500</span> responses per Month',
            '<span>2GB</span> storage',
            'Email Notifications',
            'White Label Formlets',
            'Export Data Available',
            '<span>500+ app</span> integration support',
            'Custom Thank You page',
        ),
        'maxActiveForms' => 5,
        'maxViews'=>'UNLIMITED',
        'emailTemplates' => false,
        'theme' => false,
        'team' => false,
        'endpoint' => false,
    ),

    array(
        'name'           => 'Small Business',
        'plan'           => 'BASIC',
        'display'        => true,
        'status'         => 'active',
        'stripe_plans'   =>array(
            'USD'=>array('MONTHLY'=>18,'YEARLY' =>180),
            'EUR'=>array('MONTHLY'=>17,'YEARLY' =>170)
        ),
        'descr'          => array(
            'Publish <span>10</span> forms',
            '<span>Unlimited</span> responses',
            '<span>Unlimited</span> form views',
            'Respondent email notifications',
            'Export your data',
            '<span>Paypal & Stripe</span> payment option',
            '<span>500+ app</span> integration support',
            'Modify <span>Colors & add Logo</span>',
            'Custom <span>Thank You</span> page',
            '<span>No</span> Formlets branding on form',
            'Endpoints',
            'Pass <span>external data</span> to forms',
            '<span>Conditional Display</span> of fields',
            '<span>3GB</span> space for file upload',
            '<span>GDPR</span> compliant',
            '<span>Priority</span> customer support'
        ),
        'smalldescr'=>array(
            'Publish <span>10</span> forms',
            '<span>Unlimited</span> form views',
            '<span>Paypal & Stripe</span> payment option',
            'File Upload'
        ),
        'maxActiveForms' => 10,
        'maxViews'=>'UNLIMITED',
        'emailTemplates' => true,
        'theme' => true,
        'team' => false,
        'endpoint' => true,
    ),

    array(
        'name'           => 'OLD Professional',
        'plan'          => 'PRO-MONTHLY',
        'status'         => 'discontinued',
        'display'        => false,
        'price_usd'      => 30,
        'descr'          => array(
            '<span>Unlimited Multi-Page</span> forms',
            '<span>100,000</span> responses',
            '<span>Unlimited</span> form views',
            '<span>10GB</span> space for file upload',
            'Email Notifications',
            'Payments',
            'White Label Formlets',
            'Export Data Available',
            '<span>500+ app</span> integration support',
            '<span>Team</span> - 5 Additional Users',
            '<span>GDPR</span> compliant'
        ),
        'maxActiveForms' => 2000,
        'maxViews'=>'UNLIMITED',
        'emailTemplates' => false,
        'theme' => false,
        'team' => false,
        'endpoint' => false,
    ),

    array(
        'name'           => 'Professional',
        'plan'           => 'PRO',
        'status'         => 'active',
        'display'        => true,
        'price_usd'      => 48,
        'stripe_plans'   =>array(
            'USD'=>array('MONTHLY'=>48,'YEARLY' =>480),
            'EUR'=>array('MONTHLY'=>45,'YEARLY' =>450)
        ),
        'descr'          => array(
            'Publish <span>50</span> forms',
            '<span>Unlimited</span> responses',
            '<span>Unlimited</span> form views',
            'Respondent email notifications',
            'Export your data',
            '<span>Paypal & Stripe</span> payment option',
            '<span>500+ app</span> integration support',
            'Modify <span>Colors & add Logo</span>',
            'Custom <span>Thank You</span> page',
            '<span>No</span> Formlets branding on form',
            'Endpoints',
            'Pass <span>external data</span> to forms',
            '<span>Conditional Display</span> of fields',
            'Pass response data to new forms',
            '<span>Up to 2GB</span> upload file size',
            '<span>20GB</span> space for file upload',
            '<span>10</span> team users',
            '<span>GDPR</span> compliant',
            '<span>Priority</span> customer support'
        ),
        'smalldescr'=>array(
            'Publish <span>50</span> forms',
            '<span>Unlimited</span> form views',
            '<span>Paypal & Stripe</span> payment option',
            'File Upload'
        ),
        'maxActiveForms' => 50,
        'maxViews'=>'UNLIMITED',
        'emailTemplates' => true,
        'theme' => true,
        'team' => true,
        'endpoint' => true,
    ),

    array(
        'name'           => 'Enterprise',
        'plan'           => 'PRE',
        'status'         => 'active',
        'display'        => true,
        'price_usd'      => 160,
        'stripe_plans'   =>array(
            'USD'=>array('MONTHLY'=>260,'YEARLY' =>2600),
            'EUR'=>array('MONTHLY'=>250,'YEARLY' =>2500)
        ),
        'descr'          => array(
            'Publish <span>200</span> forms',
            '<span>Unlimited</span> responses',
            '<span>Unlimited</span> form views',
            'Respondent email notifications',
            'Export your data',
            '<span>Paypal & Stripe</span> payment option',
            '<span>500+ app</span> integration support',
            'Modify <span>Colors & add Logo</span>',
            'Custom <span>Thank You</span> page',
            '<span>No</span> Formlets branding on form',
            'Endpoints',
            'Pass <span>external data</span> to forms',
            '<span>Conditional Display</span> of fields',
            'Pass response data to new form',
            '<span>Up to 2GB</span> upload file size',
            '<span>50GB</span> space for file upload',
            '<span>Unlimited</span> team users',
            '<span>GDPR</span> compliant',
            'Request <span>Custom functionality</span>',
            'Support Telephone hotline 24/7',
        ),
        'smalldescr'=>array(
            'Publish <span>200</span> forms',
            '<span>Unlimited</span> form views',
            '<span>Paypal & Stripe</span> payment option',
            'File Upload'
        ),
        'maxActiveForms' => 200,
        'maxViews'=>'UNLIMITED',
        'emailTemplates' => true,
        'theme' => true,
        'team' => true,
        'endpoint' => true,
    ),

);
/*
$ref['plan_lookup'] = array();
foreach ($ref['plan_lists'] as $key => $plan) {
    if(empty($plan['stripe_plans'])) {
        $ref['plan_lookup'][$plan['label']] = $key;
    } else {
        foreach($plan['stripe_plans'] as $key2 => $stripe) {
            $ref['plan_lookup'][$key2] = $key;
        }
    }
}
*/
$ref['plan_lookup_name'] = array();
foreach ($ref['plan_lists'] as $key => $plan) {
    $ref['plan_lookup_name'][$plan['plan']] = $key;
}

$ref["element_address"] = array(
    'US'    => array(
        'state_type' => 'select',
        'states'     => array(
            //""   => "State",
            "AL" => "AL",
            "AK" => "AK",
            "AZ" => "AZ",
            "AR" => "AR",
            "CA" => "CA",
            "CO" => "CO",
            "CT" => "CT",
            "DC" => "DC",
            "DE" => "DE",
            "FL" => "FL",
            "GA" => "GA",
            "HI" => "HI",
            "ID" => "ID",
            "IL" => "IL",
            "IN" => "IN",
            "IA" => "IA",
            "KS" => "KS",
            "KY" => "KY",
            "LA" => "LA",
            "ME" => "ME",
            "MD" => "MD",
            "MA" => "MA",
            "MI" => "MI",
            "MN" => "MN",
            "MS" => "MS",
            "MO" => "MO",
            "MT" => "MT",
            "NE" => "NE",
            "NV" => "NV",
            "NH" => "NH",
            "NJ" => "NJ",
            "NM" => "NM",
            "NY" => "NY",
            "NC" => "NC",
            "ND" => "ND",
            "OH" => "OH",
            "OK" => "OK",
            "OR" => "OR",
            "PA" => "PA",
            "RI" => "RI",
            "SC" => "SC",
            "SD" => "SD",
            "TN" => "TN",
            "TX" => "TX",
            "UT" => "UT",
            "VT" => "VT",
            "VA" => "VA",
            "WA" => "WA",
            "WV" => "WV",
            "WI" => "WI",
            "WY" => "WY",
        ),
    ),
    'OTHER' => array(
        'state_type' => 'textbox',
        'states'     => array(),
    ),
    //'PHILIPPINES'=>array()
);

$ref["meta"] = array(
    'description' => 'Setup Forms, connect the data',
    'image' => '/static/img/twitter.png',
);

$ref["blacklisted_elements"] = array(
    'p(a|ä|å|á)ssword',
    'login',
    'username',
    'p/w',
    'pass',
    'wagwoord',
    'fjalëkalim',
    'salasana',
    'senha',
    'adgangskode',
    'wachtwoord',
    'passwort',
    'contraseña'
);

$ref["credit_card_detector"] = array(
    'cvv'
);

$ref['mail_blacklist'] = array(
    'qatardutyfree',
    'qatarairdutyfree',
    'Admin portal',
    'SycleRQ',
    'Admin care portal',
    '52cay.net'
);

$ref['mail_ip_blacklist'] = array(
    '194.226.137.235','217.28.208.180'
);
$ref["languages"]=array(
    'en',
    'ph',
);

$ref['payment_currencies'] = array(
    'INR',
    'USD',
    'AUD',
    'BRL',
    'CAD',
    'CNY',
    'CZK',
    'DKK',
    'EUR',
    'HKD',
    'HUF',
    'ILS',
    'JPY',
    'MYR',
    'MXN',
    'NZD',
    'NOK',
    'PHP',
    'PLN',
    'SGD',
    'SEK',
    'CHF',
    'TRY',
    'GBP',
);

// TODO(fil) : add the other colors to the array
$ref['formcolors']=array(
    array(
      'label'=>'Page Background',
      'tooltip'=>'This color changes the browser background color',
      'color'=>'#F5F5F5',
      'fontcolor'=>'#222',
      'prop'=>'themeBrowserBackground'
    ),
    array(
      'label'=>'Form Background',
      'tooltip'=>'This color changes the form background color',
      'color'=>'#FFF',
      'fontcolor'=>'#222',
      'prop'=>'themeFormBackground'
    ),
    array(
      'label'=>'Form Border',
      'tooltip'=>'This color changes the border color of the form',
      'color'=>'#D6D7D6',
      'fontcolor'=>'#222',
      'prop'=>'themeFormBorder'
    ),
    array(
      'label'=>'Field Background',
      'tooltip'=>'This color changes the field background color',
      'color'=>'#FFF',
      'fontcolor'=>'#222',
      'prop'=>'themeFieldBackground'
    )
);

$ref['rights'] = array(
    'read'              => 1,
    'edit'              => 2,
    'delete'            => 4,
    'create'            => 8, //can invite user
    'manage_account'    => 16,
    'manage_forms'   => 32,
);

$ref['user_all_rights'] = 63;

$ref['pagination_rows'] = 10;

$ref['integration']['type'] = array(
    'stripe'=> array('key','secret'),
);

$ref['flatpickr_langs'] = array(
    'ar'=>'Arabic',
    'bg'=>'Bulgarian',
    'bn'=>'Bengali (Bangla)',
    'cat'=>'Catalan',
    'cs'=>'Czech',
    'cy'=>'Welsh',
    'da'=>'Danish',
    'de'=>'German',
    'es'=>'Spanish',
    'et'=>'Estonian',
    'fa'=>'Persian (Farsi)',
    'fi'=>'Finnish',
    'fr'=>'French',
    'gr'=>'Greek',
    'he'=>'Hebrew',
    'hi'=>'Hindi',
    'hr'=>'Croatian',
    'hu'=>'Hungarian',
    'id'=>'Indonesian',
    'it'=>'Italian',
    'ja'=>'Japanese',
    'ko'=>'Korean',
    'lt'=>'Lithuanian',
    'lv'=>'Latvian (Lettish)',
    'mk'=>'Macedonian',
    'ms'=>'Malay',
    'my'=>'Burmese',
    'nl'=>'Dutch',
    'no'=>'Norwegian',
    'pa'=>'Punjabi (Eastern)',
    'pl'=>'Polish',
    'pt'=>'Portuguese',
    'ro'=>'Romanian',
    'ru'=>'Russian',
    'si'=>'Sinhalese',
    'sl'=>'Slovenian',
    'sq'=>'Albanian',
    'sr'=>'Serbian',
    'sv'=>'Swedish',
    'th'=>'Thai',
    'tr'=>'Turkish',
    'uk'=>'Ukrainian',
    'vn'=>'Vietnamese',
    'zh'=>'Chinese'
);

$ref['FORMLETS_KEY'] = 'FORMLETSSECRETKEY54321';

$ref['default_email_template'] = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<style>
			@import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro);

			.white-background {
				background-color: #FFF;
			}
			.lightGray {
				color: #C5CAC5;
			}

			table.body-wrap .container {
				border: 2px solid #D6D7D6;
				border-radius: 3px;
                font-family: "Source Sans Pro", "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
			}

			.padding {
				padding: 10px 0;
			}

			table {
				border-collapse: collapse;
			}
			table.body-wrap {
				width: 100%;
				padding: 20px;
			}

			h1, h2, h3 {
				font-family: "Source Sans Pro", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
				line-height: 1.1;
				margin-bottom: 15px;
				margin: 40px 0 10px;
				line-height: 1.2;
				font-weight: 200;
			}
			h1 {
				font-weight: 500;
				margin: 18px;
				font-size: 36px;
			}

			.container {
				margin-top: 10px;
				display: block!important;
				max-width: 600px;
				margin: 0 auto!important; /* makes it centered */
				clear: both!important;
			}

			.body-wrap .container {
				padding: 20px;
			}

			.content {
				max-width: 600px;
				margin: 0 auto;
				display: block;
			}

			.content table {
				width: 100%;
			}
		</style>
	</head>
	<body>
    <table class="body-wrap">
		<tr>
			<td class="container white-background">
				<div class="content">
    				<table>
    					<tr>
    						<td align="center">
    							<h1>{SUBMISSION::SUBJECT}</h1>
    							<p class="lightGray">{SUBMISSION::DATE}</p>
    						</td>
    					</tr>
    					<tr class="padding"><td class="padding"></td></tr>
    					<tr>
    						<td>
    							{email_body}
    						</td>
    					</tr>
    				</table>
				</div>
			</td>
		</tr>
	</table>
    </body>
</html>
EOD;


$ref['default_autoresponder'] = <<<EOD
<!DOCTYPE html>
<html>
<head></head>
<body>
</body>
</html>
EOD;

<?php
/**
 * SAML 2.0 remote SP metadata for simpleSAMLphp.
 *
 * See: http://simplesamlphp.org/docs/trunk/simplesamlphp-reference-sp-remote
 */

/*
 * Example simpleSAMLphp SAML 2.0 SP
 */
/*
$metadata['https://saml2sp.example.org'] = array(
	'AssertionConsumerService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
	'SingleLogoutService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
);
*/

/*
 * This example shows an example config that works with Google Apps for education.
 * What is important is that you have an attribute in your IdP that maps to the local part of the email address
 * at Google Apps. In example, if your google account is foo.com, and you have a user that has an email john@foo.com, then you
 * must set the simplesaml.nameidattribute to be the name of an attribute that for this user has the value of 'john'.
 */
/*
$metadata['google.com'] = array(
	'AssertionConsumerService' => 'https://www.google.com/a/g.feide.no/acs',
	'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:email',
	'simplesaml.nameidattribute' => 'uid',
	'simplesaml.attributes' => FALSE,
);
*/

$metadata['https://auth.coreprim.fr/saml/metadata'] = array (
  'entityid' => 'https://auth.coreprim.fr/saml/metadata',
  'name' =>
  array (
    'en' => 'CRDP Aix Marseille',
  ),
  'description' =>
  array (
    'en' => 'CRDP',
  ),
  'OrganizationName' =>
  array (
    'en' => 'CRDP',
  ),
  'OrganizationDisplayName' =>
  array (
    'en' => 'CRDP Aix Marseille',
  ),
  'url' =>
  array (
    'en' => 'https://www.coreprim.fr/main-menu.html',
  ),
  'OrganizationURL' =>
  array (
    'en' => 'https://www.coreprim.fr/main-menu.html',
  ),
  'contacts' =>
  array (
  ),
  'metadata-set' => 'saml20-sp-remote',
  'AssertionConsumerService' =>
  array (
    0 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
      'Location' => 'https://auth.coreprim.fr/saml/proxySingleSignArtifact',
      'index' => 0,
      'isDefault' => true,
    ),
    1 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'https://auth.coreprim.fr/saml/proxySingleSignOnPost',
      'ResponseLocation' => 'https://auth.coreprim.fr/saml/singleSignOnReturn',
      'index' => 1,
      'isDefault' => false,
    ),
   ),
  'SingleLogoutService' =>
  array (
    0 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
      'Location' => 'https://auth.coreprim.fr/saml/proxySingleLogoutSOAP',
    ),
    1 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'https://auth.coreprim.fr/saml/proxySingleLogout',
      'ResponseLocation' => 'https://auth.coreprim.fr/saml/proxySingleLogoutReturn',
    ),
    2 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'https://auth.coreprim.fr/saml/proxySingleLogout',
      'ResponseLocation' => 'https://auth.coreprim.fr/saml/proxySingleLogoutReturn',
    ),
  ),
  'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
  'simplesaml.nameidattribute' => 'id_dbuser',
  'AttributeNameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:basic',
  'simplesaml.attributes' => true,
  'attributes' => array('id_dbuser', 'mail_dbuser'),
  'redirect.sign' => true,
);


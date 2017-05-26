<?php

namespace Drupal\passbook\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure passbook settings for this site.
 */
class PassbookSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'passbook_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['passbook.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('passbook.settings');

    $form['p12_file_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('P12 File Path'),
      '#default_value' => $config->get('p12_file_path'),
      '#required' => TRUE,
      '#description' => $this->t('Path to your .p12 file.'),
    ];

    $form['p12_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('P12 Password'),
      '#default_value' => $config->get('p12_password'),
      '#description' => $this->t('Password associated to your .p12 file.'),
    ];

    $form['wwdr_file_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('WWDR File Path'),
      '#default_value' => $config->get('wwdr_file_path'),
      '#required' => TRUE,
      '#description' => $this->t('<a href="@cert_link">Apple’s World Wide Developer Relations (WWDR) certificate</a>.
        You will have to add this to your Keychain Access and export it in .pem format to use it with the library. <br />
        The WWDR certificate links your development certificate to Apple, completing the trust chain for your application.',
        ['@cert_link' => 'http://developer.apple.com/certificationauthority/AppleWWDRCA.cer']
      ),
    ];

    $form['pass_type_identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pass Type Identifier'),
      '#default_value' => $config->get('pass_type_identifier'),
      '#description' => $this->t('Your pass type identifier.'),
      '#required' => TRUE,
    ];

    $form['team_identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Team Identifier'),
      '#default_value' => $config->get('team_identifier'),
      '#description' => $this->t('Your team ID.'),
      '#required' => TRUE,
    ];

    $form['organization_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organization Name'),
      '#default_value' => $config->get('organization_name'),
      '#description' => $this->t('The organization name is displayed on the lock screen when your pass is relevant and by apps such as Mail which act as a conduit for passes.<br /> 
        Choose a name that users recognize and associate with your organization or company.'
      ),
      '#required' => TRUE,
    ];

    $form['output_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Output Path'),
      '#default_value' => $config->get('output_path'),
      '#description' => $this->t('Path to output directory. (e.g.: public://passbook-data)'),
      '#required' => TRUE,
    ];

    $form['icon_file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon File'),
      '#default_value' => $config->get('icon_file'),
      '#description' => $this->t('Path to icon.'),
      '#required' => TRUE,
    ];

    $form['icon2x_file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon@2x File'),
      '#default_value' => $config->get('icon2x_file'),
      '#description' => $this->t('Path to icon.'),
    ];

    $form['web_service_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Web Service Url'),
      '#default_value' => $config->get('web_service_url'),
      '#description' => $this->t('The URL to your web service, as specified in the pass.'),
    ];

    // Documentation.
    $form['docs'] = [
      '#type' => 'details',
      '#title' => $this->t('Documentation'),
    ];

    // How to create a .cer file.
    $form['docs']['cert'] = [
      '#type' => 'details',
      '#title' => $this->t('How to create pass type ID certificate'),
    ];

    $items = [
      'In <a href="https://developer.apple.com/account/#/welcome" target="_blank">Certificates, Identifiers & Profiles</a>, select <b>Certificates, Identifiers & Profiles</b>.',
      'Under <b>Identifiers</b> select <b>Pass Type IDs</b>.',
      'click <b>(+)</b> and create a new one (Enter the description and pass type identifier, and click Submit).',
      'Under <b>Certificates</b> click <b>Production</b>.',
      'Click <b>(+)</b> and select <b>Pass Type ID Certificate</b>, and click <b>Continue</b>.',
      'For <b>Pass Type ID</b> select previously created Pass Type ID, and click <b>Continue</b>.',
      'Follow instruction from the page and create a <b>CSR</b> file and click <b>Continue</b>.',
      'Select previously created <b>.certSigningRequest</b> file and click <b>Continue</b>.',
      'Click <b>Download</b> to download <b>.cer</b> file.',
    ];

    $form['docs']['cert']['details'] = [
      '#theme' => 'item_list',
      '#items' => $this->addHtmlItems($items),
    ];

    // How to convert the .cer file to .p12.
    $form['docs']['p12_convert'] = [
      '#type' => 'details',
      '#title' => $this->t('How to export previously created Pass Type ID Certificate from Apple to .p12'),
    ];

    $items = [
      'On your Mac open <b>Keychain Access</b>',
      'If you have not already added the certificate to Keychain, select <b>File > Import</b>. Then navigate to the certificate file (the .cer file) you obtained from Apple.',
      'Select the <b>Keys</b> category in Keychain Access.',
      'Select the private key associated with your Pass Type ID Certificate. The private key is identified by the Pass Type ID Developer: public certificate that is paired with it.',
      'Select <b>File > Export Items</b>.',
      'Save your key in the Personal Information Exchange (.p12) file format.',
      'You will be prompted to create a password that is used when you attempt to import this key on another computer.',
    ];

    $form['docs']['p12_convert']['details'] = [
      '#theme' => 'item_list',
      '#items' => $this->addHtmlItems($items),
    ];

    // Pass type identifier.
    $form['docs']['pass_type_id'] = [
      '#type' => 'details',
      '#title' => $this->t('How to find my pass type identifier.'),
    ];

    $items = [
      'In <a href="https://developer.apple.com/account/#/welcome" target="_blank">Certificates, Identifiers & Profiles</a>, select <b>Certificates, Identifiers & Profiles</b>.',
      'Under <b>Identifiers</b> select <b>Pass Type IDs</b>.',
      'There you will find your Pass Type IDs.',
    ];

    $form['docs']['pass_type_id']['details'] = [
      '#theme' => 'item_list',
      '#items' => $this->addHtmlItems($items),
    ];

    // Pass type identifier.
    $form['docs']['team_id'] = [
      '#type' => 'details',
      '#title' => $this->t('How to find my team ID.'),
    ];

    $items = [
      'On the <a href="https://developer.apple.com/account/#/welcome" target="_blank">Account page</a>, select <b>Membership</b>.',
      'There you will find your <b>Team ID</b>.',
    ];

    $form['docs']['team_id']['details'] = [
      '#theme' => 'item_list',
      '#items' => $this->addHtmlItems($items),
    ];

    // Pass web service.
    $form['docs']['web_service'] = [
      '#type' => 'details',
      '#title' => $this->t('Web service.'),
    ];

    $items = [
      'More details about passbook Web Service you can find <a href="https://developer.apple.com/library/content/documentation/PassKit/Reference/PassKit_WebService/WebService.html" target="_blank">here</a>.',
      'If you are using <b>Passbook Web service</b> provided by passbook module path to webservice if <b>[SITE_URL]/passbook-webservice</b>.',
    ];

    $form['docs']['web_service']['details'] = [
      '#theme' => 'item_list',
      '#items' => $this->addHtmlItems($items),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Allow html in item_list.
   *
   * @param array $items
   *   Items list.
   *
   * @return array
   *   Items list.
   */
  public function addHtmlItems($items) {
    $out = [];

    foreach ($items as $item) {
      $out[] = [
        '#markup' => $item,
        '#allowed_tags' => ['a', 'b'],
      ];
    }

    return $out;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate files and paths.
    $fields = [
      'p12_file_path',
      'wwdr_file_path',
      // 'output_path',
      'icon_file',
      'icon2x_file',
    ];

    foreach ($fields as $field) {
      if ($path = $form_state->getValue($field)) {
        if (!file_exists($path)) {
          $form_state->setError($form[$field], $this->t("The specified %field doesn't exist.", ['%field' => $form[$field]['#title']->__toString()]));
        }
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save passbook config.
    $this->config('passbook.settings')
      ->set('p12_file_path', $form_state->getValue('p12_file_path'))
      ->set('p12_password', $form_state->getValue('p12_password'))
      ->set('wwdr_file_path', $form_state->getValue('wwdr_file_path'))
      ->set('pass_type_identifier', $form_state->getValue('pass_type_identifier'))
      ->set('team_identifier', $form_state->getValue('team_identifier'))
      ->set('organization_name', $form_state->getValue('organization_name'))
      ->set('output_path', $form_state->getValue('output_path'))
      ->set('icon_file', $form_state->getValue('icon_file'))
      ->set('icon2x_file', $form_state->getValue('icon2x_file'))
      ->set('web_service_url', trim($form_state->getValue('web_service_url')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

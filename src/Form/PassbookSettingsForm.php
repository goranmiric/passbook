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
    ];

    $form['p12_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('P12 Password'),
      '#default_value' => $config->get('p12_password'),
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
      '#description' => $this->t('Your organization name.'),
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
      '#description' => $this->t('The URL to your web service, as specified in the pass..'),
    ];

    return parent::buildForm($form, $form_state);
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

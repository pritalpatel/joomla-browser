<?php

namespace Codeception\Module;

use Codeception\Module\WebDriver;

class JoomlaBrowser extends WebDriver
{
    /**
     * The module required fields, to be set in the suite .yml configuration file.
     *
     * @var array
     */
    protected $requiredFields = array(
        'url',
        'browser',
        'username',
        'password',
        'database type',
        'database host',
        'database user',
        'database password',
        'database name',
        'database type',
        'database prefix',
        'admin email',
        'language',

    );

    /**
     * Function to Do Admin Login In Joomla!
     */
    public function doAdministratorLogin()
    {
        $I = $this;
        $I->amOnPage('/administrator/index.php');
        $I->fillField('#mod-login-username', $this->config['username']);
        $I->fillField('#mod-login-password', $this->config['password']);
        // @todo: update login button in joomla login screen to make this xPath more friendly
        $I->click("//form[@id='form-login']/fieldset/div[3]/div/div/button");
        $I->waitForText('Control Panel', 10, 'H1');
    }

    /**
     * Installs Joomla
     */
    public function installJoomla()
    {
        $I = $this;

        // Install Joomla CMS');

        // @todo: activate the filesystem module
        //$I->expect('no configuration.php is in the Joomla CMS folder');
        //$I->dontSeeFileFound('configuration.php', $this->config['Joomla folder')];
        $I->amOnPage('/installation/index.php');
        $this->debug('I open Joomla Installation Configuration Page and fill the fields');

        // I Wait for the text Main Configuration, meaning that the page is loaded
        $this->debug('I for Main Configuration');
        $I->waitForText('Main Configuration', 10, 'h3');

        $this->debug('I click Language Selector');
        $I->click("//div[@id='jform_language_chzn']/a"); // Language Selector
        $this-debug('I select en-GB');
        $I->click("//li[text()='English (United Kingdom)']"); // English (United Kingdom)
        sleep(1);
        $this-debug('I fill Site Name');
        $I->fillField('Site Name', 'Joomla CMS test');
        $this-debug('I fill Site Description');
        $I->fillField('Description', 'Site for testing Joomla CMS');

        // I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
        $this-debug('I fill Admin Email');
        $I->fillField('Admin Email', $this->config['admin email']);
        $this-debug('I fill Admin Username');
        $I->fillField('Admin Username', $this->config['username']);
        $this-debug('I fill Admin Password');
        $I->fillField('Admin Password', $this->config['password']);
        $this-debug('I fill Admin Password Confirmation');
        $I->fillField('Confirm Admin Password', $this->config['password']);
        $this-debug('I click Site Offline: no');
        $I->click("//fieldset[@id='jform_site_offline']/label[2]"); // ['No Site Offline']
        $this-debug('I click Next');
        $I->click('Next');

        $this->debug('I Fill the form for creating the Joomla site Database');
        $I->waitForText('Database Configuration', 10, 'h3');

        $this-debug('I select MySQLi');
        $I->selectOption('#jform_db_type', $this->config['database type']);
        $this-debug('I fill Database Host');
        $I->fillField('Host Name', $this->config['database host']);
        $this-debug('I fill Database User');
        $I->fillField('Username', $this->config['database user']);
        $this-debug('I fill Database Password');
        $I->fillField('Password', $this->config['database password']);
        $this-debug('I fill Database Name');
        $I->fillField('Database Name', $this->config['database name']);
        $this-debug('I fill Database Prefix');
        $I->fillField('Table Prefix', $this->config['database prefix']);
        $this-debug('I click Remove Old Database ');
        $I->click("//label[@for='jform_db_old1']"); // Remove Old Database button
        $I->click('Next');
        $this-debug('I click Next');
        $I->click('Next');

        $this->debug('I install joomla with or without sample data');
        $I->waitForText('Finalisation', 10, 'h3');

        $this-debug('I install Sample Data');
        if ($this->config['install sample data']) :
            $I->selectOption('#jform_sample_file', $this->config['sample data']);
        else :
            $I->selectOption('#jform_sample_file', '#jform_sample_file0'); // No sample data
        endif;
        $I->click('Install');

        // Wait while Joomla gets installed
        $I->waitForText('Congratulations! Joomla! is now installed.', 30, 'h3');
        $this->debug('Joomla is now installed');
    }

    /**
     * Sets in Adminitrator->Global Configuration the Error reporting to Development
     *
     * @note: doAdminLogin() before
     */
    public function setErrorReportingtoDevelopment()
    {
        $I = $this;
        $I->amOnPage('/administrator/index.php?option=com_config');
        $I->waitForText('Global Configuration',10,'.page-title');
        $I->click('Server');
        $I->waitForElementVisible("//div[@id='jform_error_reporting_chzn']/a"); // Error reporting Dropdown
        $I->click("//div[@id='jform_error_reporting_chzn']/a");
        $I->click("//div[@id='jform_error_reporting_chzn']/div/ul/li[contains(text(), 'Development')]"); // Development
        $I->click('Save');
        $I->waitForText('Global Configuration',10,'.page-title');
        $I->see('Configuration successfully saved.','#system-message-container');
    }

    /**
     * Sets in Adminitrator->Global Configuration the Error reporting to Development
     *
     * @note: doAdminLogin() before
     */
    public function installExtensionFromDirectory($path)
    {
        $I = $this;
        $I->amOnPage('/administrator/index.php?option=com_installer');
        $I->click('Install from Directory');
        $I->fillField('#install_directory', $path);
        // @todo: we need to find a better locator for the following Install button
        $I->click("//input[contains(@onclick,'Joomla.submitbutton3()')]"); // Install button
        $I->waitForText('was successful', 10, '#system-message-container');
    }
}

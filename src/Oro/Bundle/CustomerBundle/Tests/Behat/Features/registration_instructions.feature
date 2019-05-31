@regression
Feature: Registration Instructions
  In order to let customers know about alternative account registration options
  As an Administrator
  I want to be able to enable and configure account registration instructions for the store frontend login page.

#  Description
#  Add configuration settings (website, organization and global levels) to enable account registration instructions and to edit account registration message for the store frontend login page.
#  See the text of the default account registration instructions below.
#  By default displayin of these instructions should be disabled (default system setup - self-registration is enabled, displaying the instructions - disabled).
#  Displaying of the account registration instructions does not depend on the self-registration (e.g. it is possible that both the link to the self-registration and the instructions could be displayed at the same time).
#  Configuration
#  New configuration options on the global, organization and website levels at Configuration -> Commerce -> Customer -> Customer Users -> Customer Users Registration:
#  Show Registration Instructions - checkbox. Default value: unchecked (disabled). Hint:
#  When enabled, the registration instructions will be shown on the store frontend login page.
#  Registration Instructions Text - textarea. Hint: no hint. Default value:
#  To register for a new account, contact a sales
#  representative at 1 (800) 555-0123
#  Acceptance Criteria
#  Administrator can turn on/off the account registration instructions globally, per organization, per website
#  Administrator can modif account registration instructions text globally, per organization, per website

  Scenario: Create different window session
    Given sessions active:
      | Admin  |first_session |
      | User   |second_session|

  Scenario: Default condition
    Given I proceed as the User
    When I am on homepage
    And click "Sign In"
    Then I should not see "To register for a new account, contact a sales representative at 1 (800) 555-0123"

  Scenario: Show Registration Instructions with default text
    Given I proceed as the Admin
    And login as administrator
    And go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And fill "Customer Users Registration form" with:
      |Show Registration Instructions Default|false|
      |Show Registration Instructions        |true |
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    When reload the page
    Then I should see "To register for a new account, contact a sales representative at 1 (800) 555-0123"

  Scenario: Show Registration Instructions with custom text
    Given I proceed as the Admin
    And fill "Customer Users Registration form" with:
      |Registration Instructions Text Default|false               |
      |Registration Instructions Text        |Test text blablabla |
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    When reload the page
    Then I should see "Test text blablabla"

  Scenario: Show Registration Instructions when Text is setted to default
    Given I proceed as the Admin
    And fill "Customer Users Registration form" with:
      |Registration Instructions Text Default|true|
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    When reload the page
    Then I should see "To register for a new account, contact a sales representative at 1 (800) 555-0123"
    And I should not see "Test text blablabla"

  Scenario: Show Registration Instructions is setted to default
    Given I proceed as the Admin
    And fill "Customer Users Registration form" with:
      |Show Registration Instructions Default|true|
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    When reload the page
    Then I should not see "To register for a new account, contact a sales representative at 1 (800) 555-0123"

  Scenario: New Site creation and configuration (Site level)
    Given I proceed as the Admin
    And go to System/ Websites
    And click "Create Website"
    And fill form with:
      |Name                           |NewSite                   |
      |Guest Role                     |Non-Authenticated Visitors|
      |Default Self-Registration Role |Buyer                     |
    And save and close form
    And should see "Website has been saved" flash message
    And go to System/ Websites
    And click "Set default" on row "NewSite" in grid
    And click "Configuration" on row "Default" in grid
    And I follow "System Configuration/Websites/Routing" on configuration sidebar
    And I fill "Routing General form" with fictional website
    And submit form
    And I should see "Configuration saved" flash message
    And go to System/ Websites
    And click "Configuration" on row "NewSite" in grid

  Scenario: Default condition (Site level)
    Given I proceed as the User
    When reload the page
    Then I should not see "To register for a new account, contact a sales representative at 1 (800) 555-0123"

  Scenario: Show Registration Instructions with default text (Site level)
    Given I proceed as the Admin
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And fill "Customer Users Registration form" with:
      |Show Registration Instructions Default|false|
      |Show Registration Instructions        |true |
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    When reload the page
    Then I should see "To register for a new account, contact a sales representative at 1 (800) 555-0123"

  Scenario: Show Registration Instructions with custom text (Site level)
    Given I proceed as the Admin
    And fill "Customer Users Registration form" with:
      |Registration Instructions Text Default|false               |
      |Registration Instructions Text        |Test text blablabla |
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    When reload the page
    Then I should see "Test text blablabla"
    When I proceed as the Admin
    And go to System/ Websites
    And click "Set default" on row "Default" in grid
    And click "Configuration" on row "Default" in grid
    And I follow "System Configuration/Websites/Routing" on configuration sidebar
    And I fill "Routing General form" with actual website
    And submit form
    And I should see "Configuration saved" flash message
    And go to System/ Websites
    And click "Configuration" on row "NewSite" in grid
    And I follow "System Configuration/Websites/Routing" on configuration sidebar
    And I fill "Routing General form" with fictional website
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    And reload the page
    Then I should not see "Test text blablabla"
    And I should not see "To register for a new account, contact a sales representative at 1 (800) 555-0123"
    And I proceed as the Admin
    And go to System/ Websites
    And click "Set default" on row "NewSite" in grid
    And click "Configuration" on row "Default" in grid
    And I follow "System Configuration/Websites/Routing" on configuration sidebar
    And I fill "Routing General form" with fictional website
    And submit form
    And I should see "Configuration saved" flash message
    And go to System/ Websites
    And click "Configuration" on row "NewSite" in grid
    And I follow "System Configuration/Websites/Routing" on configuration sidebar
    And I fill "Routing General form" with actual website
    And submit form
    And I should see "Configuration saved" flash message
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar

  Scenario: Show Registration Instructions when Text is setted to default (Site level)
    Given I proceed as the Admin
    And fill "Customer Users Registration form" with:
      |Registration Instructions Text Default|true|
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    When reload the page
    Then I should see "To register for a new account, contact a sales representative at 1 (800) 555-0123"
    And I should not see "Test text blablabla"

  Scenario: Show Registration Instructions is setted to default (Site level)
    Given I proceed as the Admin
    And fill "Customer Users Registration form" with:
      |Show Registration Instructions Default|true|
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    When reload the page
    Then I should not see "To register for a new account, contact a sales representative at 1 (800) 555-0123"

@regression
@ticket-BB-26729
@fixture-OroCustomerBundle:CustomerUserAmandaRCole.yml
Feature: Guest Access Allowed System Pages
  In order to allow guests to access specific system pages when guest access is disabled
  As an Administrator
  I want to be able to configure which system pages are accessible to guests

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Guest | second_session |

  Scenario: Disable guest access
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/Configuration
    And I follow "Commerce/Guests/Website Access" on configuration sidebar
    And uncheck "Use default" for "Enable Guest Access" field
    And I uncheck "Enable Guest Access"
    When I save form
    Then I should see "Configuration Saved" flash message

  Scenario: Verify system pages are not accessible when guest access is disabled
    Given I proceed as the Guest
    And I am on the homepage
    When I go to "/contact-us/"
    Then I should be on Customer User Login page

  Scenario: Configure allowed system pages
    Given I proceed as the Admin
    And I go to System/Configuration
    And I follow "Commerce/Guests/Website Access" on configuration sidebar
    And uncheck "Use default" for "Allow Guest Access to System Pages" field
    And I fill form with:
      | Allow Guest Access to System Pages | [Oro Contactus Bridge Contact Us Page (Contact Us)] |
    When I save form
    Then I should see "Configuration Saved" flash message

  Scenario: Verify configured system page is accessible
    Given I proceed as the Guest
    And I am on the homepage
    When I go to "/contact-us/"
    Then Page title equals to "Contact Us"

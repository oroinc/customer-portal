@regression
@ticket-BAP-12990
@automatically-ticket-tagged
Feature: Configure usual and secure url
  In order to configure usual and secure url
  As a Site Administrator
  I want to be able to change them in settings

  Scenario: Set invalid URL
    Given I login as administrator
    And I go to System/Configuration
    And I follow "System Configuration/Websites/Routing" on configuration sidebar
    When I fill "Routing Settings Form" with:
      | URL | no-proper-url-value |
    And I click "Save settings"
    Then I should see "This value is not a valid URL."

  Scenario: Set empty URL
    Given I fill "Routing Settings Form" with:
      | URL |  |
    And I click "Save settings"
    Then I should see "This value should not be blank."

  Scenario: Set Secure URL and usual URL properly
    Given I fill "Routing Settings Form" with:
      | Secure URL | https://dev-commerce-new.local/ |
      | URL | http://dev-commerce-new.local/ |
    And I click "Save settings"
    Then I should see "Configuration saved" flash message

  Scenario: Set invalid Secure URL
    Given I fill "Routing Settings Form" with:
      | Secure URL | no-proper-url-value |
    And I click "Save settings"
    Then I should see "This value is not a valid URL."

  Scenario: Set empty Secure URL
    Given I fill "Routing Settings Form" with:
      | Secure URL |  |
    And I click "Save settings"
    Then I should see "This value should not be blank."

@ticket-BAP-21040

Feature: Fallback Localization Logic
  In order to check fallback logic
  As an administrator
  I want to go to Customer User Configuration and check Use Default functionality of Cookies Banner Fallback Localization

  Scenario: Prepare the session
    Given I login as administrator
    And I go to System/Configuration
    And I follow "System Configuration/General Setup/Display Settings" on configuration sidebar
    When I fill "System Config Form" with:
      | Enable WYSIWYG editor | false |
    And save form
    Then I should see "Configuration saved" flash message

  Scenario: Go to Customer User Configuration and check Cookies Banner Fallback Localization
    Given I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Cookies Banner Text" field
    When I click on "Cookies Banner Text Fallbacks"
    And I should see an "Cookies Banner Default Value" element
    And I should not see an "Cookies Banner Default Value Disabled" element
    And I should see an "Cookies Banner Name English Disabled" element
    Then I should not see an "Cookies Banner Name English" element
    When I click on "Cookies Banner Name English Use Default"
    And I should not see an "Cookies Banner Default Value" element
    And I should see an "Cookies Banner Default Value Disabled" element
    And I should not see an "Cookies Banner Name English Disabled" element
    Then I should see an "Cookies Banner Name English" element

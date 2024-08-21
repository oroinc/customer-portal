@regression
@ticket-BB-24264

Feature: Legacy configuration options
  Some system configuration options remain in the system configuration in order to support old storefront themes.
  These options are either not used in the new themes, or are configured elsewhere (most likely in the theme configurator).
  To help admins better understand what is going on, we explain why such options are preserved,
  and (if applicable) what other configuration should be used instead.

  Scenario: Check legacy configuration options in System Configuration
    Given I login as administrator
    And I go to System / Configuration

    When I follow "Commerce/Design/Theme" on configuration sidebar
    Then I should see "Menu Templates"
    And I click on warning tooltip for "User Menu" config field
    Then I should see "This configuration applies to OroCommerce version 5.1 and below and is retained in the current version only for backward compatibility with legacy storefront themes."

@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroCustomerBundle:AddressTypeTranslation.yml
Feature: Checking the address types at different locales
  In order to check the address type
  As a Buyer
  I should have the opportunity to see different names of address types at different locations

  Scenario: Feature Background
    Given I enable the existing localizations
    And I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account"
    And I click "Address Book"

  Scenario: Check address type on Zulu localization
    Given I click "New Address"
    When I click on "Localization dropdown"
    And I click "Zulu"
    Then "OroForm" must contains values:
      | Billing Zulu          | false |
      | Shipping Zulu         | false |
      | Default Billing Zulu  | false |
      | Default Shipping Zulu | false |

  Scenario: Check address type on English localization
    Given I reload the page
    When I click on "Localization dropdown"
    And I click "English"
    Then "OroForm" must contains values:
      | Billing          | false |
      | Shipping         | false |
      | Default Billing  | false |
      | Default Shipping | false |

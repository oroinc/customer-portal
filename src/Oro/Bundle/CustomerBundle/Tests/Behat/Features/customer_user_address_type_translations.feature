@regression
@ticket-BAP-17733
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml

Feature: Customer User Address Type Translations
  In order to fully support localization
  As a Buyer
  I want to be able to see translated value for address type

  Scenario: Create different window sessions
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Feature Background
    Given I proceed as the Admin
    And I login as administrator
    And I go to System / Configuration
    And I follow "System Configuration/General Setup/Language Settings" on configuration sidebar
    And I fill form with:
      | Supported Languages | [English, Zulu] |
      | Use Default         | false           |
      | Default Language    | Zulu            |
    And I submit form
    Then I should see "Configuration saved" flash message

    When I follow "System Configuration/General Setup/Localization" on configuration sidebar
    When fill form with:
      | Enabled Localizations | [English, Zulu] |
      | Default Localization  | English         |
    And I submit form
    Then I should see "Configuration saved" flash message

    When go to System/Localization/Translations
    And filter Translated Value as is empty
    And filter Key as contains "address_type.billing"
    And I edit "address_type.billing" Translated Value as "Billing - Zulu"
    Then I should see following records in grid:
      | Billing - Zulu |

    When filter Key as contains "address_type.shipping"
    And I edit "address_type.shipping" Translated Value as "Shipping - Zulu"
    Then I should see following records in grid:
      | Shipping - Zulu |

    When I click "Update Cache"
    Then I should see "Translation Cache has been updated" flash message

  Scenario: Check translations for address types
    Given I proceed as the User
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    And I click "Address Book"
    Then should see following "Customer Users Address Book Grid" grid:
      | Customer Address     | Zip/Postal Code | Country       | Type                              |
      | 801 Scenic Hwy       | 33844           | United States | Default Shipping, Default Billing |
      | 23400 Caldwell Road  | 14608           | United States | Default Billing                   |
      | 34500 Capitol Avenue | 47981           | United States |                                   |
    And I wait for action
    And I click "Localization Switcher"
    When I select "Zulu" localization
    Then should see following "Customer Users Address Book Grid" grid:
      | Customer Address     | Zip/Postal Code | Country       | Type                                            |
      | 801 Scenic Hwy       | 33844           | United States | Default Shipping - Zulu, Default Billing - Zulu |
      | 23400 Caldwell Road  | 14608           | United States | Default Billing - Zulu                          |
      | 34500 Capitol Avenue | 47981           | United States |                                                 |

@ticket-BB-23966
@fixture-OroCustomerBundle:order_line_items.yml
Feature: Create customer report and check grid translations
  In order to manage reports
  As administrator
  I need to be able to create report and switch translation in backoffice

  Scenario: Add Japanese language and localization, set default localization to English (United States)
    Given I login as administrator
    And go to System / Localization/ Languages

    # Add Japanese language
    When click "Add Language"
    And fill "Language Form" with:
      | Language | Japanese (Japan) - ja_JP |
    And click "Add Language" in modal window
    Then I should see "Language has been added" flash message
    When I click Install "Japanese (Japan)" in grid
    Then I should see "UiDialog" with elements:
      | Title    | Install "Japanese (Japan)" language |
      | okButton | Install                             |
    When I click "Install" in modal window
    Then I should see "Language has been installed" flash message
    When I click enable "Japanese (Japan)" in grid
    Then I should see "Language has been enabled" flash message

    # Add Japanese localization
    When go to System / Localization/ Localizations
    And click "Create Localization"
    And fill "Localization Form" with:
      | Name                | Japanese         |
      | Title               | Japanese         |
      | Language            | Japanese (Japan) |
      | Formatting          | Japanese (Japan) |
    And I save and close form
    Then I should see "Localization has been saved" flash message

    # Set default localization to English (United States)
    When I go to System / Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And fill form with:
      | Enabled Localizations | [English (United States), Japanese] |
      | Default Localization  | English (United States)             |
    And submit form
    Then I should see "Configuration saved" flash message

  Scenario: Create Report with State Name and Country Name translatable columns
    Given I set alias "LocalizationSettings" for the current browser tab
    And I open a new browser tab and set "Reports" alias for it
    When I go to Reports & Segments/ Manage Custom Reports
    And I click "Create Report"
    Then I fill "Report Form" with:
      | Name        | OrderLineItem ShippingAddress Country And State Report |
      | Entity      | Order Line Item                                        |
      | Report Type | Table                                                  |
    And I add the following columns:
      | Order->Shipping Address->State Name   |
      | Order->Shipping Address->Country Name |
    When I save and close form
    Then I should see "Report saved" flash message
    And I should see following grid:
      | State Name | Country Name  |
      | California | United States |
      | Florida    | United States |

  Scenario: Switch default language in backoffice
    When I switch to the browser tab "LocalizationSettings"
    And fill form with:
      | Default Localization | Japanese |
    And submit form
    Then I should see "Configuration saved" flash message

  Scenario: Check localized values in order line item report grid
    When I switch to the browser tab "Reports"
    And reload the page
    Then I should see following grid:
      | State Name     | Country Name |
      | カリフォルニア州 | アメリカ合衆国 |
      | フロリダ州      | アメリカ合衆国 |

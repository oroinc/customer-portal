@ticket-BB-5982
@fixture-OroCustomerBundle:CustomerUserFixture.yml
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml

Feature: Create customer user address
  In order to to manage addresses for Customer User
  As an administrator
  I want to create new Address

  Scenario: Feature Background
    Given I login as administrator
    And I go to System / Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And I fill form with:
      | Enabled Localizations | [English, Zulu_Loc] |
      | Default Localization  | Zulu_Loc            |
    And I submit form

  Scenario: Create customer user address and see validation errors
    Given I go to Customers / Customer Users
    And I click on first customer in grid
    Then I should not see "Test billing address"

    When I click "New Address"
    And I fill form with:
      | Label           | Test billing address |
      | First name      | Test first name      |
      | Last name       |                      |
      | Street          | Test street          |
      | City            | Test city            |
      | Country         | GermanyZulu          |
      | State           | BerlinZulu           |
      | Zip/Postal Code | 111111               |
    And I click "Save"
    Then I should see "First Name and Last Name or Organization should not be blank."
    Then I should see "Last Name and First Name or Organization should not be blank."
    Then I should see "Organization or First Name and Last Name should not be blank."
    When I fill form with:
      | Organization | Test Organization |
    And I click "Save"
    Then I should see "Address saved" flash message
    And customer user has 1 address
    And Test billing address address must be primary

  Scenario: Add customer user address via edit page
    Given I go to Customers / Customer Users
    When I click Edit first customer in grid
    And I click "Add"
    And I fill "Customer User Form" with:
      | Second Primary      | true              |
      | Second Street       | Test street       |
      | Second City         | Test city         |
      | Second Postal Code  | 111111            |
      | Second Organization | test organization |
      | Second Country      | United StatesZulu |
      | Second State        | FloridaZulu       |
    And I save and close form
    Then I should see "Customer User has been saved" flash message
    And customer user has 2 addresses
    And TEST CITY FL US 111111 address must be primary

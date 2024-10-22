@ticket-BB-24055
@fixture-OroCustomerBundle:CustomerUserBuyerFixture.yml

Feature: Check customer user address create page access
  In order to be able correctly work with customer user permissions
  As an administrator
  I set role buyer to customer user address with different type of permission and creation page should available or
  forbidden for different customer user

  Scenario: Feature background
    Given sessions active:
      | Admin  | system_session |
      | Buyer  | first_session  |
    And I proceed as the Admin

  Scenario: Set permission to create address for current customer user only
    Given I login as administrator
    And I go to Customers/Customer User Roles
    When click Edit Buyer in grid
    And select following permissions:
      | Customer Address      | View:Сorporate (All Levels) | Create:None  | Edit:None | Delete:None | Assign:None |
      | Customer User Address | View:None      | Create:User  | Edit:None | Delete:None | Assign:None |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Check new address button is present and address created for current customer user
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And click "Address Book"
    Then I should see "New Address"
    When I follow "Account"
    And I click "My Profile"
    Then I should see "New Address"
    When click "New Address"
    And I should not see "Nancy Sallee"
    And I fill form with:
      | User            | AmandaMu Cole      |
      | Country         | Germany            |
      | Street          | Amanda New street  |
      | City            | New city           |
      | Zip/Postal Code | 111111             |
      | State           | Berlin             |
    And I save form
    Then I should see "Customer User Address has been saved" flash message
    And the url should match "/customer/user/address"

  Scenario: Set permission to create address for customer user department
    Given I proceed as the Admin
    And I go to Customers/Customer User Roles
    When click Edit Buyer in grid
    And select following permissions:
      | Customer User Address | View:None | Create:Department (Same Level) | Edit:None | Delete:None | Assign:None |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Check new address button is present and address created for department from address book
    Given I proceed as the Buyer
    And I follow "Account"
    And I click "My Profile"
    Then I should see "New Address"
    When I follow "Account"
    And click "Address Book"
    Then I should see "New Address"
    When click "New Address"
    And I should see "Nancy Sallee"
    And I should not see "John Doe"
    And I fill form with:
      | User            | Nancy Sallee       |
      | Country         | Germany            |
      | Street          | Nancy New street   |
      | City            | New city           |
      | Zip/Postal Code | 111111             |
      | State           | Berlin             |
    And I save form
    Then I should see "Customer User Address has been saved" flash message
    And the url should match "/customer/user/address"

  Scenario: Set permission to view department users list
    Given I proceed as the Admin
    And I go to Customers/Customer User Roles
    When click Edit Buyer in grid
    And select following permissions:
      | Customer User | View:Department (Same Level)  | Create:None| Edit:None | Delete:None | Assign:None |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Check new address button is present and address created for department from user list
    Given I proceed as the Buyer
    And I follow "Account"
    And I click "My Profile"
    Then I should see "New Address"
    When I follow "Account"
    And click "Address Book"
    When I follow "Account"
    And click "Users"
    Then I should see following records in grid:
      | Amanda |
      | Nancy  |
    When click view "NancyJSallee@example.org" in grid
    Then I should see "New Address"
    When click "New Address"
    And I should see that option "Nancy Sallee" is selected in "Frontend Customer User Owner" select
    And I fill form with:
      | User            | Nancy Sallee               |
      | Country         | Germany                    |
      | Street          | Nancy second New street    |
      | City            | New city                   |
      | Zip/Postal Code | 111111                     |
      | State           | Berlin                     |
    And I save form
    Then I should see "Customer User Address has been saved" flash message
    And the url should match "/customer/user/view/"

  Scenario: Set permission to create address for corporate customer user
    Given I proceed as the Admin
    And I go to Customers/Customer User Roles
    When click Edit Buyer in grid
    And select following permissions:
      | Customer User         | View:None | Create:None                   | Edit:None | Delete:None | Assign:None |
      | Customer User Address | View:None | Create:Сorporate (All Levels) | Edit:None | Delete:None | Assign:None |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Check new address button is present and address created for corporate from address book
    Given I proceed as the Buyer
    And I follow "Account"
    And I click "My Profile"
    Then I should see "New Address"
    When I follow "Account"
    And click "Address Book"
    Then I should see "New Address"
    When click "New Address"
    And I should see "Nancy Sallee"
    And I should see "John Doe"
    And I fill form with:
      | User            | John Doe             |
      | Country         | Germany              |
      | Street          | John Doe New street  |
      | City            | New city             |
      | Zip/Postal Code | 111111               |
      | State           | Berlin               |
    And I save form
    Then I should see "Customer User Address has been saved" flash message
    And the url should match "/customer/user/address"

  Scenario: Set permission to view corporate users list
    Given I proceed as the Admin
    And I go to Customers/Customer User Roles
    When click Edit Buyer in grid
    And select following permissions:
      | Customer User | View:Сorporate (All Levels)| Create:None| Edit:None | Delete:None | Assign:None |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Check new address button is present and address created for corporate from user list
    Given I proceed as the Buyer
    And I follow "Account"
    And I click "My Profile"
    Then I should see "New Address"
    When I follow "Account"
    And click "Users"
    Then I should see following records in grid:
      | Amanda |
      | Nancy  |
      | John   |
    When click view "john@example.org" in grid
    Then I should see "New Address"
    When click "New Address"
    And I should see that option "John Doe" is selected in "Frontend Customer User Owner" select
    And I fill form with:
      | User            | John Doe                   |
      | Country         | Germany                    |
      | Street          | John second New street    |
      | City            | New city                   |
      | Zip/Postal Code | 111111                     |
      | State           | Berlin                     |
    And I save form
    Then I should see "Customer User Address has been saved" flash message
    And the url should match "/customer/user/view/"

  Scenario: Set corporate role for customer user address view and edit
    Given I proceed as the Admin
    And I go to Customers/Customer User Roles
    When click Edit Buyer in grid
    And select following permissions:
      | Customer Address      | View:Сorporate (All Levels)| Create:None       | Edit:None                   | Delete:None | Assign:None |
      | Customer User         | View:Сorporate (All Levels)| Create:None       | Edit:Сorporate (All Levels)| Delete:None | Assign:None |
      | Customer User Address | View:Сorporate (All Levels)| Create:User (Own) | Edit:Сorporate (All Levels)| Delete:None | Assign:None |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Edit address for corporate customer user
    Given I proceed as the Buyer
    And I follow "Account"
    And click "Address Book"
    And I should see following actions for Amanda New street in grid:
      | Map  |
      | Edit |
    And I should see following actions for Nancy New street in grid:
      | Map     |
      | Edit    |
    And I should see following actions for John Doe New street in grid:
      | Map     |
      | Edit    |
    When click edit "John Doe New street" in grid
    And I fill form with:
      | Street | John Doe Updated street |
    And I save form
    Then I should see "Customer User Address has been saved" flash message
    And the url should match "/customer/user/view/"

  Scenario: Set department role for customer user address edit
    Given I proceed as the Admin
    And I go to Customers/Customer User Roles
    When click Edit Buyer in grid
    And select following permissions:
      | Customer Address      | View:Сorporate (All Levels)| Create:None       | Edit:None                   | Delete:None | Assign:None |
      | Customer User         | View:Сorporate (All Levels)| Create:None       | Edit:Сorporate (All Levels)| Delete:None | Assign:None |
      | Customer User Address | View:Сorporate (All Levels)| Create:User (Own) | Edit:Department (Same Level) | Delete:None | Assign:None |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Edit address for department customer user
    Given I proceed as the Buyer
    And I follow "Account"
    And click "Address Book"
    And I should see following actions for Amanda New street in grid:
      | Map  |
      | Edit |
    And I should see following actions for Nancy New street in grid:
      | Map     |
      | Edit    |
    And I should see following actions for John Doe New street in grid:
      | Map     |
    When click edit "Nancy New street" in grid
    And I fill form with:
      | Street | Nancy Updated street |
    And I save form
    Then I should see "Customer User Address has been saved" flash message
    And the url should match "/customer/user/view/"

  Scenario: Set user role for customer user address edit
    Given I proceed as the Admin
    And I go to Customers/Customer User Roles
    When click Edit Buyer in grid
    And select following permissions:
      | Customer Address      | View:Сorporate (All Levels)| Create:None       | Edit:None                   | Delete:None | Assign:None |
      | Customer User         | View:Сorporate (All Levels)| Create:None       | Edit:Сorporate (All Levels)| Delete:None | Assign:None |
      | Customer User Address | View:Сorporate (All Levels)| Create:User (Own) | Edit:User (Own)             | Delete:None | Assign:None |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Edit customer user address
    Given I proceed as the Buyer
    And I follow "Account"
    And click "Address Book"
    And I should see following actions for Amanda New street in grid:
      | Map  |
      | Edit |
    And I should see following actions for Nancy New street in grid:
      | Map     |
    And I should see following actions for John Doe New street in grid:
      | Map     |
    When click edit "Amanda New street" in grid
    And I fill form with:
      | Street | Amanda Updated street |
    And I save form
    Then I should see "Customer User Address has been saved" flash message
    And the url should match "/customer/user/address"
@ticket-BB-14685
@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Customer User Case Insensitive Email Addresses
  In order to avoid possible user mistakes
  As an administrator
  I want to disable customer user registration with emails if the same email with a different capitalization already belongs to some customer user
  As a user
  I must be able to login with email in another case if "Case Insensitive Email Addresses" is enabled
  I must not be able to login with email in another case if "Case Insensitive Email Addresses" is disabled

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Check unsuccessful login with email in another case
    Given I proceed as the Buyer
    When I signed in as amandarcole@example.org with password AmandaRCole@example.org on the store frontend
    Then I should see "Your login was unsuccessful. Please check your e-mail address and password before trying again. If you have forgotten your password, follow \"Forgot Your password?\" link."

  Scenario: Create customer user from the Admin panel with disabled "Case Insensitive Email Addresses"
    Given I proceed as the Admin
    And I login as administrator
    And I go to Customers/ Customer Users
    And I click "Create Customer User"
    And I fill form with:
      | First Name    | Branda                     |
      | Last Name     | Sanborn                    |
      | Email Address | BrandaJSanborn@example.org |
    And I focus on "Birthday" field
    And I click "Today"
    And I fill form with:
      | Password                   | BrandaJSanborn1@example.org |
      | Confirm Password           | BrandaJSanborn1@example.org |
      | Customer                   | first customer              |
      | Administrator (Predefined) | true                        |
    When I save and close form
    Then should see "Customer User has been saved" flash message

  Scenario: Create second customer user from the Admin panel with disabled "Case Insensitive Email Addresses"
    Given I go to Customers/ Customer Users
    And I click "Create Customer User"
    And I fill form with:
      | First Name    | Branda                     |
      | Last Name     | Sanborn                    |
      | Email Address | brandajsanborn@example.org |
    And I focus on "Birthday" field
    And I click "Today"
    And I fill form with:
      | Password                   | BrandaJSanborn1@example.org |
      | Confirm Password           | BrandaJSanborn1@example.org |
      | Customer                   | first customer              |
      | Administrator (Predefined) | true                        |
    When I save and close form
    Then should see "Customer User has been saved" flash message
    And I go to Customers/ Customer Users
    And I click delete "brandajsanborn@example.org" in grid
    And I click "Yes, Delete"
    And I should see "Customer User deleted" flash message

  Scenario: Enable "Case Insensitive Email Addresses" configuration option
    Given I go to System/Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And I check "Case-Insensitive Email Addresses"
    When I save form
    Then I should see "Configuration saved" flash message

  Scenario: Check successful login with lowercase email when "Case Insensitive Email Addresses" is enabled
    Given I proceed as the Buyer
    When I signed in as amandarcole@example.org with password AmandaRCole@example.org on the store frontend
    Then I should see text matching "Signed in as: Amanda Cole"

  Scenario: Check successful login with original email when "Case Insensitive Email Addresses" is enabled
    Given I signed in as AmandaRCole@example.org with password AmandaRCole@example.org on the store frontend
    Then I should see text matching "Signed in as: Amanda Cole"
    And click "Sign Out"

  Scenario: Check registration is not allowed when same email in lowercase exists when "Case Insensitive Email Addresses" is enabled
    Given I click "Register"
    And Page title equals to "Registration"
    And I should see a "Registration Form" element
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Ruth                     |
      | Last Name        | Maxwell                  |
      | Email Address    | amandarcole@example.org  |
      | Password         | amandarcolE@example.org1 |
      | Confirm Password | amandarcolE@example.org1 |
    When I click "Create An Account"
    Then I should see that "Customer User Registration Error Container" contains "This email is already used."

  Scenario: Check that you cant enable "Case Insensitive Email Addresses" options while there are customer users with same lowercase emails exist
    Given I proceed as the Admin
    When I go to System/Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And I uncheck "Case-Insensitive Email Addresses"
    And I save form
    Then I should see "Configuration saved" flash message
    When I proceed as the Buyer
    And I am on the homepage
    And I click "Register"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Ruth                     |
      | Last Name        | Maxwell                  |
      | Email Address    | amandarcole@example.org  |
      | Password         | amandarcolE@example.org1 |
      | Confirm Password | amandarcolE@example.org1 |
    And I click "Create An Account"
    Then I should see "Please check your email to complete registration"
    When I proceed as the Admin
    And I go to System/Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And I check "Case-Insensitive Email Addresses"
    And I save form
    Then I should see "there are existing customer users who have identical lowercase emails"
    When I click "Click here"
    Then I should be on Customer User Index page
    And I should see following grid:
      | Customer       | First Name | Last Name | Email Address           | Enabled | Confirmed | Guest |
      | first customer | Amanda     | Cole      | AmandaRCole@example.org | Yes     | Yes       | No    |
      | OroCommerce    | Ruth       | Maxwell   | amandarcole@example.org | Yes     | No        | No    |

  Scenario: Create customer user from the Admin panel with enabled "Case Insensitive Email Addresses"
    Given I go to Customers/ Customer Users
    And I click "Create Customer User"
    And I fill form with:
      | First Name    | Ruth                    |
      | Last Name     | Maxwell                 |
      | Email Address | AmandaRCole@example.org |
    And I focus on "Birthday" field
    And I click "Today"
    And I fill form with:
      | Password                   | amandarcolE@example.org1 |
      | Confirm Password           | amandarcolE@example.org1 |
      | Customer                   | OroCommerce              |
      | Administrator (Predefined) | true                     |
    When I save and close form
    Then I should see validation errors:
      | Email Address | This email is already used. |

  Scenario: Create second customer user from the Admin panel with enabled "Case Insensitive Email Addresses"
    Given I go to Customers/ Customer Users
    And I click "Create Customer User"
    And I fill form with:
      | First Name    | Marlene                     |
      | Last Name     | Bradley                     |
      | Email Address | MarleneSBradley@example.com |
    And I focus on "Birthday" field
    And I click "Today"
    And I fill form with:
      | Password                   | MarleneSBradley@example.com1 |
      | Confirm Password           | MarleneSBradley@example.com1 |
      | Customer                   | OroCommerce                  |
      | Administrator (Predefined) | true                         |
    When I save and close form
    Then should see "Customer User has been saved" flash message

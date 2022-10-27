@regression
@ticket-BB-14855
@fixture-OroCustomerBundle:EmailForCustomerUserRegistration.yml

Feature: Email templates used for customer user registration
  In order to be sure that correct email template is used for customer user registration
  As a User
  I want to register customer user from different ways using different send email configurations

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |
    And I proceed as the Admin
    And I login as administrator
    And go to System/ Configuration
    And follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Confirmation Required" field
    And I uncheck "Confirmation Required"
    And click "Save settings"

  Scenario: Check email send to the customer user when register by itself
    Given I proceed as the User
    And I am on the homepage
    And click "Sign In"
    And click "Create An Account"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | FrontU                   |
      | Last Name        | LastN                    |
      | Email Address    | FrontULastN1@example.org |
      | Password         | FrontULastN1@example.org |
      | Confirm Password | FrontULastN1@example.org |
    When I click "Create An Account"
    Then email with Subject "Welcome: FrontU LastN" containing the following was sent:
      | Body | Dear FrontU LastN,                                                                             |
      | Body | Welcome to ORO.                                                                                |
      | Body | To log into your new account, just click 'Sign In' at the top of                               |
      | Body | Email: FrontULastN1@example.org                                                                |
      | Body | Password: This is the password you created. If you ever forget your password, recover it here. |
      | Body | If you have any questions, please contact us.                                                  |
      | Body | Thank you,                                                                                     |
      | Body | ORO Team                                                                                       |

  Scenario: Check email send to the customer user when register by frontstore administrator ("Send Welcome Email" option disabled)
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    And follow "Account"
    And click "Users"
    And click "Create User"
    When fill form with:
      | Email Address      | AmandaChild1@test.com |
      | First Name         | Amanda                |
      | Last Name          | Child                 |
      | Password           | AmandaChild1@test.com |
      | Confirm Password   | AmandaChild1@test.com |
      | Buyer (Predefined) | true                  |
      | Send Welcome Email | false                 |
    And click "Save"
    Then should see "Customer User has been saved" flash message
    And email with Subject "Welcome: Amanda Child" was not sent

  Scenario: Check email send to the customer user when register by frontstore administrator ("Send Welcome Email" option enabled)
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    And follow "Account"
    And click "Users"
    And click "Create User"
    When fill form with:
      | Email Address      | BrandaChild1@test.com |
      | First Name         | Branda                |
      | Last Name          | Child                 |
      | Password           | BrandaChild1@test.com |
      | Confirm Password   | BrandaChild1@test.com |
      | Buyer (Predefined) | true                  |
      | Send Welcome Email | true                  |
    And click "Save"
    Then should see "Customer User has been saved" flash message
    And email with Subject "Welcome: Branda Child" containing the following was sent:
      | Body | Dear Branda Child,                                                                             |
      | Body | Welcome to ORO.                                                                                |
      | Body | To log into your new account, just click 'Sign In' at the top of                               |
      | Body | Email: BrandaChild1@test.com                                                                   |
      | Body | Password: This is the password you created. If you ever forget your password, recover it here. |
      | Body | If you have any questions, please contact us.                                                  |
      | Body | Thank you,                                                                                     |
      | Body | ORO Team                                                                                       |

  Scenario: Check email send to the customer user when register by admin console administrator ("Send Welcome Email" option enabled)
    Given I proceed as the Admin
    And go to Customers/ Customer Users
    And click "Create Customer User"
    And fill form with:
      | First Name    | Branda                      |
      | Last Name     | Sanborn                     |
      | Email Address | BrandaJSanborn1@example.org |
    And I focus on "Birthday" field
    And click "Today"
    And fill form with:
      | Password           | BrandaJSanborn1@example.org |
      | Confirm Password   | BrandaJSanborn1@example.org |
      | Customer           | Company B                   |
      | Send Welcome Email | true                        |
    And fill "Customer User Addresses Form" with:
      | Primary                    | true          |
      | First Name Add             | Branda        |
      | Last Name Add              | Sanborn       |
      | Organization               | Smoke Org     |
      | Country                    | United States |
      | Street                     | Market St. 12 |
      | City                       | San Francisco |
      | State                      | California    |
      | Zip/Postal Code            | 90001         |
      | Billing                    | true          |
      | Shipping                   | true          |
      | Default Billing            | true          |
      | Default Shipping           | true          |
      | Administrator (Predefined) | true          |
    And save and close form
    Then should see "Customer User has been saved" flash message
    And email with Subject "Welcome: Branda Sanborn" containing the following was sent:
      | Body | Dear Branda Sanborn,                                                                           |
      | Body | Welcome to ORO.                                                                                |
      | Body | Please follow the link below to create a password for your new account.                        |
      | Body | /customer/user/reset?token=                                                                    |
      | Body | To log into your new account, just click 'Sign In' at the top of                               |
      | Body | Email: BrandaJSanborn1@example.org                                                             |
      | Body | Password: This is the password you created. If you ever forget your password, recover it here. |
      | Body | If you have any questions, please contact us.                                                  |
      | Body | Thank you,                                                                                     |
      | Body | ORO Team                                                                                       |

  Scenario: Check email send to the customer user when register by admin console administrator ("Send Welcome Email" option disabled)
    Given I proceed as the Admin
    And go to Customers/ Customer Users
    And click "Create Customer User"
    And fill form with:
      | First Name    | LonnieV                      |
      | Last Name     | Townsend                     |
      | Email Address | LonnieVTownsend1@example.org |
    And I focus on "Birthday" field
    And click "Today"
    And fill form with:
      | Password           | LonnieVTownsend1@example.org |
      | Confirm Password   | LonnieVTownsend1@example.org |
      | Customer           | Company B                    |
      | Send Welcome Email | false                        |
    And fill "Customer User Addresses Form" with:
      | Primary                    | true          |
      | First Name Add             | LonnieV       |
      | Last Name Add              | Townsend      |
      | Organization               | Smoke Org     |
      | Country                    | United States |
      | Street                     | Market St. 15 |
      | City                       | San Francisco |
      | State                      | California    |
      | Zip/Postal Code            | 90001         |
      | Billing                    | true          |
      | Shipping                   | true          |
      | Default Billing            | true          |
      | Default Shipping           | true          |
      | Administrator (Predefined) | true          |
    And save and close form
    Then should see "Customer User has been saved" flash message
    And email with Subject "Welcome: LonnieV Townsend" was not sent

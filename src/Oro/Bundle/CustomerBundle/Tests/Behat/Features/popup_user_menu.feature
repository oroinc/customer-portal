@regression
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml

Feature: Popup user menu
  In order to provide optimized user experience based on the customer user menu configuration
  As an Administrator
  I want to choose the template for the customer user menu

  # Description
  # Based on the setting selected in system configuration use one of the two tempaltes to display customer user menu:
  # all menu items are displayed on the page
  # menu items are displayed in a drop-down when user clicks on the user name
  # Use different welcome messages in the templates:
  # default template (all at once) - "Signed in as: John Doe"
  # when only name is shown - "Welcome, John Doe"
  #
  # Configuration
  # Add new fieldset "Menu Templates" to the System -> Configuration -> COMMERCE -> Design -> Theme page:
  # new field "User Menu" - drop-down, default value - "Show all items at once", hint - none, values:
  # Show all items at once
  # Show subitems in a popup
  # These settings should be configurable on the global, organization and website levels.
  #
  # Acceptance Criteria
  # Show how an administrator can change the template used for customer user menu
  # Show how the "popup" template works

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Enable "Popup user menu"
    Given I proceed as the Admin
    And I login as administrator
    And go to System/ Configuration
    And I follow "Commerce/Design/Theme" on configuration sidebar
    And fill "Menu Templates Form" with:
      | Use Default | false                    |
      | User Menu   | Show subitems in a popup |
    When save form
    Then I should see "Configuration saved" flash message

  Scenario: Enable "Roles" page view for Buyer role
    And go to Customers/ Customer User Roles
    And I click Edit Buyer in grid
    And select following permissions:
      | Customer User Role | View:Department | Create:None | Edit:None |
    And I save form
    Then I should see "Customer User Role has been saved"

  Scenario: "Popup user menu" is present on front store
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    When I click "Popup User Menu trigger"
    And I should see an "Popup User Menu" element
    And I should see an "Popup User Menu Link Users" element
    And I should see an "Popup User Menu Link Roles" element
    When I click on "Popup User Menu Link Roles"

  Scenario: Deny "Roles" page view for Buyer role
    Given I proceed as the Admin
    And select following permissions:
      | Customer User Role | View:None | Create:None | Edit:None |
    And I save form
    Then I should see "Customer User Role has been saved"

  Scenario: Check that "Popup user menu" is present on front store and Roles link is not visible
    Given I proceed as the User
    When I reload the page
    When I click "Popup User Menu trigger"
    And I should see an "Popup User Menu" element
    And I should see an "Popup User Menu Link Users" element
    But I should not see an "Popup User Menu Link Roles" element

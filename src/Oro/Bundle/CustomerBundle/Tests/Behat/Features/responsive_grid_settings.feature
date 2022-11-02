@regression
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml

Feature: Responsive Grid Settings
  In order to provide possibility enable/disable responsive view for datagrid

  # Configuration
  # Add new fieldset "Grid Settings" to the System -> Configuration -> COMMERCE -> Design -> Theme page:
  # new field "Responsive Grids" - checkbox,
  # default value - true (enabled)

  Scenario: Create different window session
    Given sessions active:
      | User  |first_session  |
      | Admin |second_session |

  Scenario: Check default value (Enable) for datagrid on front store
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Requests For Quote"
    Then I should see an "Customer User Responsive Datagrid" element

  Scenario: Check value (Disable) for datagrid on front store
    Given I proceed as the Admin
    And I login as administrator
    And go to System / Configuration
    And I follow "Commerce/Design/Theme" on configuration sidebar
    And uncheck "Use default" for "Responsive grids" field
    And I uncheck "Responsive grids"
    And click "Save settings"
    When I proceed as the User
    And I reload the page
    Then I should not see an "Customer User Responsive Datagrid" element

@ticket-BAP-21510
@fixture-OroCustomerBundle:CustomerUserAmandaRCole.yml

Feature: Check system configuration from mobile
  As a administrator
  I need to check the system configuration from a mobile device

  Scenario: Feature Background
    Given sessions active:
      | admin_desktop | first_session  |
      | admin_mobile  | mobile_session |

  Scenario: Check visibility for the mobile version Customers and Customer Group Configuration
    Given I proceed as the admin_mobile
    And I login as administrator
    When I click "Mobile Menu Toggler"
    Then I should not see "System"

    When I go to "/admin/config/customer/1"
    Then I should see "System configuration is not available in mobile version. Please open the page on the desktop."

    When I go to "/admin/config/customerGroup/1"
    Then I should see "System configuration is not available in mobile version. Please open the page on the desktop."

  Scenario: Check visibility for the desktop version Customers Configuration
    Given I proceed as the admin_desktop
    And I login as administrator
    When I go to Customers/ Customers
    And I click Configuration AmandaRCole in grid
    Then I should not see "System configuration is not available in mobile version. Please open the page on the desktop."
    And I should see "Routing"
    And I should see "Main Navigation Menu"

    And I follow "Commerce/Product/Customer Settings" on configuration sidebar
    And I should see "Product Data Export"
    And I should see "Enable Product Grid Export"

  Scenario: Check visibility for the desktop version Customer Group Configuration
    When I go to Customers/ Customer Group
    And I click Configuration AmandaRCole in grid
    Then I should not see "System configuration is not available in mobile version. Please open the page on the desktop."
    And I should see "Routing"
    And I should see "Main Navigation Menu"

    And I follow "Commerce/Product/Customer Settings" on configuration sidebar
    And I should see "Product Data Export"
    And I should see "Enable Product Grid Export"

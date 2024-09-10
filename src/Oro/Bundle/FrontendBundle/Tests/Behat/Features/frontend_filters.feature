@fixture-OroFrontendBundle:frontend_all_grid_view_label.yml

Feature: Frontend filters
  As a User
  I want to be sure that filters hints is collapsed
  Check multiselect filters hint chips

  Scenario: Check many filters hints
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I am on homepage
    Then I click "Account Dropdown"
    And I click "Order History"
    And I show filter "Total" in "PastOrdersGrid" frontend grid
    And I show filter "Subtotal" in "PastOrdersGrid" frontend grid
    And I show filter "Currency" in "PastOrdersGrid" frontend grid
    And I check "Open,Cancelled,Closed" in Order Status filter in "PastOrdersGrid"
    Then I should see "Order Status 3" in the "PastOrdersGrid" element
    And I check "EUR" in Currency filter in "PastOrdersGrid"
    Then I should see "Currency 1" in the "PastOrdersGrid" element
    And I check "Pending payment" in Payment Status filter in "PastOrdersGrid"
    Then I should see "Payment Status 1" in the "PastOrdersGrid" element
    And I filter Total as equals "1000" in "PastOrdersGrid" grid strictly
    And I filter Subtotal as equals "900" in "PastOrdersGrid" grid strictly
    Then I should see "Total 1" in the "PastOrdersGrid" element
    Then I should see "Subtotal 1" in the "PastOrdersGrid" element
    And I scroll to top
    And I filter "Created At" as between "today-2" and "today-1" in "PastOrdersGrid" grid strictly
    Then I should see "Created At 1" in the "PastOrdersGrid" element
    Then I should see filter hints in "PastOrdersGrid" frontend grid:
      | Cancelled                       |
      | Closed                          |
      | Subtotal: equals 900.00         |
      | Total: equals 1,000.00          |
      | Currency: EUR                   |
      | Payment Status: Pending payment |
    And I should see "+2" in the "PastOrdersGrid" element
    And I click on "Filter Hint Items Toggle" with title "+2" in element "PastOrdersGrid"
    Then should see filter hints in "PastOrdersGrid" frontend grid:
      | Open                                    |
      | Cancelled                               |
      | Closed                                  |
      | Total: equals 1,000.00                  |
      | Subtotal: equals 900.00                 |
      | Currency: EUR                           |
      | Payment Status: Pending payment         |
      | Created At: between today-2 and today-1 |
    Then I should see "Clear All Filters" in the "PastOrdersGrid" element
    And I click on "Filter Hint Items Toggle" with title "+2" in element "PastOrdersGrid"

  Scenario: Check filter hints responsive
    When I set window size to 992x1024
    And I should see "+5" in the "PastOrdersGrid" element
    Then should see filter hints in "PastOrdersGrid" frontend grid:
      | Subtotal: equals 900.00 |
      | Currency: EUR           |
      | Total: equals 1,000.00  |
    And I click on "Filter Hint Items Toggle" with title "+5" in element "PastOrdersGrid"
    Then should see filter hints in "PastOrdersGrid" frontend grid:
      | Open                                    |
      | Cancelled                               |
      | Closed                                  |
      | Total: equals 1,000.00                  |
      | Subtotal: equals 900.00                 |
      | Currency: EUR                           |
      | Payment Status: Pending payment         |
      | Created At: between today-2 and today-1 |
    And I click on "Filter Hint Items Toggle" with title "+5" in element "PastOrdersGrid"
    Then I set window size to 375x640
    And I reload the page
    And I should see "+7" in the "PastOrdersGrid" element
    Then should see filter hints in "PastOrdersGrid" frontend grid:
      | Closed |
    And I should see "Clear All" in the "PastOrdersGrid" element
    Then I click on "Filter Hint Items Toggle" with title "+7" in element "PastOrdersGrid"
    Then should see filter hints in "PastOrdersGrid" frontend grid:
      | Open                                    |
      | Cancelled                               |
      | Closed                                  |
      | Total: equals 1,000.00                  |
      | Subtotal: equals 900.00                 |
      | Currency: EUR                           |
      | Payment Status: Pending payment         |
      | Created At: between today-2 and today-1 |
    Then I set window size to 1440x900
    And I reload the page

  Scenario: Check reset particular filters by Clear filter Button
    When I click "Filter Toggle" in "PastOrdersGrid" element
    And I click "Order Status 3" in "PastOrdersGrid" element
    And I click on "Visible Filter Clean"
    Then should see filter hints in "PastOrdersGrid" frontend grid:
      | Total: equals 1,000.00                  |
      | Currency: EUR                           |
      | Payment Status: Pending payment         |
      | Created At: between today-2 and today-1 |
    Then I click "Total 1" in "PastOrdersGrid" element
    And I click on "Visible Filter Clean"
    Then should see filter hints in "PastOrdersGrid" frontend grid:
      | Currency: EUR                           |
      | Payment Status: Pending payment         |
      | Created At: between today-2 and today-1 |
    Then I click "Currency 1" in "PastOrdersGrid" element
    And I click on "Visible Filter Clean"
    Then should see filter hints in "PastOrdersGrid" frontend grid:
      | Payment Status: Pending payment         |
      | Created At: between today-2 and today-1 |
    Then I click "Payment Status 1" in "PastOrdersGrid" element
    And I click on "Visible Filter Clean"
    Then should see filter hints in "PastOrdersGrid" frontend grid:
      | Created At: between today-2 and today-1 |
    Then I click "Created At 1" in "PastOrdersGrid" element
    And I click on "Visible Filter Clean"

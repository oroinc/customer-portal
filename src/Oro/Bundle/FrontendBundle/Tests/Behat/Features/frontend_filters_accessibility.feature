@ticket-BB-18755
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml

Feature: Frontend filters accessibility
  As a User
  I want to be sure that filters are accessible via keyboard

  Scenario: Setup filters list for feature's test
    Given I login as AmandaRCole@example.org buyer
    And follow "Account"
    When click "Users"
    Then I should see "All Users"
    And I click "Grid Filters Button"
    When I hide filter "Email Address" in frontend grid
    And I hide filter "Confirmed" in frontend grid
    And I hide filter "Locked" in frontend grid
    And I hide filter "Password" in frontend grid
    Then I should see "First Name" filter in frontend grid
    And I should see "Last Name" filter in frontend grid
    And I should see "Enabled" filter in frontend grid

  Scenario: Open filter dropdown by pressing Enter key
    When I click "Filter By Last Name"
    And I press "Esc" key on "Opened Filter Dropdown" element
    Then I should see "Filter By Last Name" element focused
    When I press "Enter" key on "Filter By Last Name" element
    Then I should see focus within "Opened Filter Dropdown" element
    And I press "Esc" key on "Opened Filter Dropdown" element

  Scenario: Open filter dropdown by pressing Space key
    When I click "Filter By Last Name"
    And I press "Esc" key on "Opened Filter Dropdown" element
    When I press "Space" key on "Filter By Last Name" element
    Then I should see focus within "Opened Filter Dropdown" element
    And I press "Esc" key on "Opened Filter Dropdown" element

  Scenario: Open filter dropdown by pressing UpArrow key
    When I click "Filter By Last Name"
    And I press "Esc" key on "Opened Filter Dropdown" element
    When I press "ArrowUp" key on "Filter By Last Name" element
    Then I should see focus within "Opened Filter Dropdown" element
    And I press "Esc" key on "Opened Filter Dropdown" element

  Scenario: Open filter dropdown by pressing DownArrow key
    When I click "Filter By Last Name"
    And I press "Esc" key on "Opened Filter Dropdown" element
    When I press "ArrowDown" key on "Filter By Last Name" element
    Then I should see focus within "Opened Filter Dropdown" element
    And I press "Esc" key on "Opened Filter Dropdown" element

  Scenario: Looping filters navigation by pressing RightArrow key
    When I click "Filter By Last Name"
    And I press "Esc" key on "Opened Filter Dropdown" element
    When I press "ArrowRight" key on "Filter By Last Name" element
    Then I should see "Filter By Enabled" element focused
    When I press "ArrowRight" key on "Filter By Enabled" element
    Then I should see "Filter By First Name" element focused
    When I press "ArrowRight" key on "Filter By First Name" element
    Then I should see "Filter By Last Name" element focused

  Scenario: Looping filters navigation by pressing LeftArrow key
    When I click "Filter By Last Name"
    And I press "Esc" key on "Opened Filter Dropdown" element
    When I press "ArrowLeft" key on "Filter By Last Name" element
    Then I should see "Filter By First Name" element focused
    When I press "ArrowLeft" key on "Filter By First Name" element
    Then I should see "Filter By Enabled" element focused
    When I press "ArrowLeft" key on "Filter By Enabled" element
    Then I should see "Filter By Last Name" element focused

  Scenario: Toggle filters/state view by keyboard
    Given I check "Yes" in "Filter By Enabled" filter
    And I click "GridFiltersButton"
    Then I should not see an "GridFilters" element
    And I should see an "GridFiltersState" element
    When I press "Enter" key on "GridFiltersButton" element
    Then I should see an "GridFilters" element
    And I should not see an "GridFiltersState" element
    When I click "GridFiltersButton"
    And I click "GridFiltersState"
    Then I should see "Filter By Enabled" element focused

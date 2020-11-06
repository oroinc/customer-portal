@ticket-BB-9097
@automatically-ticket-tagged
@fixture-OroFrontendBundle:Products.yml
Feature: Sticky panel for main menu, product filters etc
  As a User
  I want to be sure that sticky panel is visible
  So I start check visibility on different screen resolutions

  Scenario: Check filters in sticky panel
    Given I login as AmandaRCole@example.org buyer
    And I click "Search Button"
    When I click "Copyright"
    Then I should see an "Active Sticky Panel" element
    And I should see an "Sticky Filters Dropdown" element
    And I click "Sticky Filters Dropdown"
    Then I should see an "Product Filter Into Sticky Panel" element
    And I should see an "Mass Actions Into Sticky Panel" element
    And I should see an "Pegination Into Sticky Panel" element
    And I should see an "Sorting Into Sticky Panel" element
    And I should see an "Catalog Switcher Into Sticky Panel" element

  Scenario: Check is sticky panel visible and has main menu content (mobile version)
    Given here is the "User" under "320_session"
    And I set window size to 320x640
    And I am on homepage
    Then I should not see an "Active Sticky Panel" element
    And I should see a "Main Menu Into Header" element
    When I click "Copyright"
    Then I should see an "Active Sticky Panel" element
    And I should see a "Main Menu Into Sticky Panel" element
    When I click "Header"
    Then I should not see an "Active Sticky Panel" element
    And I should see a "Main Menu Into Header" element

  Scenario: Check is sticky panel visible and has product filter
    Given here is the "User" under "320_session"
    And I set window size to 320x640
    And I am on "/product/?grid"
    Then I should not see an "Active Sticky Panel" element
    And I click "GridFiltersButton"
    And I should see a "Product Filter Into Page Content" element
    When I click "Copyright"
    Then I should see an "Active Sticky Panel" element
    And I should see a "Product Filter Into Sticky Panel" element
    When I click "Header"
    Then I should not see an "Active Sticky Panel" element
    And I should see a "Product Filter Into Page Content" element

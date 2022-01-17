@ticket-BB-19890
@fixture-OroFrontendBundle:Products.yml

Feature: Frontend dropdown autoclose
  As a User
  I want to be sure that dropdowns closes if click outside

  Scenario: Check dropdowns autoclose
    Given I login as AmandaRCole@example.org buyer
    And I click "Search Button"
    And I click "Frontend Grid Action Filter Button"
    And I should not see an "Opened Filter Dropdown" element
    When I click "Filter By Name"
    And I should see an "Opened Filter Dropdown" element
    And I click "Frontend Product Grid Sorter Action"
    Then I should not see an "Opened Filter Dropdown" element

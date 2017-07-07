@ticket-BB-9097
@automatically-ticket-tagged
@fixture-OroFrontendBundle:Products.yml
Feature: Sticky panel for main menu, product filters etc.

  Scenario: Check is sticky panel visible and has main menu content (mobile version)
    Given I set window size to 320x640
    Given I am on homepage
    Then I should not see an "Active Sticky Panel" element
    And I should see a "Main Menu Into Header" element
    When I click "Copyright"
    Then I should see an "Active Sticky Panel" element
    And I should see a "Main Menu Into Sticky Panel" element
    When I click "Header"
    Then I should not see an "Active Sticky Panel" element
    And I should see a "Main Menu Into Header" element

  Scenario: Check is sticky panel visible and has product filter
    Given I am on "/product/?grid"
    Then I should not see an "Active Sticky Panel" element
    And I should see a "Product Filter Into Page Content" element
    When I click "Copyright"
    Then I should see an "Active Sticky Panel" element
    And I should see a "Product Filter Into Sticky Panel" element
    When I click "Header"
    Then I should not see an "Active Sticky Panel" element
    And I should see a "Product Filter Into Page Content" element

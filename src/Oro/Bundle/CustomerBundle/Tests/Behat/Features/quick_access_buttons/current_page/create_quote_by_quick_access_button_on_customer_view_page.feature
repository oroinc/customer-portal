@feature-BB-21879
@fixture-OroProductBundle:single_product.yml

Feature: Create quote by quick access button on customer view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new quote from customer view page by quick access button using current page

  Scenario: Create quote by click on quick access button
    Given I login as administrator
    When I go to Customers / Customers
    And I click view "Company A" in grid
    Then I should see following buttons:
      | New Quote |
    When I click "New Quote"
    Then "Quote Form" must contains values:
      | Customer | Company A |
      | Website  | Default   |
    When I click "Line Items"
    And I fill "Quote Form" with:
      | LineItemProduct | PSKU1 |
      | LineItemPrice   | 10    |
    And I save and close form
    And I click "Save" in modal window
    Then I should see Quote with:
      | Customer | Company A |
      | Website  | Default   |

  Scenario: Create quote by click on quick access button from "More actions" dropdown
    When I go to Customers / Customers
    And I click view "Company A" in grid
    And I follow "More actions"
    And I follow "Create Quote"
    Then "Quote Form" must contains values:
      | Customer | Company A |
      | Website  | Default   |
    When I click "Line Items"
    And I fill "Quote Form" with:
      | LineItemProduct | PSKU1 |
      | LineItemPrice   | 10    |
    And I save and close form
    And I click "Save" in modal window
    Then I should see Quote with:
      | Customer | Company A |
      | Website  | Default   |

  Scenario: Save quote and return to customer view page after click on quick access button
    When I go to Customers / Customers
    And I click view "Company A" in grid
    And I click "New Quote"
    Then "Quote Form" must contains values:
      | Customer | Company A |
      | Website  | Default   |
    And I should see "Save and Return" action button
    And I set alias "tab1" for the current browser tab
    When I open a new browser tab and set "tab2" alias for it
    Then I should see "Save and Return" action button
    When I switch to the browser tab "tab1"
    And I click "Line Items"
    And I fill "Quote Form" with:
      | LineItemProduct | PSKU1 |
      | LineItemPrice   | 10    |
    And I save form and return
    And I click "Save" in modal window
    Then I should see "Customers / Company A"
    And I should see following "Quotes by Customer Grid" grid:
      | Quote # | Step  |
      | 1       | Draft |
      | 2       | Draft |
      | 3       | Draft |

  Scenario: Set Customer entity view permissions to 'View:None' for Administrator Role
    When I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    And I click edit "Administrator" in grid
    And select following permissions:
      | Customer | View:None |
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: Check save and return action button is not shown on quote create page
    When I switch to the browser tab "tab2"
    And I reload the page
    Then I should not see "Save and Return" action button

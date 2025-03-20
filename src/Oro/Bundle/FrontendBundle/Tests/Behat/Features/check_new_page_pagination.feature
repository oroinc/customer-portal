@fixture-OroFrontendBundle:AllProductsFixture.yml

Feature: Check new page pagination

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Check new page pagination
    Given I proceed as the User
    When I am on "/product"
    And I reload the page
    Then I should see "250 products" and continue checking the condition is met for maximum 20 seconds

    When I press on 2 page in the page pagination
    Then I should see that page 2 is active
    And I should see "1 2 3 4 5 ... 10" in the "Frontend List Pagination" element

    When I press on 5 page in the page pagination
    Then I should see "1 ... 4 5 6 ... 10" in the "Frontend List Pagination" element

    When I press on 10 page in the page pagination
    Then I should see "1 ... 6 7 8 9 10" in the "Frontend List Pagination" element

    When I type 5 into the page paginator
    Then I should see that page 5 is active

  Scenario: Change the number of pages for pagination
    Given I proceed as the Admin
    And login as administrator
    When I go to System/Theme Configurations
    And I click "Edit" on row "Golden Carbon" in grid
    And I fill "Theme Configuration Form" with:
      | Use pagination input if number of pages exceeds | 10 |
    And I save and close form
    Then I should see "Theme Configuration" flash message

  Scenario: Check that the paginator has changed
    Given I proceed as the User
    When I reload the page
    Then I should see a "Frontend Pagination" element
    And I should not see a "Frontend List Pagination" element

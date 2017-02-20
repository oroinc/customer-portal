@fixture-BuyerCustomerFixture.yml
Feature: Price lists must be sortable in customer & customerGroup create\edit page

  Scenario: Changing Price List Priorities In Customer Group
    Given I login as administrator
    And I go to "/admin/customer/group/view/1"
    And I click "Edit"
    Then I should not see "Priority"
    And I should see Drag-n-Drop icon present on price list line
    When I click "Add Price List"
    And I choose a price list "first price list" in "2" row
    And I choose a price list "second price list" in "1" row
    And I drag "2" row on top in price lists collection
    And I click "Save and Close"
    Then I should see "Customer group has been saved" flash message
    And I should see that "first price list" price list is in "1" row on view page

  Scenario: Changing Price List Priorities In Customers
    Given I login as administrator
    And I go to "/admin/customer/view/1"
    And I click "Edit"
    Then I should not see "Priority"
    And I should see Drag-n-Drop icon present on price list line
    When I click "Add Price List"
    And I choose a price list "first price list" in "2" row
    And I choose a price list "second price list" in "1" row
    And I drag "2" row on top in price lists collection
    And I click "Save and Close"
    Then I should see "Customer has been saved" flash message
    And I should see that "first price list" price list is in "1" row on view page

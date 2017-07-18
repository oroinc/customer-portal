@ticket-BB-8699
@automatically-ticket-tagged
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml

Feature: Add 'New Company Address' and 'New Address' buttons under datagrids

  Scenario: Check Buttons under datagrids
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account"
    When I click "Address Book"
    Then I should see a "Customer Company Address Button" element
    And I should see a "Customer New Company Address Button" element
